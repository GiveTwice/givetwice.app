<?php

namespace App\Actions;

use App\Models\Gift;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\ProductInfo;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\TransportAttempt;
use GiveTwice\ProductInfoFetcher\Enum\FetchOutcome;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\SlackAlerts\Facades\SlackAlert;
use Throwable;

class FetchGiftDetailsAction implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 4;

    /** @var array<int, int> */
    public array $backoff = [5, 30, 60];

    private ?ProductInfoFetcher $fetcher = null;

    public function __construct(
        public readonly Gift $gift
    ) {
        $this->onQueue('fetch');
    }

    public function setFetcher(ProductInfoFetcher $fetcher): self
    {
        $this->fetcher = $fetcher;

        return $this;
    }

    public function handle(ProcessGiftImageAction $imageAction): void
    {
        $started = microtime(true);
        $status = 'failed';
        $product = null;
        $this->gift->update(['fetch_status' => 'fetching']);

        try {
            $fetcher = $this->getFetcher();
            $product = $fetcher->fetchResult();
            if ($this->shouldTryAmazonBrowser($product)) {
                $httpAttempts = $product->attempts;
                $product = $fetcher->preferHeadless()->fetchResult();
                $product->attempts = [...$httpAttempts, ...$product->attempts];
            }

            if ($product->outcome === FetchOutcome::Success
                && preg_match('/just a moment|shop online fashion/i', $product->name ?? '')) {
                $product->outcome = FetchOutcome::Incomplete;
            }

            if ($product->outcome === FetchOutcome::Success
                || ($product->outcome === FetchOutcome::Consent && $product->hasProductData())) {
                $this->applyProduct($product, $imageAction);
                $status = $product->outcome === FetchOutcome::Success ? 'completed' : 'failed';
            } else {
                $error = $this->outcomeError($product);
                if ($product->outcome === FetchOutcome::Error) {
                    throw new RuntimeException($error['summary']);
                }

                $this->markAsFailed($error);
            }
        } catch (Throwable $e) {
            $this->recordAttempt($product ? $this->outcomeError($product) : [
                'summary' => 'Unexpected: '.get_class($e),
            ]);

            throw $e;
        } finally {
            Log::info('Gift fetch attempt', [
                'gift_id' => $this->gift->id,
                'host' => $this->gift->siteName(),
                'status' => $status,
                'outcome' => $product?->outcome->value ?? FetchOutcome::Error->value,
                'final_url' => $this->redactUrl($product?->finalUrl),
                'http_status' => $product?->httpStatus,
                'attempts' => array_map(fn (TransportAttempt $attempt): array => [
                    ...$attempt->toArray(),
                    'finalUrl' => $this->redactUrl($attempt->finalUrl),
                ], $product->attempts ?? []),
                'duration_ms' => (int) round((microtime(true) - $started) * 1000),
                'attempt' => $this->attempts(),
            ]);
        }

        $imageAction->dispatchCompletedEvent($this->gift);
    }

    private function shouldTryAmazonBrowser(ProductInfo $product): bool
    {
        $host = strtolower(parse_url($this->gift->url, PHP_URL_HOST) ?? '');

        return $product->outcome === FetchOutcome::Blocked
            && $product->httpStatus === 200
            && count($product->attempts) === 1
            && $product->attempts[0]->transport === 'http'
            && in_array($host, [
                'amazon.com.be', 'www.amazon.com.be', 'amazon.nl', 'www.amazon.nl',
                'amazon.de', 'www.amazon.de', 'amazon.fr', 'www.amazon.fr',
            ], true);
    }

    private function applyProduct(ProductInfo $product, ProcessGiftImageAction $imageAction): void
    {
        $this->gift->getConnection()->transaction(function () use ($product): void {
            $this->gift->refreshForUpdate();
            $details = [
                'title' => $product->name ? Str::limit($product->name, 255, '') : $this->gift->title,
                'description' => $product->description ? Str::limit($product->description, 1497, '...') : $this->gift->description,
                'price_in_cents' => $product->priceInCents,
                'currency' => $product->priceCurrency ?: $this->gift->currency,
                'fetch_status' => $product->outcome === FetchOutcome::Success ? 'completed' : 'failed',
                'fetch_error' => $product->outcome === FetchOutcome::Success ? null : $this->outcomeError($product),
                'fetch_attempts' => $this->attempts(),
                'fetched_at' => now(),
                'rating' => $product->rating,
                'review_count' => $product->reviewCount,
            ];
            foreach ($this->gift->edited_fields ?? [] as $field) {
                unset($details[$field]);
            }
            $this->gift->update(array_filter($details, fn ($value, $field) => $value !== null || $field === 'fetch_error', ARRAY_FILTER_USE_BOTH));
        });
        $this->tryAddImageFromUrls($imageAction, $product->allImageUrls);
    }

    /** @return array{summary: string, outcome: string, status_code: ?int} */
    private function outcomeError(ProductInfo $product): array
    {
        return [
            'summary' => 'Product fetch: '.$product->outcome->value,
            'outcome' => $product->outcome->value,
            'status_code' => $product->httpStatus,
        ];
    }

    private function redactUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host']
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .($parts['path'] ?? '');
    }

    public function failed(?Throwable $exception): void
    {
        $fallback = [
            'summary' => $exception
                ? get_class($exception)
                : 'Unknown error',
        ];

        Log::error('Gift fetch permanently failed after all retries', [
            'gift_id' => $this->gift->id,
            'url' => $this->redactUrl($this->gift->url),
        ]);

        $this->markAsFailed($this->gift->fetch_error ?? $fallback);

        $adminUrl = route('admin.gifts.show', $this->gift);

        SlackAlert::message("⚠️ Gift fetch failed for <{$this->gift->url}|{$this->gift->title}> (<{$adminUrl}|#{$this->gift->id}>, owner: {$this->gift->user->email})");

        app(ProcessGiftImageAction::class)->dispatchCompletedEvent($this->gift);
    }

    private function tryAddImageFromUrls(ProcessGiftImageAction $imageAction, array $imageUrls): void
    {
        if (in_array('image', $this->gift->fresh()->edited_fields ?? [], true)) {
            return;
        }

        foreach ($imageUrls as $imageUrl) {
            if (empty($imageUrl)) {
                continue;
            }

            $success = $imageAction->fromUrl($this->gift, $imageUrl);

            if ($success) {
                $this->gift->update(['original_image_url' => $imageUrl]);

                return;
            }
        }
    }

    private function getFetcher(): ProductInfoFetcher
    {
        return $this->fetcher ?? $this->createFetcher();
    }

    protected function createFetcher(): ProductInfoFetcher
    {
        $fetcher = (new ProductInfoFetcher($this->gift->url))
            ->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36')
            ->setTimeout(15)
            ->setConnectTimeout(10)
            ->withExtraHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'max-age=0',
                'Sec-CH-UA' => '"Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
                'Sec-CH-UA-Mobile' => '?0',
                'Sec-CH-UA-Platform' => '"macOS"',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
            ])
            ->enableHeadlessFallback();

        $proxy = config('services.gift_fetcher.proxy');

        if ($proxy) {
            $fetcher->viaProxy($proxy);
        }

        return $fetcher;
    }

    private function markAsFailed(array $error): void
    {
        $this->gift->update([
            'title' => $this->gift->title ?: $this->gift->suggestedTitle(),
            'fetch_status' => 'failed',
            'fetch_error' => $error,
            'fetch_attempts' => $this->attempts(),
            'fetched_at' => now(),
        ]);
    }

    private function recordAttempt(array $error): void
    {
        $this->gift->update([
            'fetch_error' => $error,
            'fetch_attempts' => $this->attempts(),
        ]);
    }
}
