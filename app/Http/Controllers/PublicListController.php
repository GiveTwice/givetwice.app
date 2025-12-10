<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\View\View;

class PublicListController extends Controller
{
    public function show(string $locale, string $slug): View
    {
        $list = GiftList::where('slug', $slug)
            ->where('is_public', true)
            ->with('user:id,name')
            ->firstOrFail();

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
