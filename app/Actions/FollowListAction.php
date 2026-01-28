<?php

namespace App\Actions;

use App\Exceptions\FollowListException;
use App\Models\FollowedList;
use App\Models\GiftList;
use App\Models\User;

class FollowListAction
{
    public function execute(GiftList $list, User $user): FollowedList
    {
        if ($list->hasUser($user)) {
            throw FollowListException::cannotFollowOwnList();
        }

        return $user->followedLists()->firstOrCreate(
            ['list_id' => $list->id],
            ['notifications' => true],
        );
    }
}
