<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicListController extends Controller
{
    public function show(string $locale, GiftList $list, ?string $slug = null): View|RedirectResponse
    {
        if ($redirect = $this->ensureOnCorrectSlugUrl($list, $slug, $locale)) {
            return $redirect;
        }

        $list->load('user:id,name');

        $gifts = $list->gifts()
            ->whereNull('deleted_at')
            ->withCount(['claims' => function ($query) {
                $query->whereNotNull('confirmed_at');
            }])
            ->with(['claims' => function ($query) {
                $query->whereNotNull('confirmed_at');
            }])
            ->reorder()
            ->orderBy('claims_count')
            ->orderBy('title')
            ->paginate(100);

        return view('public.list', [
            'list' => $list,
            'gifts' => $gifts,
        ]);
    }

    private function ensureOnCorrectSlugUrl(GiftList $list, ?string $slug, string $locale): ?RedirectResponse
    {
        if ($slug !== $list->slug) {
            return redirect($list->getPublicUrl($locale), 301);
        }

        return null;
    }
}
