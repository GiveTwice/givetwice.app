<?php

use App\Actions\ReserveGiftAction;
use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Mail\GiftExchangeInviteMail;
use App\Mail\InactiveAccountWarningMail;
use App\Mail\ListInvitationMail;
use App\Models\Gift;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\ReservationAlert;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;

describe('recipient mail language', function () {
    it('retains recipient language through queue serialization independently of worker language', function (string $locale) {
        app()->setLocale('en');
        $recipient = User::factory()->create(['locale_preference' => $locale]);
        $exchange = GiftExchange::factory()->create(['locale' => 'fr']);
        $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id, 'user_id' => $recipient->id]);
        $invitation = ListInvitation::factory()->create(['invitee_id' => $recipient->id]);
        foreach ([new GiftExchangeInviteMail($participant, $exchange), new ExchangeRevealReminderMail($participant, $exchange), new ExchangeWishlistNudgeMail($participant, $exchange), new ListInvitationMail($invitation), new InactiveAccountWarningMail($recipient)] as $mail) {
            expect(unserialize(serialize($mail))->locale)->toBe($locale);
            $sent = (new MailManager(app()))->mailer('array')->to($recipient)->send($mail);
            $message = $sent->getSymfonySentMessage()->getOriginalMessage();
            expect($message->getHtmlBody())->not->toBeEmpty()
                ->and($message->getTextBody())->not->toBeEmpty();
        }
    })->with(['nl', 'fr', 'en']);

    it('uses the group language for guests', function () {
        $exchange = GiftExchange::factory()->create(['locale' => 'nl']);
        $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id, 'user_id' => null]);
        expect((new GiftExchangeInviteMail($participant, $exchange))->locale)->toBe('nl');
    });

    it('uses account language for password recovery and verification notifications', function () {
        Notification::fake();
        $recipient = User::factory()->create(['locale_preference' => 'nl']);
        $recipient->sendPasswordResetNotification('token');
        $recipient->sendEmailVerificationNotification();
        expect($recipient->preferredLocale())->toBe('nl');
        Notification::assertSentTo($recipient, ResetPassword::class);
        Notification::assertSentTo($recipient, VerifyEmail::class, fn ($notification) => $notification->locale === 'nl');
    });
});

it('keeps a guest reservation language when an owner edits in another language', function (string $locale) {
    Mail::fake();
    Queue::fake();
    app()->setLocale($locale);
    $gift = Gift::factory()->skipped()->create();
    $list = GiftList::factory()->create();
    $list->gifts()->attach($gift);
    $claim = app(ReserveGiftAction::class)->execute($gift, 'guest@example.test');
    app()->setLocale($locale === 'en' ? 'nl' : 'en');
    $gift->update(['title' => 'Changed']);
    expect($claim->fresh()->reservation['locale'])->toBe($locale)
        ->and(ReservationAlert::sole()->locale)->toBe($locale);
})->with(['nl', 'fr', 'en']);

it('keeps recovery tokens and verification signatures usable in branded multipart notifications', function () {
    $recipient = User::factory()->create(['locale_preference' => 'nl']);
    $reset = (new ResetPassword('reset-token'))->toMail($recipient);
    $resetUrl = $reset->viewData['actionUrl'];
    parse_str(parse_url($resetUrl, PHP_URL_QUERY), $query);
    expect($query['email'])->toBe($recipient->email)
        ->and(parse_url($resetUrl, PHP_URL_PATH))->toBe('/nl/reset-password/reset-token');

    $verification = (new VerifyEmail)->toMail($recipient);
    $request = Request::create($verification->viewData['actionUrl']);
    expect(URL::hasValidSignature($request))->toBeTrue();
    $this->travel(61)->minutes();
    expect(URL::hasValidSignature($request))->toBeFalse();

    foreach ([$reset, $verification] as $message) {
        expect($message->view)->toHaveCount(2);
        foreach ($message->view as $view) {
            expect(view($view, $message->viewData)->render())->not->toBeEmpty();
        }
    }
});
