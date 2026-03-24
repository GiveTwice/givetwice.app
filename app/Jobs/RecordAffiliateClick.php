<?php

namespace App\Jobs;

use App\Models\AffiliateClick;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class RecordAffiliateClick implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        public readonly int $giftId,
        public readonly ?int $listId,
        public readonly string $url,
        public readonly string $affiliateUrl,
        public readonly string $retailerDomain,
        public readonly string $ipHash,
        public readonly ?string $userAgent,
        public readonly CarbonInterface $clickedAt,
    ) {}

    public function handle(): void
    {
        AffiliateClick::create([
            'gift_id' => $this->giftId,
            'list_id' => $this->listId,
            'url' => $this->url,
            'affiliate_url' => $this->affiliateUrl,
            'retailer_domain' => $this->retailerDomain,
            'ip_hash' => $this->ipHash,
            'user_agent' => $this->userAgent,
            'clicked_at' => $this->clickedAt,
        ]);
    }
}
