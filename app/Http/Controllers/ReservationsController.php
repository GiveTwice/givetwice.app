<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationLinkRequest;
use App\Mail\ReservationLinkMail;
use App\Models\Claim;
use App\Models\FollowedList;
use App\Models\GiftList;
use App\Models\ReservationAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ReservationsController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->has('email')) {
            if (! $request->hasValidSignature()) {
                return response()->view('claims.link-unavailable', [], 403)->header('Cache-Control', 'private, no-store');
            }
            $request->session()->put('reservations_email', $request->string('email')->toString());

            return redirect()->route('reservations.index', ['locale' => app()->getLocale()]);
        }
        $email = $request->user()?->hasVerifiedEmail() ? $request->user()->email : $request->session()->get('reservations_email');
        $claims = $email ? Claim::with('gift.lists', 'user', 'alerts')->whereNotNull('confirmed_at')->where(fn ($query) => $query->where('claimer_email', $email)->when($request->user()?->hasVerifiedEmail(), fn ($query) => $query->orWhere('user_id', $request->user()->id)))->get()
            ->reject(fn (Claim $claim) => $request->user() && ($claim->reservation['owner_id'] === $request->user()->id || $claim->gift?->hasAccessibleListFor($request->user()))) : collect();

        return response()->view('claims.history', ['email' => $email, 'groups' => $claims->sortByDesc(fn (Claim $claim) => $claim->latestAlert() !== null || ($claim->isRemoved() && ! $claim->resolution))->groupBy('reservation.list_id')])->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function link(ReservationLinkRequest $request): RedirectResponse
    {
        Mail::to(strtolower(trim($request->validated('email'))))->queue((new ReservationLinkMail(strtolower(trim($request->validated('email'))), app()->getLocale()))->locale(app()->getLocale()));

        return back()->with('success', __('reservations.sent'));
    }

    public function mute(Request $request, string $locale, ReservationAlert $alert): View|RedirectResponse
    {
        abort_unless(hash_equals(hash('sha256', $alert->email), $request->string('email')->toString()), 403);
        if (! $request->isMethod('post')) {
            return view('claims.mute', compact('alert'));
        }
        $alert->update(['muted_at' => now()]);
        FollowedList::where('list_id', $alert->list_id)->whereHas('user', fn ($query) => $query->where('email', $alert->email)->whereNotNull('email_verified_at'))->update(['notifications' => false]);

        return redirect()->route('reservations.index', ['locale' => $locale])->with('success', __('reservations.muted'));
    }

    public function choose(Request $request, string $locale, Claim $claim, string $choice): View|RedirectResponse
    {
        return $claim->getConnection()->transaction(function () use ($request, $locale, $claim, $choice) {
            if ($request->isMethod('post')) {
                $claim->refreshForUpdate();
            }
            abort_unless(hash_equals(hash('sha256', $claim->claimer_email ?? $claim->user->email), $request->string('email')->toString()), 403);
            abort_if($request->user() && ($claim->reservation['owner_id'] === $request->user()->id || $claim->gift?->hasAccessibleListFor($request->user())), 403);
            $alert = $request->integer('alert') ? $claim->alerts()->findOrFail($request->integer('alert')) : null;
            abort_unless(in_array($choice, $alert ? ($alert->after['deleted'] ? ['review', 'keep', 'alternative', 'dismiss'] : ['review', 'new', 'old', 'release']) : ['keep', 'alternative', 'dismiss'], true), 404);
            if ($alert && ! $request->isMethod('post')) {
                return view('claims.alert', compact('claim', 'choice', 'alert'));
            }
            if ($alert) {
                abort_if($choice === 'review', 405);
                abort_if($alert->resolved_at || $claim->latestAlert()?->id !== $alert->id, 409);
                $claim->update(['reservation' => $choice === 'new' ? $alert->after : ($choice === 'keep' && $claim->resolution === 'keep' ? $claim->reservation : $alert->before)]);
                $claim->alerts()->whereNull('resolved_at')->update(['resolved_at' => now()]);
                $choice = ['new' => 'keep', 'old' => 'keep', 'release' => 'dismiss'][$choice] ?? $choice;
            }
            if (! $request->isMethod('post')) {
                return view('claims.choice', compact('claim', 'choice'));
            }
            $historyUrl = $claim->historyUrl($locale);
            $list = GiftList::find($claim->reservation['list_id']);
            if ($choice === 'keep') {
                $claim->update(['resolution' => 'keep']);
            } else {
                $claim->delete();
            }

            return redirect($choice === 'alternative' && $list ? $list->getPublicUrl($locale).'?filter=free' : $historyUrl)->with('success', __('reservations.'.$choice.'_done', ['owner' => $claim->reservation['owner']]));
        });
    }
}
