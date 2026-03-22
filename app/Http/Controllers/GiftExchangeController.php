<?php

namespace App\Http\Controllers;

use App\Actions\CreateGiftExchangeAction;
use App\Actions\PerformDrawAction;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class GiftExchangeController extends Controller
{
    public function landing(string $locale, string $exchangeType): View
    {
        if (! auth()->check()) {
            Session::put('url.intended', route('exchanges.landing', [
                'locale' => $locale,
                'exchangeType' => $exchangeType,
            ]));
        }

        return view('exchanges.landing', [
            'exchangeType' => $exchangeType,
            'locale' => $locale,
        ]);
    }

    public function store(
        Request $request,
        string $locale,
        string $exchangeType,
        CreateGiftExchangeAction $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after:today'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_currency' => ['nullable', 'string', 'in:EUR,USD'],
            'participants' => ['required', 'array', 'min:2'],
            'participants.*.name' => ['required', 'string', 'max:255'],
            'participants.*.email' => ['required', 'email', 'max:255'],
            'organizer_participates' => ['nullable', 'boolean'],
        ]);

        $budgetAmount = $validated['budget_amount']
            ? (int) ($validated['budget_amount'] * 100)
            : null;

        $exchange = $action->execute(
            organizer: $request->user(),
            name: $validated['name'],
            eventDate: $validated['event_date'],
            locale: $locale,
            participants: $validated['participants'],
            budgetAmount: $budgetAmount,
            budgetCurrency: $validated['budget_currency'] ?? 'EUR',
            organizerParticipates: (bool) ($validated['organizer_participates'] ?? false),
        );

        return redirect()
            ->route('exchanges.status', ['locale' => $locale, 'exchange' => $exchange->slug])
            ->with('success', __('Group created! Add more people or draw names when you\'re ready.'));
    }

    public function draw(
        Request $request,
        string $locale,
        GiftExchange $exchange,
        PerformDrawAction $action,
    ): RedirectResponse {
        $this->authorize('draw', $exchange);

        $action->execute($exchange);

        return redirect()
            ->route('exchanges.status', ['locale' => $locale, 'exchange' => $exchange->slug])
            ->with('success', __('Names drawn! Invites are on their way.'));
    }

    public function status(string $locale, GiftExchange $exchange): View
    {
        $this->authorize('viewStatus', $exchange);

        $exchange->load('participants.user');

        $claimCount = 0;
        /** @var \App\Models\GiftExchangeParticipant $participant */
        foreach ($exchange->participants as $participant) {
            if ($participant->user_id) {
                $wishlist = $participant->defaultWishlist();
                if ($wishlist) {
                    $claimCount += $wishlist->gifts()
                        ->whereHas('claims', fn ($q) => $q->whereNotNull('confirmed_at'))
                        ->count();
                }
            }
        }

        return view('exchanges.status', [
            'exchange' => $exchange,
            'claimCount' => $claimCount,
        ]);
    }

    public function reveal(string $locale, string $token): View
    {
        $participant = GiftExchangeParticipant::where('token', $token)
            ->with(['exchange', 'assignedTo'])
            ->firstOrFail();

        if ($participant->isTokenExpired()) {
            abort(410, __('This link has expired. Ask the organizer for a new one.'));
        }

        $participant->markAsViewed();

        /** @var GiftExchangeParticipant|null $assignedTo */
        $assignedTo = $participant->assignedTo;
        $wishlist = $assignedTo?->defaultWishlist();
        $wishlistGiftCount = $wishlist?->gifts()->count() ?? 0;

        $participantHasWishlist = false;
        if ($participant->user_id !== null) {
            $participantHasWishlist = (bool) $participant->defaultWishlist();
        }

        return view('exchanges.reveal', [
            'participant' => $participant,
            'exchange' => $participant->exchange,
            'assignedTo' => $assignedTo,
            'wishlist' => $wishlist,
            'wishlistGiftCount' => $wishlistGiftCount,
            'participantHasWishlist' => $participantHasWishlist,
        ]);
    }
}
