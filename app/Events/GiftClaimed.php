<?php

namespace App\Events;

use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftClaimed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Gift $gift,
        public readonly Claim $claim,
        public readonly int $claimCount,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            // Owner's private channel (for dashboard notifications)
            new PrivateChannel('user.'.$this->gift->user_id),
        ];

        /** @var GiftList $list */
        foreach ($this->gift->lists as $list) {
            $channels[] = new Channel('list.'.$list->slug);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'gift.claimed';
    }

    public function broadcastWith(): array
    {
        return [
            'gift' => [
                'id' => $this->gift->id,
                'title' => $this->gift->title,
                'allow_multiple_claims' => $this->gift->allow_multiple_claims,
                'claim_count' => $this->claimCount,
            ],
            'claimed' => ! $this->gift->allowsMultipleClaims(),
        ];
    }
}
