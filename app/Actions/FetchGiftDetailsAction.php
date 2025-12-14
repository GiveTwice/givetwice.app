<?php

namespace App\Actions;

use App\Models\Gift;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Mattiasgeniar\ProductInfoFetcher\ProductInfoFetcherClass;
use Throwable;

class FetchGiftDetailsAction implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly Gift $gift
    ) {
        $this->onQueue('fetch');
    }

    public function handle(ProcessGiftImageAction $imageAction): void
    {
        $this->gift->update(['fetch_status' => 'fetching']);

        try {
            $product = (new ProductInfoFetcherClass($this->gift->url))
                ->setUserAgent('GiftWithLove/1.0 (Wishlist Service; +https://giftwith.love) Mozilla/5.0 (compatible)')
                ->setTimeout(15)
                ->setConnectTimeout(10)
                ->fetchAndParse();

            $this->gift->update([
                'title' => $product->name ?: $this->gift->title,
                'description' => $product->description ?: $this->gift->description,
                'price_in_cents' => $product->priceInCents,
                'currency' => $product->priceCurrency ?: $this->gift->currency,
                'fetch_status' => 'completed',
                'fetched_at' => now(),
            ]);

            $this->tryAddImageFromUrls($imageAction, $product->allImageUrls ?? []);
        } catch (ClientException|ServerException|ConnectException $e) {
            Log::warning('Gift fetch failed due to HTTP error', [
                'gift_id' => $this->gift->id,
                'url' => $this->gift->url,
                'error' => $e->getMessage(),
            ]);

            $this->gift->update([
                'fetch_status' => 'failed',
                'fetched_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->gift->update([
                'fetch_status' => 'failed',
                'fetched_at' => now(),
            ]);

            $imageAction->dispatchCompletedEvent($this->gift);

            throw $e;
        }

        $imageAction->dispatchCompletedEvent($this->gift);
    }

    /**
     * Try to add an image from a list of URLs.
     * Stops at the first successful image download.
     */
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
}
