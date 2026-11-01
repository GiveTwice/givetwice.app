<?php

namespace App\Http\Controllers;

use App\Models\FollowedList;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListUpdatePreferenceController extends Controller
{
    public function __invoke(Request $request, string $locale, FollowedList $follow): View
    {
        if ($request->isMethod('post')) {
            $follow->update(['notifications' => false]);
        }

        return view('friends.mute', compact('follow'));
    }
}
