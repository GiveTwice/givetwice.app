<?php

namespace App\Jobs;

use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\GiftList;
use App\Models\ListChange;
use App\Models\ReservationAlert;
use App\Models\User;
use App\Support\DigestHours;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
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
                    ->with(['gift' => fn ($gift) => $gift->with('media')->withCount(['claims' => fn ($claims) => $claims->confirmed()]), 'actor'])
                    ->orderBy('id')->get();
                $follow->update(['last_change_id' => $changes->last()->id]);
                if ($managesList) {
                    $data->push($this->managerNotice($follow, $changes));

                    continue;
                }
                $alerted = ReservationAlert::where('email', $follow->user->email)
                    ->where('list_id', $follow->list_id)->where('updated_at', '>=', $changes->first()->created_at)->pluck('gift_id');
                $open = $changes->reject(fn ($change) => $alerted->contains($change->gift_id))
                    ->reverse()->unique('gift_id')->reverse()
                    ->filter(fn ($change) => $change->kind !== 'removed' && $change->gift?->claims_count === 0);
                if ($open->isEmpty()) {
                    continue;
                }
                $data->push([
                    'list' => $follow->list,
                    'added_gifts' => $open->where('kind', 'added')->values(),
                    'edited_gifts' => $open->where('kind', 'edited')->values(),
                    'actors' => $changes->pluck('actor.name')->filter()->unique()->implode(', '),
                    'mute_url' => $follow->muteUrl(),
                ]);
            }
            if ($data->isEmpty()) {
                return;
            }
            Mail::to($user)->send(new FriendDigestMail($user, $data));
            $user->update(['last_friend_digest_at' => now()]);
        });
    }

    /**
     * Builds the update for a list the recipient manages, without naming any gift.
     * Managers cannot see reservations. A list of changed gifts that left out the reserved
     * ones would let them guess which gifts are reserved, so this only says who changed the list.
     *
     * @param  Collection<int, ListChange>  $changes
     * @return array{list: GiftList, added_gifts: Collection<int, ListChange>, edited_gifts: Collection<int, ListChange>, actors: string, mute_url: string}
     */
    private function managerNotice(FollowedList $follow, Collection $changes): array
    {
        return [
            'list' => $follow->list,
            'added_gifts' => collect(),
            'edited_gifts' => collect(),
            'actors' => $changes->pluck('actor.name')->filter()->unique()->implode(', '),
            'mute_url' => $follow->muteUrl(),
        ];
    }
}
