<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $lists = $user->lists()
            ->withCount(['gifts', 'gifts as claimed_gifts_count' => function ($query) {
                $query->whereHas('claims');
            }])
            ->with(['gifts' => function ($query) {
                $query->with('claims')->reorder()->orderByDesc('created_at');
            }])
            ->get();

        // Determine if user is in "single list mode" (90% of users)
        $isSingleListMode = $lists->count() === 1;

        // Get the default list for single-list mode
        $defaultList = $lists->firstWhere('is_default', true) ?? $lists->first();

        return view('dashboard', [
            'lists' => $lists,
            'isSingleListMode' => $isSingleListMode,
            'defaultList' => $defaultList,
        ]);
    }
}
