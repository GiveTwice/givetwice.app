<?php

namespace App\Http\Controllers;

use App\Mail\ClaimConfirmationMail;
use App\Models\Claim;
use App\Models\Gift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ClaimController extends Controller
{
    /**
     * Claim a gift (for registered users).
     */
    public function store(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        $user = $request->user();

        // Prevent claiming own gifts
        if ($gift->user_id === $user->id) {
            return back()->with('error', __('You cannot claim your own gift.'));
        }

        // Check if gift is already claimed
        if ($gift->isClaimed()) {
            return back()->with('error', __('This gift has already been claimed.'));
        }

        // Check if user already has a pending claim on this gift
        $existingClaim = $gift->claims()
            ->where('user_id', $user->id)
            ->first();

        if ($existingClaim) {
            return back()->with('error', __('You have already claimed this gift.'));
        }

        // Create the claim (auto-confirmed for registered users)
        Claim::create([
            'gift_id' => $gift->id,
            'user_id' => $user->id,
            'confirmed_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        return back()->with('success', __('Gift claimed! The owner won\'t see who claimed it.'));
    }

    /**
     * Unclaim a gift (for registered users).
     */
    public function destroy(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        $user = $request->user();

        $claim = $gift->claims()
            ->where('user_id', $user->id)
            ->first();

        if (! $claim) {
            return back()->with('error', __('You have not claimed this gift.'));
        }

        $claim->delete();

        return back()->with('success', __('Claim removed.'));
    }

    /**
     * Show the anonymous claim form.
     */
    public function showAnonymousForm(string $locale, Gift $gift): View|RedirectResponse
    {
        // Check if gift is already claimed
        if ($gift->isClaimed()) {
            return back()->with('error', __('This gift has already been claimed.'));
        }

        // Get the list for the back link
        $list = $gift->lists()->first();

        return view('claims.anonymous', [
            'gift' => $gift,
            'list' => $list,
        ]);
    }

    /**
     * Store an anonymous claim (requires email confirmation).
     */
    public function storeAnonymous(Request $request, string $locale, Gift $gift): RedirectResponse
    {
        // Rate limiting: 5 attempts per minute per IP
        $key = 'claim-attempt:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->with('error', __('Too many claim attempts. Please try again later.'));
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        // Check if gift is already claimed
        if ($gift->isClaimed()) {
            return back()->with('error', __('This gift has already been claimed.'));
        }

        // Check if this email already has a pending claim on this gift
        $existingClaim = $gift->claims()
            ->where('claimer_email', $validated['email'])
            ->first();

        if ($existingClaim) {
            /** @var Claim $existingClaim */
            if ($existingClaim->isConfirmed()) {
                return back()->with('error', __('This gift has already been claimed with this email.'));
            }
            // Resend confirmation email
            Mail::to($validated['email'])->send(new ClaimConfirmationMail($existingClaim));

            return back()->with('success', __('A confirmation email has been resent to your email address.'));
        }

        // Create pending claim
        $claim = Claim::create([
            'gift_id' => $gift->id,
            'claimer_email' => $validated['email'],
            'claimer_name' => $validated['name'],
        ]);

        // Send confirmation email
        Mail::to($validated['email'])->send(new ClaimConfirmationMail($claim));

        // Get the list for the redirect
        /** @var \App\Models\GiftList|null $list */
        $list = $gift->lists()->first();

        return redirect()
            ->route('public.list', ['locale' => $locale, 'slug' => $list?->slug ?? 'unknown'])
            ->with('success', __('Please check your email to confirm your claim.'));
    }

    /**
     * Confirm an anonymous claim via token.
     */
    public function confirm(string $locale, string $token): View|RedirectResponse
    {
        $claim = Claim::where('confirmation_token', $token)
            ->whereNull('confirmed_at')
            ->first();

        if (! $claim) {
            return view('claims.invalid-token');
        }

        // Check if gift was claimed by someone else in the meantime
        /** @var \App\Models\Gift $gift */
        $gift = $claim->gift;
        if ($gift->isClaimed()) {
            $claim->delete();

            return view('claims.already-claimed');
        }

        // Confirm the claim
        $claim->confirm();

        return view('claims.confirmed', [
            'claim' => $claim,
            'gift' => $claim->gift,
        ]);
    }
}
