<?php

namespace App\Actions;

use App\Models\Gift;
use GiveTwice\ProductInfoFetcher\HeadlessBrowser\Exceptions\BrowserException;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
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
        $this->gift->update(['fetch_status' => 'fetching']);

        try {
            $product = $this->getFetcher()->fetchAndParse();

            $description = $product->description
                ? Str::limit($product->description, 1497, '...')
                : $this->gift->description;

            $this->gift->update([
                'title' => $product->name ?: $this->gift->title,
                'description' => $description,
                'price_in_cents' => $product->priceInCents,
                'currency' => $product->priceCurrency ?: $this->gift->currency,
                'fetch_status' => 'completed',
                'fetch_error' => null,
                'fetch_attempts' => $this->attempts(),
                'fetched_at' => now(),
                'rating' => $product->rating,
                'review_count' => $product->reviewCount,
            ]);

            $this->tryAddImageFromUrls($imageAction, $product->allImageUrls ?? []);
        } catch (ClientException|ServerException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();

            $error = [
                'summary' => "HTTP {$statusCode}: {$response->getReasonPhrase()}",
                'status_code' => $statusCode,
                'headers' => $response->getHeaders(),
                'body' => Str::limit($this->getResponseBody($response), 5000, '… [truncated]'),
            ];

            Log::warning('Gift fetch failed due to HTTP error', [
                'gift_id' => $this->gift->id,
                'url' => $this->gift->url,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);

            if (in_array($statusCode, [429, 503])) {
                $this->recordAttempt($error);

                throw $e;
            }

            $this->markAsFailed($error);
        } catch (ConnectException $e) {
            $error = [
                'summary' => "Connection failed: {$e->getMessage()}",
            ];

            Log::warning('Gift fetch failed due to connection error', [
                'gift_id' => $this->gift->id,
                'url' => $this->gift->url,
                'error' => $e->getMessage(),
            ]);

            $this->markAsFailed($error);
        } catch (BrowserException $e) {
            $error = [
                'summary' => "Browser error: {$e->getMessage()}",
            ];

            if ($e->hasDebugData()) {
                $error['status_code'] = $e->getStatusCode();
                $error['final_url'] = $e->getFinalUrl();
                $error['headers'] = $e->getResponseHeaders();
                $error['body'] = Str::limit($e->getResponseHtml() ?? '', 5000, '… [truncated]');
            }

            Log::warning('Gift fetch failed due to browser error', [
                'gift_id' => $this->gift->id,
                'url' => $this->gift->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $this->recordAttempt($error);

            throw $e;
        } catch (Throwable $e) {
            $error = [
                'summary' => 'Unexpected: '.get_class($e).": {$e->getMessage()}",
            ];

            Log::error('Gift fetch failed due to unexpected error', [
                'gift_id' => $this->gift->id,
                'url' => $this->gift->url,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $this->recordAttempt($error);

            throw $e;
        }

        $imageAction->dispatchCompletedEvent($this->gift);
    }

    public function failed(?Throwable $exception): void
    {
        $fallback = [
            'summary' => $exception
                ? get_class($exception).": {$exception->getMessage()}"
                : 'Unknown error',
        ];

        Log::error('Gift fetch permanently failed after all retries', [
            'gift_id' => $this->gift->id,
            'url' => $this->gift->url,
            'error' => $exception?->getMessage(),
        ]);

        $this->markAsFailed($this->gift->fetch_error ?? $fallback);

        app(ProcessGiftImageAction::class)->dispatchCompletedEvent($this->gift);
    }

    private function tryAddImageFromUrls(ProcessGiftImageAction $imageAction, array $imageUrls): void
    {
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
            ->preferHeadless();

        $proxy = config('services.gift_fetcher.proxy');

        if ($proxy) {
            $fetcher->viaProxy($proxy);
        }

        return $fetcher;
    }

    private function markAsFailed(array $error): void
    {
        $this->gift->update([
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

    private function getResponseBody(ResponseInterface $response): string
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return (string) $body;
    }
}
