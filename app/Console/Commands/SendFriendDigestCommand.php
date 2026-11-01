<?php

namespace App\Console\Commands;

use App\Jobs\SendListUpdate;
use App\Models\FollowedList;
use App\Models\GiftList;
use App\Models\ListChange;
use Illuminate\Console\Command;

class SendFriendDigestCommand extends Command
{
    protected $signature = 'friends:send-digest';

    protected $description = 'Send content updates after ten quiet minutes per follower and list';

    public function handle(): int
    {
        GiftList::with('users', 'creator')->chunkById(100, function ($lists): void {
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
        FollowedList::query()->where('notifications', true)
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at')->where('friend_notifications_enabled', true))
            ->with('user', 'list.creator', 'list.users')->chunkById(100, function ($follows): void {
                foreach ($follows as $follow) {
                    SendListUpdate::dispatch($follow->id)->afterCommit();
                }
            });

        return self::SUCCESS;
    }
}
