<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $lists = $user->lists()->withCount('gifts')->with('gifts')->get();

        return view('dashboard', [
            'lists' => $lists,
        ]);
    }
}
