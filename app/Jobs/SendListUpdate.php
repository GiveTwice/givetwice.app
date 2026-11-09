<?php

namespace App\Jobs;

use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\ListChange;
use App\Models\ReservationAlert;
use App\Models\User;
use App\Support\DigestHours;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendListUpdate implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $followId) {}

    public function handle(): void
    {
        if (! DigestHours::isOpen()) {
            return;
        }
        $seed = FollowedList::find($this->followId);
        if (! $seed) {
            return;
        }
        $seed->getConnection()->transaction(function () use ($seed): void {
            $user = User::query()->lockForUpdate()->find($seed->user_id);
            if (! $user) {
                return;
            }
            if (! User::query()->whereKey($user->id)->dueForFriendDigest()->exists()) {
                return;
            }
            $data = collect();
            $follows = $user->followedLists()->digestReady()->with('user', 'list.creator', 'list.users')->lockForUpdate()->get();
            foreach ($follows as $follow) {
                $managesList = $follow->list->creator_id === $user->id;
                if (! $managesList) {
                    $managesList = $follow->list->hasUser($user);
                }
                $changes = ListChange::where('list_id', $follow->list_id)->where('id', '>', $follow->last_change_id)
                    ->where(fn ($query) => $query->whereNull('actor_id')->orWhere('actor_id', '!=', $follow->user_id))
                    ->with('gift.media', 'actor')->orderBy('id')->get();
                $included = $changes;
                if (! $managesList) {
                    $alerted = ReservationAlert::where('email', $follow->user->email)
                        ->where('list_id', $follow->list_id)->where('updated_at', '>=', $changes->first()->created_at)->pluck('gift_id');
                    $included = $changes->reject(fn ($change) => $alerted->contains($change->gift_id));
                }
                if ($included->isEmpty()) {
                    $follow->update(['last_change_id' => $changes->last()->id]);

                    continue;
                }
                $latest = $included->groupBy('gift_id')->map->last();
                $data->push([
                    'list' => $follow->list,
                    'added_gifts' => $latest->where('kind', 'added')->values(),
                    'edited_gifts' => $latest->where('kind', 'edited')->values(),
                    'removed_gifts' => $latest->where('kind', 'removed')->values(),
                    'actors' => $included->pluck('actor.name')->filter()->unique()->implode(', '),
                    'mute_url' => $follow->muteUrl(),
                ]);
                $follow->update(['last_change_id' => $changes->last()->id]);
            }
            if ($data->isEmpty()) {
                return;
            }
            Mail::to($user)->send(new FriendDigestMail($user, $data));
            $user->update(['last_friend_digest_at' => now()]);
        });
    }
}
