<?php

namespace App\Http\Controllers;

use App\Models\GiftExchange;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $lists = $request->user()
            ->lists()
            ->withCount(['gifts', 'gifts as claimed_gifts_count' => function ($query) {
                $query->whereHas('claims');
            }])
            ->with(['gifts' => function ($query) {
                $query->with('claims')->reorder()->orderByDesc('created_at');
            }])
            ->with('users:id,name,avatar')
            ->orderBy('lists.created_at', 'asc')
            ->get();

        $exchanges = GiftExchange::where('organizer_id', $request->user()->id)
            ->withCount('participants')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard', [
            'lists' => $lists,
            'exchanges' => $exchanges,
        ]);
    }
}
