<?php

namespace App\Events;

use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftFetchCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Gift $gift
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            // Owner's private channel (for dashboard updates)
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
        return 'gift.fetch.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'gift' => [
                'id' => $this->gift->id,
                'url' => $this->gift->url,
                'title' => $this->gift->title,
                'description' => $this->gift->description,
                'price_in_cents' => $this->gift->price_in_cents,
                'price_formatted' => $this->gift->formatPrice(),
                'currency' => $this->gift->currency,
                'image_url_thumb' => $this->gift->getImageUrl('thumb'),
                'image_url_card' => $this->gift->getImageUrl('card'),
                'image_url_large' => $this->gift->getImageUrl('large'),
                'fetch_status' => $this->gift->fetch_status,
            ],
        ];
    }
}
