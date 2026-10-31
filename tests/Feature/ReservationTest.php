<?php

use App\Events\GiftClaimed;
use App\Mail\ClaimConfirmationMail;
use App\Mail\GaveTwiceMail;
use App\Mail\ReservationMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use App\Support\EmailSuggestion;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\SlackAlerts\Facades\SlackAlert;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    SlackAlert::fake();
    $this->list = GiftList::factory()->create(['spoiler_mode' => false]);
    $this->list->users()->attach($this->list->creator);
    $this->gift = Gift::factory()->create(['user_id' => $this->list->creator_id, 'price_in_cents' => 2500, 'currency' => 'EUR']);
    $this->list->gifts()->attach($this->gift);
    $this->url = '/en/gifts/'.$this->gift->id;
    $this->withHeader('X-Reservation-Sheet', '1');
});

it('reserves immediately and sends one receipt without alerting the owner', function () {
    $this->post($this->url.'/claim-anonymous', ['email' => 'Buyer@gmial.com'])->assertOk()->assertCookie('reservation_'.$this->gift->id)->assertCookie('reservation_email', 'buyer@gmial.com');
    $claim = $this->gift->claims()->sole();
    expect($this->gift->fresh()->isClaimed())->toBeTrue();
    expect($claim->isConfirmed())->toBeTrue();
    Mail::assertQueued(ReservationMail::class, fn ($mail) => $mail->hasTo('buyer@gmial.com'));
    Mail::assertQueuedCount(1);
    Mail::assertNotSent(ClaimConfirmationMail::class);
    Mail::assertNotQueued(GaveTwiceMail::class);
    expect(collect((new GiftClaimed($this->gift, $claim, 1))->broadcastOn())->pluck('name')->all())->not->toContain('private-user.'.$this->list->creator_id);
    $this->actingAs($this->list->creator)->get($this->list->getPublicUrl('en'))->assertDontSee('data-reservation-progress', false)->assertDontSee('data-reserved-heading', false);
    $this->get($this->url.'/claimed')->assertRedirect($this->list->getPublicUrl('en'));
});

it('gives the second buyer free alternatives from this list ordered by price distance', function () {
    $near = Gift::factory()->create(['price_in_cents' => 2300, 'currency' => 'EUR']);
    $far = Gift::factory()->create(['price_in_cents' => 700, 'currency' => 'EUR']);
    $taken = Gift::factory()->create(['price_in_cents' => 2500, 'currency' => 'EUR']);
    $this->list->gifts()->attach([$far->id, $taken->id, $near->id]);
    Claim::factory()->create(['gift_id' => $taken->id]);
    $this->post($this->url.'/claim-anonymous', ['email' => 'first@example.test'])->assertOk();
    $response = $this->post($this->url.'/claim-anonymous?list='.$this->list->id, ['email' => 'second@example.test'])->assertOk();
    expect($response->viewData('late'))->toBeTrue();
    expect($response->viewData('alternatives')->pluck('id')->all())->toBe([$near->id, $far->id]);
    expect($this->gift->claims()->count())->toBe(1);
    Mail::assertQueuedCount(1);
});

it('limits reservations across gifts by device and normalized email', function (string $dimension) {
    $this->withSession(['device' => true])->withCookie(config('session.cookie'), session()->getId())->withCredentials();
    for ($i = 0; $i < 6; $i++) {
        $gift = Gift::factory()->create();
        $this->list->gifts()->attach($gift);
        if ($dimension === 'email') {
            $this->withCookie(config('session.cookie'), Str::random(40));
        }
        $response = $this->postJson('/en/gifts/'.$gift->id.'/claim-anonymous', ['email' => $dimension === 'device' ? "buyer{$i}@example.test" : 'BUYER@example.test']);
        $i === 5 ? $response->assertTooManyRequests() : $response->assertOk();
        expect($gift->fresh()->isClaimed())->toBe($i !== 5);
    }
})->with(['device', 'email']);

it('requires this browser for undo, email changes and resend', function (string $method, string $suffix) {
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
    $this->{$method}($this->url.$suffix, ['email' => 'new@example.test'])->assertForbidden();
    $this->withCookie('reservation_email', $claim->claimer_email)->{$method}($this->url.$suffix, ['email' => 'new@example.test'])->assertForbidden();
    $this->withCookie('reservation_'.$this->gift->id, 'forged')->{$method}($this->url.$suffix, ['email' => 'new@example.test'])->assertForbidden();
    $this->withCookie('reservation_'.$this->gift->id, $claim->confirmation_token)->{$method}($this->url.$suffix, ['email' => 'new@example.test'])->assertOk();
    if ($method === 'delete') {
        expect($this->gift->fresh()->isClaimed())->toBeFalse();
    } else {
        expect($this->gift->fresh()->isClaimed())->toBeTrue();
        Mail::assertQueuedCount(1);
        expect($claim->fresh()->claimer_email)->toBe($method === 'patch' ? 'new@example.test' : $claim->claimer_email);
    }
})->with([['delete', '/reservation'], ['patch', '/reservation'], ['post', '/reservation/resend']]);

it('rejects tampered cookies and limits email changes and resends', function () {
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
    $this->withUnencryptedCookie('reservation_'.$this->gift->id, $claim->confirmation_token)->post($this->url.'/reservation/resend')->assertForbidden();
    $this->unencryptedCookies = [];
    $this->withCookie(config('session.cookie'), session()->getId());
    $this->withCookie('reservation_'.$this->gift->id, $claim->confirmation_token);
    for ($i = 0; $i < 5; $i++) {
        $this->post($this->url.'/reservation/resend')->assertOk();
    }
    $this->patch($this->url.'/reservation', ['email' => 'new@example.test'])->assertTooManyRequests();
});

it('allows signed mail undo from another browser and invalidates old mail links after email changes', function () {
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
    $mail = new ReservationMail($claim);
    $this->get($mail->undoUrl)->assertOk();
    expect($this->gift->isClaimed())->toBeTrue();
    $this->get(str_replace('email=', 'email=forged', $mail->undoUrl))->assertForbidden();
    $this->withCookie('reservation_'.$this->gift->id, $claim->confirmation_token)->patch($this->url.'/reservation', ['email' => 'fixed@example.test'])->assertOk();
    $this->post($mail->undoUrl)->assertForbidden();
    $this->post((new ReservationMail($claim->fresh()))->undoUrl)->assertRedirect();
    expect($this->gift->fresh()->isClaimed())->toBeFalse();
});

it('only prefills remembered email and can forget it', function () {
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
    $this->withCookie('reservation_email', $claim->claimer_email)->get($this->url.'/claim')->assertViewHas('claim', null)->assertViewHas('late', true);
    $other = Gift::factory()->create(['url' => null, 'original_image_url' => null, 'price_in_cents' => null, 'owner_note' => 'A personal note']);
    $this->list->gifts()->attach($other);
    $this->get('/en/gifts/'.$other->id.'/claim')->assertOk()->assertViewHas('email', $claim->claimer_email)->assertViewHas('claim', null)->assertSee('A personal note');
    $this->delete('/en/reservation-email')->assertNoContent()->assertCookieExpired('reservation_email');
});

it('keeps old pending links working and links new reservations only on verified signup', function () {
    $pending = Claim::factory()->anonymous()->pending()->create(['gift_id' => $this->gift->id]);
    $this->get('/en/claim/confirm/'.$pending->confirmation_token)->assertRedirect();
    expect($pending->fresh()->isConfirmed())->toBeTrue();
    $user = User::factory()->unverified()->create(['email' => $pending->claimer_email]);
    expect($pending->fresh()->user_id)->toBeNull();
    event(new Verified($user));
    expect($pending->fresh()->user_id)->toBe($user->id);
});

it('lets a signed in buyer reserve in one tap and reopen their reservation', function () {
    $buyer = User::factory()->create();
    $this->actingAs($buyer)->post($this->url.'/claim')->assertOk();
    $this->get($this->url.'/claim')->assertViewHas('claim', fn ($claim) => $claim->user_id === $buyer->id);
    $this->delete($this->url.'/reservation')->assertOk();
    expect($this->gift->fresh()->isClaimed())->toBeFalse();
});

it('suggests common email typos without changing the address', function (string $domain, ?string $expected) {
    expect(EmailSuggestion::for('buyer@'.$domain))->toBe($expected ? 'buyer@'.$expected : null);
})->with(['gmial.com' => ['gmial.com', 'gmail.com'], 'gmal.com' => ['gmal.com', 'gmail.com'], 'hotmial.com' => ['hotmial.com', 'hotmail.com'], 'outlok.com' => ['outlok.com', 'outlook.com'], 'telent.be' => ['telent.be', 'telenet.be'], 'skyent.be' => ['skyent.be', 'skynet.be'], 'proxims.be' => ['proxims.be', 'proximus.be'], 'correct' => ['gmail.com', null], 'unknown' => ['private.test', null]]);

it('keeps a reservation when an email correction is invalid', function () {
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
    $this->withCookie('reservation_'.$this->gift->id, $claim->confirmation_token)->patchJson($this->url.'/reservation', ['email' => 'broken'])->assertUnprocessable();
    expect($claim->fresh()->claimer_email)->toBe($claim->claimer_email);
    expect($this->gift->fresh()->isClaimed())->toBeTrue();
    Mail::assertNothingOutgoing();
});

it('preserves multiple reservations without granting another browser control by email', function () {
    $this->gift->update(['allow_multiple_claims' => true]);
    $this->postJson($this->url.'/claim-anonymous', ['email' => 'first@example.test'])->assertOk();
    $this->postJson($this->url.'/claim-anonymous', ['email' => 'second@example.test'])->assertOk();
    $this->postJson($this->url.'/claim-anonymous', ['email' => 'FIRST@example.test'])->assertConflict();
    expect($this->gift->claims()->count())->toBe(2);
    $this->get($this->url.'/claim')->assertViewHas('claim', null);
});

it('links an instant reservation to an account after verification', function () {
    $this->post($this->url.'/claim-anonymous', ['email' => 'new@example.test'])->assertOk();
    $claim = $this->gift->claims()->sole();
    $user = User::factory()->unverified()->create(['email' => 'new@example.test']);
    $this->actingAs($user)->get($this->url.'/claimed')->assertRedirect();
    expect($claim->fresh()->user_id)->toBeNull();
    event(new Verified($user));
    expect($claim->fresh()->user_id)->toBe($user->id);
});
