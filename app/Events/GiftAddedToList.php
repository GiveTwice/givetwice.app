<?php

namespace App\Events;

use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftAddedToList implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Gift $gift,
        public readonly GiftList $list,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('list.'.$this->list->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'gift.added';
    }

    public function broadcastWith(): array
    {
        return [
            'gift' => [
                'id' => $this->gift->id,
                'url' => $this->gift->url,
                'title' => $this->gift->title,
                'description' => $this->gift->description,
                'price_formatted' => $this->gift->formatPrice(),
                'fetch_status' => $this->gift->fetch_status,
                'image_url_card' => $this->gift->getImageUrl('card'),
            ],
            'list' => [
                'id' => $this->list->id,
                'slug' => $this->list->slug,
            ],
        ];
    }
}
