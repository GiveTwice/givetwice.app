<?php

namespace App\Events;

use App\Models\Gift;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GiftFetchCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Gift $gift
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.'.$this->gift->user_id),
        ];
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
                'image_url' => $this->gift->image_url,
                'fetch_status' => $this->gift->fetch_status,
            ],
        ];
    }
}
