<?php

namespace App\Http\Controllers;

use App\Actions\CreateListAction;
use App\Http\Requests\AttachExchangeWishlistRequest;
use App\Models\GiftExchangeParticipant;
use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExchangeWishlistController extends Controller
{
    public function create(string $locale, string $token): View
    {
        return view('exchanges.wishlist', [
            'participant' => GiftExchangeParticipant::forReveal($token),
            'lists' => GiftList::where('creator_id', auth()->id())->get(),
        ]);
    }

    public function store(AttachExchangeWishlistRequest $request, string $locale, string $token, CreateListAction $action): RedirectResponse
    {
        $participant = GiftExchangeParticipant::forReveal($token);
        $data = $request->validated();
        $list = ! empty($data['wishlist_id'])
            ? GiftList::where('creator_id', $request->user()->id)->findOrFail($data['wishlist_id'])
            : $action->execute($request->user(), $data['name']);
        $participant->update(['wishlist_id' => $list->id, 'user_id' => $request->user()->id]);

        return redirect($list->getPublicUrl($locale))->with('success', __('Your wishlist is linked. Whoever drew you can see it now.'));
    }
}
