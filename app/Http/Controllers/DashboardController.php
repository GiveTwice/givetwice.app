<?php

namespace App\Http\Controllers;

use App\Models\GiftExchange;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $lists = $this->dashboardLists($request);
        $exchanges = $this->dashboardExchanges($request);

        return view('dashboard', [
            'lists' => $lists,
            'exchanges' => $exchanges,
        ]);
    }

    public function secretSanta(Request $request): View
    {
        return view('dashboard.secret-santa', [
            'exchanges' => $this->dashboardExchanges($request),
            ...$this->listSummary($request),
        ]);
    }

    private function dashboardLists(Request $request)
    {
        return $request->user()
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
    }

    private function dashboardExchanges(Request $request)
    {
        return GiftExchange::where('organizer_id', $request->user()->id)
            ->withCount('participants')
            ->orderByDesc('created_at')
            ->get();
    }

    private function listSummary(Request $request): array
    {
        $lists = $request->user()
            ->lists()
            ->withCount('gifts')
            ->get();

        return [
            'listCount' => $lists->count(),
            'giftCount' => $lists->sum('gifts_count'),
        ];
    }
}
