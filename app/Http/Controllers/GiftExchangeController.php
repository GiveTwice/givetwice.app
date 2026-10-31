<?php

namespace App\Http\Controllers;

use App\Actions\CreateGiftExchangeAction;
use App\Actions\PerformDrawAction;
use App\Http\Requests\ExchangeParticipantRequest;
use App\Http\Requests\StoreGiftExchangeRequest;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GiftExchangeController extends Controller
{
    public function addParticipant(ExchangeParticipantRequest $request, string $locale, GiftExchange $exchange): RedirectResponse
    {
        $this->authorize('manage', $exchange);

        if ($exchange->participants()->count() >= 50) {
            return back()->withErrors(['participant' => __('This group has reached its maximum number of participants.')]);
        }

        $validated = $request->validated();

        $email = filled($validated['email'] ?? null) ? strtolower(trim($validated['email'])) : null;

        if ($email && $exchange->participants()->where('email', $email)->exists()) {
            return back()->withErrors(['email' => __('This email is already in the group.')]);
        }

        $existingUser = $email ? User::where('email', $email)->first() : null;

        GiftExchangeParticipant::create([
            'exchange_id' => $exchange->id,
            'user_id' => $existingUser?->id,
            'name' => trim($validated['name']),
            'email' => $email,
        ]);

        return back()->with('success', __(':name has been added to the group.', ['name' => $validated['name']]));
    }

    public function removeParticipant(string $locale, GiftExchange $exchange, GiftExchangeParticipant $participant): RedirectResponse
    {
        $this->authorize('manage', $exchange);

        if ($participant->exchange_id !== $exchange->id) {
            abort(404);
        }

        if ($exchange->participants()->count() <= 3) {
            return back()->withErrors(['participant' => __('A group needs at least 3 participants.')]);
        }

        $name = $participant->name;

        // Also remove any exclusions involving this participant
        $exchange->exclusions()
            ->where('giver_id', $participant->id)
            ->orWhere(function ($query) use ($exchange, $participant) {
                $query->where('exchange_id', $exchange->id)
                    ->where('receiver_id', $participant->id);
            })
            ->delete();

        $participant->delete();

        return back()->with('success', __(':name has been removed from the group.', ['name' => $name]));
    }

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
        StoreGiftExchangeRequest $request,
        string $locale,
        string $exchangeType,
        CreateGiftExchangeAction $action,
    ): RedirectResponse {
        $organizerParticipates = $request->boolean('organizer_participates');
        $validated = $request->validated();

        if ($organizerParticipates) {
            $organizerEmail = strtolower($request->user()->email);
            $listedOrganizer = collect($validated['participants'])
                ->contains(fn (array $participant) => strtolower(trim($participant['email'] ?? '')) === $organizerEmail);

            if ($listedOrganizer) {
                throw ValidationException::withMessages([
                    'participants' => __('You\'re already in the group as the organizer - no need to add yourself again.'),
                ]);
            }
        }

        $budgetAmount = ($validated['budget_amount'] ?? null)
            ? (int) ($validated['budget_amount'] * 100)
            : null;

        $exchange = $action->execute(
            organizer: $request->user(),
            name: $validated['name'],
            eventDate: $validated['event_date'] ?? null,
            locale: $locale,
            participants: $validated['participants'],
            budgetAmount: $budgetAmount,
            budgetCurrency: $validated['budget_currency'] ?? 'EUR',
            organizerParticipates: $organizerParticipates,
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

        try {
            $action->execute($exchange);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['draw' => __('These rules leave no valid draw. Remove a rule and try again.')]);
        }

        return redirect()
            ->route('exchanges.status', ['locale' => $locale, 'exchange' => $exchange->slug])
            ->with('success', __('Drawn! Send everyone their private link'));
    }

    public function status(string $locale, GiftExchange $exchange): View
    {
        $this->authorize('viewStatus', $exchange);

        $exchange->load('participants.exchange', 'exclusions.giver', 'exclusions.receiver');

        return view('exchanges.status', ['exchange' => $exchange]);
    }

    public function showJoinForm(string $locale, string $joinToken): View
    {
        $exchange = GiftExchange::where('join_token', $joinToken)->firstOrFail();

        if (! $exchange->isDraft()) {
            abort(410, __('This group has already drawn names. You can no longer join.'));
        }

        $exchange->load('participants');

        return view('exchanges.join', [
            'exchange' => $exchange,
            'joinToken' => $joinToken,
        ]);
    }

    public function join(ExchangeParticipantRequest $request, string $locale, string $joinToken): RedirectResponse
    {
        $exchange = GiftExchange::where('join_token', $joinToken)->firstOrFail();

        if (! $exchange->isDraft()) {
            abort(410, __('This group has already drawn names. You can no longer join.'));
        }

        if ($exchange->participants()->count() >= 50) {
            abort(422, __('This group has reached its maximum number of participants.'));
        }

        $validated = $request->validated();

        $email = filled($validated['email'] ?? null) ? strtolower(trim($validated['email'])) : null;

        if ($email && $exchange->participants()->where('email', $email)->exists()) {
            return back()->withInput()->withErrors([
                'email' => __('This email is already in the group.'),
            ]);
        }

        $existingUser = $email ? User::where('email', $email)->first() : null;

        GiftExchangeParticipant::create([
            'exchange_id' => $exchange->id,
            'user_id' => $existingUser?->id,
            'name' => trim($validated['name']),
            'email' => $email,
        ]);

        return back()->with('success', __('You\'ve joined :name!', ['name' => $exchange->name]));
    }

    public function reveal(Request $request, string $locale, string $token): Response
    {
        $participant = GiftExchangeParticipant::forReveal($token);
        $revealed = $request->isMethod('post');
        if ($revealed) {
            $participant->markAsViewed();
        }

        if ($revealed) {
            $participant->load('assignedTo.wishlist');
        }
        $assignedTo = $revealed ? $participant->assignedTo : null;
        $wishlist = $assignedTo?->defaultWishlist();

        return response()->view('exchanges.reveal', [
            'participant' => $participant,
            'exchange' => $participant->exchange,
            'assignedTo' => $assignedTo,
            'wishlist' => $wishlist,
            'revealed' => $revealed,
            'participantHasWishlist' => (bool) $participant->defaultWishlist(),
            'gifts' => $wishlist?->gifts()->with('media')->limit(2)->get(),
        ])->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
