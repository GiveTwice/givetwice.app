<?php

namespace App\Http\Controllers;

use App\Actions\ClaimGiftAction;
use App\Actions\ConfirmClaimAction;
use App\Actions\CreatePendingClaimAction;
use App\Exceptions\ClaimException;
use App\Models\Gift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function store(Request $request, string $locale, Gift $gift, ClaimGiftAction $action): RedirectResponse
    {
        try {
            $action->execute($gift, $request->user(), $request->input('notes'));
        } catch (ClaimException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Gift claimed! The owner won\'t see who claimed it.'));
    }

    public function destroy(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        $claim = $gift->claims()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $claim) {
            return back()->with('error', __('You have not claimed this gift.'));
        }

        $claim->delete();

        return back()->with('success', __('Claim removed.'));
    }

    public function showAnonymousForm(string $locale, Gift $gift): View|RedirectResponse
    {
        if ($gift->isClaimed()) {
            return back()->with('error', __('This gift has already been claimed.'));
        }

        $list = $gift->lists()->first();

        return view('claims.anonymous', [
            'gift' => $gift,
            'list' => $list,
        ]);
    }

    public function storeAnonymous(
        Request $request,
        string $locale,
        Gift $gift,
        CreatePendingClaimAction $action
    ): RedirectResponse {
        // 5 attempts per minute per IP
        $rateLimitKey = 'claim-attempt:'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return back()->with('error', __('Too many claim attempts. Please try again later.'));
        }
        RateLimiter::hit($rateLimitKey, 60);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $action->execute($gift, $validated['email'], $validated['name'] ?? null);
        } catch (ClaimException $e) {
            $flashType = $e->isResent ? 'success' : 'error';

            return back()->with($flashType, $e->getMessage());
        }

        /** @var \App\Models\GiftList|null $list */
        $list = $gift->lists()->first();

        return redirect()
            ->route('public.list', ['locale' => $locale, 'list' => $list])
            ->with('success', __('Please check your email to confirm your claim.'));
    }

    public function confirm(string $locale, string $token, ConfirmClaimAction $action): View
    {
        try {
            $claim = $action->execute($token);
        } catch (ClaimException $e) {
            return view('claims.invalid-token');
        }

        return view('claims.confirmed', [
            'claim' => $claim,
            'gift' => $claim->gift,
        ]);
    }
}
