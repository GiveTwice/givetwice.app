<?php

namespace App\Console\Commands;

use App\Mail\FriendDigestMail;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class SendFriendDigestCommand extends Command
{
    protected $signature = 'friends:send-digest';

    protected $description = 'Send daily email digest of friends\' wishlist updates';

    public function handle(): int
    {
        $startedAt = now();

        $users = User::query()
            ->where('friend_notifications_enabled', true)
            ->whereNotNull('email_verified_at')
            ->whereHas('followedLists', function (Builder $query) {
                $query->where('notifications', true);
            })
            ->get();

        $sentCount = 0;

        foreach ($users as $user) {
            $digestData = $this->getDigestDataForUser($user);

            if ($digestData->isEmpty()) {
                continue;
            }

            Mail::to($user)->queue(new FriendDigestMail($user, $digestData));
            $sentCount++;
        }

        if ($users->isNotEmpty()) {
            User::query()
                ->whereIn('id', $users->pluck('id'))
                ->update(['last_friend_digest_at' => $startedAt]);
        }

        $this->info("Sent {$sentCount} friend digest emails.");

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, array{list: GiftList, added_gifts: Collection<int, Gift>, removed_gifts: Collection<int, Gift>}>
     */
    protected function getDigestDataForUser(User $user): Collection
    {
        $sinceDate = $user->last_friend_digest_at ?? now()->subDay();

        $followedLists = $user->followedLists()
            ->where('notifications', true)
            ->visibleTo($user)
            ->with(['list.creator', 'list.users'])
            ->get();

        if ($followedLists->isEmpty()) {
            return collect();
        }

        $listIds = $followedLists->pluck('list_id')->toArray();

        $addedGiftsByList = $this->groupGiftsByMatchingLists(
            Gift::query()
                ->whereHas('lists', function (Builder $query) use ($listIds) {
                    $query->whereIn('lists.id', $listIds);
                })
                ->where('created_at', '>', $sinceDate)
                ->with('lists')
                ->get(),
            $listIds,
        );

        $removedGiftsByList = $this->groupGiftsByMatchingLists(
            Gift::withTrashed()
                ->whereHas('lists', function (Builder $query) use ($listIds) {
                    $query->whereIn('lists.id', $listIds);
                })
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '>', $sinceDate)
                ->with('lists')
                ->get(),
            $listIds,
        );

        $digestData = collect();

        foreach ($followedLists as $followedList) {
            $listId = $followedList->list_id;
            $addedGifts = $addedGiftsByList->get($listId, collect());
            $removedGifts = $removedGiftsByList->get($listId, collect());

            if ($addedGifts->isEmpty() && $removedGifts->isEmpty()) {
                continue;
            }

            $digestData->push([
                'list' => $followedList->list,
                'added_gifts' => $addedGifts,
                'removed_gifts' => $removedGifts,
            ]);
        }

        return $digestData;
    }

    /**
     * @param  Collection<int, Gift>  $gifts
     * @param  array<int>  $listIds
     * @return Collection<int, Collection<int, Gift>>
     */
    protected function groupGiftsByMatchingLists(Collection $gifts, array $listIds): Collection
    {
        $grouped = collect();

        foreach ($gifts as $gift) {
            $matchingListIds = $gift->lists->pluck('id')->intersect($listIds);

            foreach ($matchingListIds as $listId) {
                if (! $grouped->has($listId)) {
                    $grouped->put($listId, collect());
                }
                $grouped->get($listId)->push($gift);
            }
        }

        return $grouped;
    }
}
