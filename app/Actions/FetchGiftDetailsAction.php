<?php

namespace App\Actions;

use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
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

    public function handle(): void
    {
        $this->gift->update(['fetch_status' => 'fetching']);

        try {
            $product = (new ProductInfoFetcherClass($this->gift->url))
                ->setTimeout(15)
                ->setConnectTimeout(10)
                ->fetchAndParse();

            $this->gift->update([
                'title' => $product->name ?: $this->gift->title,
                'description' => $product->description ?: $this->gift->description,
                'price_in_cents' => $product->priceInCents,
                'currency' => $product->priceCurrency ?: $this->gift->currency,
                'image_url' => $product->imageUrl ?: $this->gift->image_url,
                'fetch_status' => 'completed',
                'fetched_at' => now(),
            ]);

            GiftFetchCompleted::dispatch($this->gift->fresh());
        } catch (Throwable $e) {
            $this->gift->update([
                'fetch_status' => 'failed',
                'fetched_at' => now(),
            ]);

            throw $e;
        }
    }
}
