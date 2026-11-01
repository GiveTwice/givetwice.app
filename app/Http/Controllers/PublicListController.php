<?php

namespace App\Http\Controllers;

use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicListController extends Controller
{
    public function show(Request $request, string $locale, int $list, ?string $slug = null): Response|RedirectResponse
    {
        $list = GiftList::findOrFail($list);

        if ($redirect = $this->ensureOnCorrectSlugUrl($list, $slug, $locale)) {
            return $redirect;
        }

        $list->load(['creator:id,name,avatar', 'users:id,name']);

        $isOwner = auth()->check() && $list->hasUser(auth()->user());

        $isFollowing = false;
        if (auth()->check() && ! $isOwner) {
            $isFollowing = auth()->user()->followedLists()->where('list_id', $list->id)->exists();
        }

        $showReservations = ! $isOwner || $list->spoiler_mode;
        $filter = $showReservations && in_array($request->query('filter'), ['free', 'under20'], true)
            ? $request->query('filter') : 'all';
        $query = $list->gifts()->with('media');
        $totalGifts = (clone $query)->count();
        $availableGifts = null;
        if ($showReservations) {
            $free = fn ($query) => $query->where('allow_multiple_claims', true)
                ->orWhereDoesntHave('claims', fn ($query) => $query->whereNotNull('confirmed_at'));
            $availableGifts = (clone $query)->where($free)->count();
            $query->withCount(['claims' => fn ($query) => $query->whereNotNull('confirmed_at')])
                ->with(['claims' => fn ($query) => $query->whereNotNull('confirmed_at')])
                ->reorder()->orderByRaw('case when gifts.allow_multiple_claims = true or claims_count = 0 then 0 else 1 end');
            if ($filter === 'free') {
                $query->where($free);
            } elseif ($filter === 'under20') {
                $query->where('currency', 'EUR')->where('price_in_cents', '<', 2000);
            }
        }
        $gifts = $query->orderBy('title')->paginate(100)->withQueryString();
        if (! $showReservations) {
            $gifts->each(fn ($gift) => $gift->setRelation('claims', collect()));
        }

        $myReservationGiftIds = $isOwner ? collect() : $gifts->filter(fn ($gift) => $gift->claims->contains(fn ($claim) => $claim->belongsToBrowser($request)))->pluck('id');

        return response()->view('public.list', [
            'myReservationGiftIds' => $myReservationGiftIds,
            'showReservations' => $showReservations,
            'filter' => $filter,
            'totalGifts' => $totalGifts,
            'availableGifts' => $availableGifts,
            'list' => $list,
            'gifts' => $gifts,
            'isOwner' => $isOwner,
            'isFollowing' => $isFollowing,
        ])->header('Cache-Control', 'private, no-store');
    }

    private function ensureOnCorrectSlugUrl(GiftList $list, ?string $slug, string $locale): ?RedirectResponse
    {
        if ($slug !== $list->slug) {
            return redirect($list->getPublicUrl($locale), 301);
        }

        return null;
    }
}
