<?php

namespace App\Actions;

use App\Models\Gift;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

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
        // TODO: Implement URL fetching logic
        // - Fetch the gift URL
        // - Parse Open Graph meta tags (og:title, og:description, og:image)
        // - Parse JSON-LD structured data for price
        // - Fallback parsing for title, description, price
        // - Update the gift with fetched data
        // - Handle errors gracefully
    }
}
