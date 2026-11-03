<?php

use App\Actions\ClaimGiftAction;
use App\Actions\ReserveGiftAction;
use App\Jobs\SendReservationReceipt;
use App\Mail\ReservationMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    $this->freezeTime();
    $this->list = GiftList::factory()->create();
    $this->reserve = function (string $email = 'buyer@example.test', ?GiftList $list = null): Claim {
        $list ??= $this->list;
        $gift = Gift::factory()->skipped()->create(['user_id' => $list->creator_id]);
        $list->gifts()->attach($gift);

        return app(ReserveGiftAction::class)->execute($gift, $email);
    };
});

it('sends one receipt after ten quiet minutes with a separate usable undo for every gift', function () {
    $first = ($this->reserve)();
    $this->travel(9)->minutes();
    $second = ($this->reserve)();
    $this->travel(1)->minute();
    $job = new SendReservationReceipt($first->id);
    $job->handle();
    Mail::assertNothingOutgoing();
    $this->travel(9)->minutes();
    $job->handle();
    (new SendReservationReceipt($second->id))->handle();
    Mail::assertSent(ReservationMail::class, function ($mail) use ($first, $second) {
        expect($mail->claims->modelKeys())->toBe([$first->id, $second->id]);
        $this->post($mail->undoUrls->get($first->id))->assertRedirect();
        expect($first->fresh())->toBeNull()->and($second->fresh())->not->toBeNull();
        $this->post($mail->undoUrls->get($second->id))->assertRedirect();
        expect($second->fresh())->toBeNull();

        return $mail->hasTo('buyer@example.test');
    });
    Mail::assertSentCount(1);
});

it('keeps different buyers and lists in separate receipts', function () {
    $first = ($this->reserve)();
    $otherBuyer = ($this->reserve)('other@example.test');
    $otherList = ($this->reserve)('buyer@example.test', GiftList::factory()->create());
    $this->travel(10)->minutes();
    foreach ([$first, $otherBuyer, $otherList] as $claim) {
        (new SendReservationReceipt($claim->id))->handle();
    }
    Mail::assertSentCount(3);
    Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->claims->count() === 1);
});

it('bundles account reservations through the same receipt flow', function () {
    $buyer = User::factory()->create();
    foreach (range(1, 2) as $number) {
        $gift = Gift::factory()->skipped()->create(['user_id' => $this->list->creator_id]);
        $this->list->gifts()->attach($gift);
        $claim = app(ClaimGiftAction::class)->execute($gift, $buyer);
    }
    $this->travel(10)->minutes();
    (new SendReservationReceipt($claim->id))->handle();
    Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->claims->count() === 2);
    Mail::assertSentCount(1);
});

it('omits cancelled and removed reservations and follows corrected email addresses', function (string $change) {
    $first = ($this->reserve)();
    $second = ($this->reserve)();
    match ($change) {
        'cancelled' => $first->delete(),
        'removed' => $first->gift->delete(),
        'corrected' => $first->update(['claimer_email' => 'corrected@example.test']),
    };
    if ($change === 'corrected') {
        SendReservationReceipt::request($first, 'nl');
    }
    $this->travel(10)->minutes();
    (new SendReservationReceipt($second->id))->handle();
    Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->claims->modelKeys() === [$second->id]);
    if ($change === 'corrected') {
        (new SendReservationReceipt($first->id))->handle();
        Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->hasTo('corrected@example.test'));
    }
})->with(['cancelled', 'removed', 'corrected']);

it('can resend a receipt without repeating already delivered gifts', function () {
    $first = ($this->reserve)();
    $second = ($this->reserve)();
    $this->travel(10)->minutes();
    $job = new SendReservationReceipt($first->id);
    $job->handle();
    SendReservationReceipt::request($first, 'fr');
    $job->handle();
    Mail::assertSentCount(1);
    $this->travel(10)->minutes();
    $job->handle();
    expect($second->fresh()->receipt_sent_at)->not->toBeNull();
    Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->locale === 'fr');
    Mail::assertSentCount(2);
});

it('renders multipart receipts with each gifts undo link in all locales', function (string $locale) {
    $first = ($this->reserve)();
    $second = ($this->reserve)();
    $claims = Claim::with('gift.media')->whereKey([$first->id, $second->id])->get();
    $mail = new ReservationMail($claims->first(), $locale, $claims);
    $sent = (new MailManager(app()))->mailer('array')->to($first->claimer_email)->send($mail);
    $message = $sent->getSymfonySentMessage()->getOriginalMessage();
    foreach ($mail->undoUrls as $url) {
        expect($message->getTextBody())->toContain($url);
        expect(html_entity_decode($message->getHtmlBody()))->toContain($url);
    }
})->with(['en', 'nl', 'fr']);

it('can resend older account reservations without a stored claimer address', function () {
    $buyer = User::factory()->create();
    $gift = Gift::factory()->skipped()->create(['user_id' => $this->list->creator_id]);
    $this->list->gifts()->attach($gift);
    $claim = Claim::factory()->create(['gift_id' => $gift->id, 'user_id' => $buyer->id]);
    Claim::query()->whereKey($claim->id)->update(['claimer_email' => null]);
    SendReservationReceipt::request($claim, 'en');
    $this->travel(10)->minutes();
    (new SendReservationReceipt($claim->id))->handle();
    Mail::assertSent(ReservationMail::class, fn ($mail) => $mail->hasTo($buyer->email));
});
