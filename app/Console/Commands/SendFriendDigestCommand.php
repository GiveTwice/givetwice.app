<?php

namespace App\Console\Commands;

use App\Jobs\SendListUpdate;
use App\Models\FollowedList;
use App\Models\GiftList;
use App\Models\ListChange;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('friends:send-digest')]
#[Description('Send bundled wishlist updates to eligible recipients during daytime')]
class SendFriendDigestCommand extends Command
{
    public function handle(): int
    {
        if (now('Europe/Brussels')->hour < 9) {
            return self::SUCCESS;
        }
        if (now('Europe/Brussels')->hour >= 20) {
            return self::SUCCESS;
        }
        GiftList::query()->where(fn ($lists) => $lists
            ->whereDoesntHave('followers', fn ($follows) => $follows->whereColumn('user_id', 'lists.creator_id')->where('manager_updates', true))
            ->orWhereHas('users', fn ($users) => $users->whereDoesntHave('followedLists', fn ($follows) => $follows
                ->whereColumn('list_id', 'lists.id')->where('manager_updates', true))))
            ->with('users', 'creator')->chunkById(100, function ($lists): void {
                foreach ($lists as $list) {
                    foreach ($list->users->push($list->creator)->unique('id') as $manager) {
                        $joinedAt = $manager->pivot->joined_at ?? $list->created_at;
                        $cursor = ListChange::where('list_id', $list->id)->where('created_at', '<', $joinedAt)->max('id') ?? 0;
                        $follow = FollowedList::firstOrCreate(['user_id' => $manager->id, 'list_id' => $list->id], [
                            'notifications' => true, 'manager_updates' => true, 'last_change_id' => $cursor,
                        ]);
                        if (! $follow->manager_updates) {
                            $follow->update(['manager_updates' => true]);
                        }
                    }
                }
            });
        User::query()->dueForFriendDigest()->with(['followedLists' => fn ($follows) => $follows->digestReady()])
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    SendListUpdate::dispatch($user->followedLists->first()->id)->afterCommit();
                }
            });

        return self::SUCCESS;
    }
}
