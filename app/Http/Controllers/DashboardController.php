<?php

namespace App\Http\Controllers;

use App\Models\GiftExchange;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard', [
            'lists' => $this->dashboardLists($request),
            'exchanges' => $this->dashboardExchanges($request),
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
        return $request->user()->lists()
            ->withCount('gifts')
            ->with([
                'gifts' => fn ($query) => $query->with('media')->orderBy('gifts.id')->limit(4),
                'users:id,name,avatar',
                'users.media',
            ])
            ->orderBy('lists.created_at')
            ->orderBy('lists.id')
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
