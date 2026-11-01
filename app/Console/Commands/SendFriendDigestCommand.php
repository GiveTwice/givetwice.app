<?php

namespace App\Console\Commands;

use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\ListChange;
use App\Models\ReservationAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
                    $follow->getConnection()->transaction(function () use ($follow): void {
                        $locked = FollowedList::query()->lockForUpdate()->find($follow->id);
                        if (! $locked || ! $locked->notifications || ! $follow->isVisibleTo($follow->user)) {
                            return;
                        }
                        $changes = ListChange::where('list_id', $follow->list_id)->where('id', '>', $locked->last_change_id)
                            ->where(fn ($query) => $query->whereNull('actor_id')->orWhere('actor_id', '!=', $follow->user_id))
                            ->orderBy('id')->get();
                        if ($changes->isEmpty() || $changes->last()->created_at->gt(now()->subMinutes(10))) {
                            return;
                        }
                        $included = $changes;
                        $alerted = ReservationAlert::where('email', $follow->user->email)
                            ->where('list_id', $follow->list_id)->where('created_at', '>=', $changes->first()->created_at)->pluck('gift_id');
                        $included = $changes->reject(fn ($change) => $alerted->contains($change->gift_id));
                        if ($included->isEmpty()) {
                            $locked->update(['last_change_id' => $changes->last()->id]);

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
                        Mail::to($follow->user)->queue((new FriendDigestMail($follow->user, $data))->afterCommit());
                        $locked->update(['last_change_id' => $changes->last()->id]);
                    });
                }
            });

        return self::SUCCESS;
    }
}
