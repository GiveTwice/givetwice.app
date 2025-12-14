<?php

namespace App\Actions;

use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Mattiasgeniar\ProductInfoFetcher\ProductInfoFetcherClass;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
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

    public function handle(): void
    {
        $this->gift->update(['fetch_status' => 'fetching']);

        try {
            $product = (new ProductInfoFetcherClass($this->gift->url))
                ->setUserAgent('GiftWithLove/1.0 (Wishlist Service; +https://giftwith.love) Mozilla/5.0 (compatible)')
                ->setTimeout(15)
                ->setConnectTimeout(10)
                ->fetchAndParse();

            $originalImageUrl = $product->imageUrl;

            $this->gift->update([
                'title' => $product->name ?: $this->gift->title,
                'description' => $product->description ?: $this->gift->description,
                'price_in_cents' => $product->priceInCents,
                'currency' => $product->priceCurrency ?: $this->gift->currency,
                'original_image_url' => $originalImageUrl ?: $this->gift->original_image_url,
                'fetch_status' => 'completed',
                'fetched_at' => now(),
            ]);

            if ($originalImageUrl) {
                $this->addImageFromUrl($originalImageUrl);
            }
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

            GiftFetchCompleted::dispatch($this->gift->fresh()->load('lists', 'media'));

            throw $e;
        }

        GiftFetchCompleted::dispatch($this->gift->fresh()->load('lists', 'media'));
    }

    private function addImageFromUrl(string $imageUrl): void
    {
        try {
            $this->gift
                ->addMediaFromUrl($imageUrl)
                ->toMediaCollection('image');
        } catch (UnreachableUrl $e) {
            Log::warning('Failed to download gift image: URL unreachable', [
                'gift_id' => $this->gift->id,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Exception downloading gift image', [
                'gift_id' => $this->gift->id,
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
