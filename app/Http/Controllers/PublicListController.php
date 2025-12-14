<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\View\View;

class PublicListController extends Controller
{
    public function show(string $locale, GiftList $list): View
    {
        $list->load('user:id,name');

        $gifts = $list->gifts()
            ->whereNull('deleted_at')
            ->withCount(['claims' => function ($query) {
                $query->whereNotNull('confirmed_at');
            }])
            ->with(['claims' => function ($query) {
                $query->whereNotNull('confirmed_at');
            }])
            ->paginate(100);

        return view('public.list', [
            'list' => $list,
            'gifts' => $gifts,
        ]);
    }
}
