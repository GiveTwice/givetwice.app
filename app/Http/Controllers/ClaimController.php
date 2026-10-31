<?php

namespace App\Http\Controllers;

use App\Actions\ClaimGiftAction;
use App\Actions\ConfirmClaimAction;
use App\Actions\ReserveGiftAction;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\ClaimException;
use App\Http\Requests\ReservationRequest;
use App\Mail\ReservationMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Support\EmailSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function store(ReservationRequest $request, string $locale, Gift $gift): View|RedirectResponse
    {
        return $this->storeAnonymous($request, $locale, $gift);
    }

    public function showConfirmed(Request $request, string $locale, Gift $gift, ?string $token = null): View|RedirectResponse
    {
        if ($request->user() && ($gift->user_id === $request->user()->id || $gift->hasAccessibleListFor($request->user()))) {
            return $this->showAnonymousForm($request, $locale, $gift);
        }
        $claim = $token ? $gift->claims()->where('confirmation_token', $token)->whereNotNull('confirmed_at')->first() : $this->browserClaim($request, $gift);
        if (! $claim) {
            return redirect()->route('claim.anonymous.form', ['locale' => $locale, 'gift' => $gift]);
        }

        return $this->sheet($request, $gift, $claim);
    }

    public function showAnonymousForm(Request $request, string $locale, Gift $gift): View|RedirectResponse
    {
        if ($request->user() && ($gift->user_id === $request->user()->id || $gift->hasAccessibleListFor($request->user()))) {
            return redirect($gift->lists()->first()?->getPublicUrl($locale) ?? route('dashboard.locale', ['locale' => $locale]));
        }

        return $this->sheet($request, $gift, $this->browserClaim($request, $gift));
    }

    public function storeAnonymous(ReservationRequest $request, string $locale, Gift $gift): View|RedirectResponse
    {
        if ($request->user() && ($gift->user_id === $request->user()->id || $gift->hasAccessibleListFor($request->user()))) {
            return redirect()->route('dashboard.locale', ['locale' => $locale])->with('error', __('You cannot claim your own gift.'));
        }
        if ($claim = $this->browserClaim($request, $gift)) {
            return $this->sheet($request, $gift, $claim);
        }
        $email = strtolower(trim($request->user() ? $request->user()->email : $request->validated('email')));
        $this->limit($request, $email);
        try {
            $claim = $request->user()
                ? app(ClaimGiftAction::class)->execute($gift, $request->user())
                : app(ReserveGiftAction::class)->execute($gift, $email);
        } catch (ClaimException $e) {
            if (! $e instanceof AlreadyClaimedException) {
                abort(409, $e->getMessage());
            }

            return $this->sheet($request, $gift);
        }
        Cookie::queue('reservation_'.$gift->id, $claim->confirmation_token, 60 * 24 * 365);
        Cookie::queue('reservation_email', $email, 60 * 24 * 90);
        if ($request->header('X-Reservation-Sheet')) {
            return $this->sheet($request, $gift, $claim);
        }

        return redirect()->route('claim.confirmed', ['locale' => $locale, 'gift' => $gift, 'list' => $request->integer('list') ?: null]);
    }

    public function updateEmail(ReservationRequest $request, string $locale, Gift $gift): View|RedirectResponse
    {
        $claim = $this->browserClaim($request, $gift);
        abort_unless($claim !== null, 403);
        $email = strtolower(trim($request->validated('email')));
        $this->limit($request, $email, 'mail');
        $claim->update(['claimer_email' => $email, 'user_id' => $claim->user?->email === $email ? $claim->user_id : null, 'confirmation_token' => $claim->confirmation_token ?? Str::random(64)]);
        Cookie::queue('reservation_'.$gift->id, $claim->confirmation_token, 60 * 24 * 365);
        Cookie::queue('reservation_email', $email, 60 * 24 * 90);
        Mail::to($email)->queue((new ReservationMail($claim, $locale))->locale($locale));

        return $this->sheet($request, $gift, $claim, __('Email sent.'));
    }

    public function resend(Request $request, string $locale, Gift $gift): View
    {
        $claim = $this->browserClaim($request, $gift);
        abort_unless($claim !== null, 403);
        $this->limit($request, $claim->claimer_email ?? $claim->user->email, 'mail');
        Mail::to($claim->claimer_email ?? $claim->user->email)->queue((new ReservationMail($claim, $locale))->locale($locale));

        return $this->sheet($request, $gift, $claim, __('Email sent.'));
    }

    public function destroy(Request $request, string $locale, Gift $gift): View|RedirectResponse
    {
        $claim = $this->browserClaim($request, $gift);
        abort_unless($claim !== null, 403);
        $claim->delete();
        Cookie::queue(Cookie::forget('reservation_'.$gift->id));

        return $this->sheet($request, $gift, notice: __('Reservation cancelled. The gift is free again.'));
    }

    public function undo(Request $request, string $locale, Claim $claim): View|RedirectResponse
    {
        abort_unless(hash_equals(hash('sha256', $claim->claimer_email ?? $claim->user->email), $request->string('email')->toString()), 403);
        if ($request->isMethod('post')) {
            $gift = $claim->gift;
            $claim->delete();

            return redirect()->route('claim.anonymous.form', ['locale' => $locale, 'gift' => $gift])->with('success', __('Reservation cancelled. The gift is free again.'));
        }

        return view('claims.undo', ['claim' => $claim]);
    }

    public function forgetEmail(): Response
    {
        return response()->noContent()->withCookie(Cookie::forget('reservation_email'));
    }

    private function browserClaim(Request $request, Gift $gift): ?Claim
    {
        return $gift->claims()->whereNotNull('confirmed_at')->where(function ($query) use ($request, $gift) {
            $query->where('confirmation_token', $request->cookie('reservation_'.$gift->id) ?: 'invalid');
            if ($request->user()) {
                $query->orWhere('user_id', $request->user()->id);
            }
        })->first();
    }

    private function limit(Request $request, string $email, string $purpose = 'reserve'): void
    {
        foreach (['device:'.$request->session()->getId() => 5, 'email:'.hash('sha256', $email) => 5, 'ip:'.$request->ip() => 30] as $key => $max) {
            $key = $purpose.':'.$key;
            abort_if(RateLimiter::tooManyAttempts($key, $max), 429, __('Too many reservations. Try again in an hour.'));
            RateLimiter::hit($key, 3600);
        }
    }

    private function sheet(Request $request, Gift $gift, ?Claim $claim = null, ?string $notice = null): View
    {
        $lists = $gift->lists()->with('creator:id,name');
        $list = $request->integer('list') ? $lists->whereKey($request->integer('list'))->firstOrFail() : $lists->first();
        $late = ! $claim && $gift->isClaimed();
        $alternatives = $late && $list ? $list->gifts()->with('media')->where('gifts.id', '!=', $gift->id)
            ->where(fn ($query) => $query->where('allow_multiple_claims', true)->orWhereDoesntHave('claims', fn ($query) => $query->whereNotNull('confirmed_at')))
            ->get()->sortBy(fn (Gift $other) => $other->price_in_cents === null ? PHP_INT_MAX - 1 : ($other->currency === $gift->currency && $gift->price_in_cents !== null ? abs($other->price_in_cents - $gift->price_in_cents) : PHP_INT_MAX))->take(3) : collect();
        $email = $claim ? ($claim->claimer_email ?? $claim->user->email) : $request->cookie('reservation_email', '');

        return view($request->header('X-Reservation-Sheet') ? 'claims.sheet-content' : 'claims.anonymous', compact('gift', 'claim', 'list', 'late', 'alternatives', 'email', 'notice') + ['suggestion' => EmailSuggestion::for($email)]);
    }

    public function confirm(string $locale, string $token, ConfirmClaimAction $action): View|RedirectResponse
    {
        try {
            $claim = $action->execute($token);
        } catch (ClaimException $e) {
            return view('claims.invalid-token');
        }

        Cookie::queue('reservation_'.$claim->gift_id, $claim->confirmation_token, 60 * 24 * 365);

        return redirect()->route('claim.confirmed', ['locale' => $locale, 'gift' => $claim->gift, 'token' => $claim->confirmation_token]);
    }
}
