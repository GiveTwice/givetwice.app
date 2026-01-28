<?php

namespace App\Http\Controllers;

use App\Actions\FollowListAction;
use App\Exceptions\FollowListException;
use App\Models\FollowedList;
use App\Models\GiftList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->authorize('update', $followedList);

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

    public function follow(Request $request, string $locale, GiftList $list, FollowListAction $action): JsonResponse
    {
        try {
            $action->execute($list, $request->user());
        } catch (FollowListException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'following' => true,
        ]);
    }

    public function unfollow(Request $request, string $locale, GiftList $list): JsonResponse
    {
        $request->user()->followedLists()->where('list_id', $list->id)->delete();

        return response()->json([
            'success' => true,
            'following' => false,
        ]);
    }
}
