<?php

namespace App\Http\Controllers;

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
            ->get();

        return view('dashboard', [
            'lists' => $lists,
        ]);
    }
}
