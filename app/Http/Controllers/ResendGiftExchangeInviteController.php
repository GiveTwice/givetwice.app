<?php

namespace App\Http\Controllers;

use App\Mail\GiftExchangeInviteMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ResendGiftExchangeInviteController extends Controller
{
    public function __invoke(string $locale, GiftExchange $exchange, GiftExchangeParticipant $participant): RedirectResponse
    {
        $this->authorize('viewStatus', $exchange);

        if (! $exchange->isDrawn()) {
            return back()->withErrors(['resend' => __('Invites can only be resent after names have been drawn.')]);
        }

        if ($participant->exchange_id !== $exchange->id) {
            abort(404);
        }

        if ($participant->hasViewed()) {
            return back()->withErrors(['resend' => __(':name has already viewed their invite.', ['name' => $participant->name])]);
        }

        $rateLimitKey = 'resend-invite:'.$exchange->id.':'.$participant->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 1)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return back()->withErrors([
                'resend' => __('Please wait :seconds seconds before resending to :name.', [
                    'seconds' => $seconds,
                    'name' => $participant->name,
                ]),
            ]);
        }

        RateLimiter::hit($rateLimitKey, decaySeconds: 600);

        Mail::to($participant->email)
            ->queue(new GiftExchangeInviteMail($participant, $exchange));

        return back()->with('success', __('Invite resent to :name.', ['name' => $participant->name]));
    }
}
