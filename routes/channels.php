<?php

use App\Models\GiftList;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('list.{listId}', function ($user, $listId) {
    $list = GiftList::find($listId);

    if (! $list) {
        return false;
    }

    // List owner can always access
    if ($user->id === $list->user_id) {
        return true;
    }

    // All lists are publicly accessible
    return true;
});
