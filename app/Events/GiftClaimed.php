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
        $channels = [];
        $lists = $this->gift->lists()->with('users')->get();
        foreach ($lists->flatMap->users->unique('id') as $user) {
            if (! $lists->contains(fn (GiftList $list) => $list->hasUser($user) && ! $list->spoiler_mode)) {
                $channels[] = new PrivateChannel('user.'.$user->id);
            }
        }
        foreach ($lists as $list) {
            $channels[] = new Channel('list.'.$list->slug.'.reservations');
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
