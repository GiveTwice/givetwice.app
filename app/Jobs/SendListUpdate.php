<?php

namespace App\Jobs;

use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\ListChange;
use App\Models\ReservationAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendListUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $followId) {}

    public function handle(): void
    {
        (new FollowedList)->getConnection()->transaction(function (): void {
            $follow = FollowedList::query()->lockForUpdate()->find($this->followId);
            if (! $follow) {
                return;
            }
            if (! $follow->notifications) {
                return;
            }
            if (! $follow->user?->hasVerifiedEmail()) {
                return;
            }
            if (! $follow->user->friend_notifications_enabled) {
                return;
            }
            if (! $follow->isVisibleTo($follow->user)) {
                return;
            }
            $changes = ListChange::where('list_id', $follow->list_id)->where('id', '>', $follow->last_change_id)
                ->where(fn ($query) => $query->whereNull('actor_id')->orWhere('actor_id', '!=', $follow->user_id))
                ->orderBy('id')->get();
            if ($changes->isEmpty()) {
                return;
            }
            if ($changes->last()->created_at->gt(now()->subMinutes(10))) {
                return;
            }
            $included = $changes;
            $alerted = ReservationAlert::where('email', $follow->user->email)
                ->where('list_id', $follow->list_id)->where('updated_at', '>=', $changes->first()->created_at)->pluck('gift_id');
            $included = $changes->reject(fn ($change) => $alerted->contains($change->gift_id));
            if ($included->isEmpty()) {
                $follow->update(['last_change_id' => $changes->last()->id]);

                return;
            }
            $latest = $included->groupBy('gift_id')->map->last();
            $data = collect([[
                'list' => $follow->list,
                'added_gifts' => $latest->where('kind', 'added')->values(),
                'edited_gifts' => $latest->where('kind', 'edited')->values(),
                'removed_gifts' => $latest->where('kind', 'removed')->values(),
                'mute_url' => $follow->muteUrl(),
            ]]);
            Mail::to($follow->user)->send(new FriendDigestMail($follow->user, $data));
            $follow->update(['last_change_id' => $changes->last()->id]);
        });
    }
}
