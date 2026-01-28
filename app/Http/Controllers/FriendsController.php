<?php

namespace App\Http\Controllers;

use App\Models\FollowedList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FriendsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $followedLists = $user->followedLists()
            ->visibleTo($user)
            ->with(['list.creator', 'list.users', 'list.gifts'])
            ->get();

        $groupedByOwner = $followedLists->groupBy(fn (FollowedList $followedList) => $followedList->list->creator_id);

        return view('friends.index', [
            'groupedByOwner' => $groupedByOwner,
            'globalNotificationsEnabled' => $user->friend_notifications_enabled,
        ]);
    }

    public function toggleListNotifications(Request $request, string $locale, FollowedList $followedList): JsonResponse
    {
        Gate::authorize('update', $followedList);

        $followedList->update([
            'notifications' => ! $followedList->notifications,
        ]);

        return response()->json([
            'success' => true,
            'notifications' => $followedList->notifications,
        ]);
    }

    public function toggleGlobalNotifications(Request $request, string $locale): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'friend_notifications_enabled' => ! $user->friend_notifications_enabled,
        ]);

        return response()->json([
            'success' => true,
            'enabled' => $user->friend_notifications_enabled,
        ]);
    }
}
