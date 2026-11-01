<?php

namespace App\Console\Commands;

use App\Jobs\SendListUpdate;
use App\Models\FollowedList;
use Illuminate\Console\Command;

class SendFriendDigestCommand extends Command
{
    protected $signature = 'friends:send-digest';

    protected $description = 'Send content updates after ten quiet minutes per follower and list';

    public function handle(): int
    {
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
