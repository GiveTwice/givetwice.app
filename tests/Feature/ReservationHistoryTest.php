<?php

use App\Events\GiftClaimed;
use App\Events\GiftUnclaimed;
use App\Mail\ReservationLinkMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    $this->list = GiftList::factory()->create();
    $this->list->users()->attach($this->list->creator_id);
    $this->gift = Gift::factory()->create(['user_id' => $this->list->creator_id]);
    $this->list->gifts()->attach($this->gift);
    $this->claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
});

it('requires mailbox proof and rejects expired or tampered history links', function () {
    $this->withCookie('reservation_email', $this->claim->claimer_email)->withCookie('reservation_'.$this->gift->id, $this->claim->confirmation_token)
        ->get('/en/reservations')->assertOk()->assertViewHas('email', null);
    $url = $this->claim->historyUrl('en');
    $this->get(str_replace('email=', 'email=other', $url))->assertForbidden();
    $this->travel(31)->days();
    $this->get($url)->assertForbidden();
    $this->travelBack();
    $this->get($url)->assertRedirect('/en/reservations');
    $this->get('/en/reservations')->assertViewHas('groups', fn ($groups) => $groups->flatten()->sole()->id === $this->claim->id);
});

it('mails a magic link without exposing history and allows verified login', function () {
    $this->post('/en/reservations/link', ['email' => $this->claim->claimer_email])->assertRedirect();
    Mail::assertQueued(ReservationLinkMail::class, fn ($mail) => $mail->hasTo($this->claim->claimer_email));
    $this->get('/en/reservations')->assertViewHas('email', null);
    $mail = new ReservationLinkMail($this->claim->claimer_email, 'en');
    $this->get($mail->content()->with['historyUrl'])->assertRedirect();
    $this->get('/en/reservations')->assertViewHas('groups', fn ($groups) => $groups->flatten()->count() === 1);
    $this->flushSession();
    $buyer = User::factory()->create(['email' => $this->claim->claimer_email]);
    $this->actingAs($buyer)->get('/en/reservations')->assertViewHas('groups', fn ($groups) => $groups->flatten()->count() === 1);
    $this->actingAs(User::factory()->unverified()->create())->get('/en/reservations')->assertViewHas('email', null);
});

it('retains the snapshot through gift, list and database cascade deletion', function (string $target) {
    if ($target === 'list') {
        $this->actingAs($this->list->creator)->delete(route('list.destroy', ['locale' => 'en', 'list' => $this->list]))->assertRedirect();
    } elseif ($target === 'cascade') {
        $this->list->creator->delete();
    } else {
        $this->gift->{$target}();
    }
    expect($this->claim->fresh()->isRemoved())->toBeTrue()
        ->and($this->claim->fresh()->reservation['title'])->toBe($this->gift->title);
})->with(['delete', 'forceDelete', 'list', 'cascade']);

it('confirms each choice without owner signals and never acts on GET', function (string $choice) {
    $this->gift->delete();
    Event::fake();
    $url = URL::signedRoute('reservations.choose', ['locale' => 'en', 'claim' => $this->claim, 'choice' => $choice, 'email' => hash('sha256', $this->claim->claimer_email)]);
    $this->get($url)->assertOk();
    expect($this->claim->fresh()->resolution)->toBeNull();
    $response = $this->post($url)->assertRedirect();
    if ($choice === 'keep') {
        expect($this->claim->fresh()->resolution)->toBe('keep');
    } else {
        expect($this->claim->fresh())->toBeNull();
    }
    if ($choice === 'alternative') {
        $response->assertRedirect($this->list->getPublicUrl('en').'?filter=free');
    }
    Mail::assertNothingOutgoing();
    Event::assertNotDispatched(GiftClaimed::class);
    Event::assertNotDispatched(GiftUnclaimed::class);
})->with(['keep', 'alternative', 'dismiss']);

it('denies another mailbox and the owner access to choices', function () {
    $url = URL::signedRoute('reservations.choose', ['locale' => 'en', 'claim' => $this->claim, 'choice' => 'keep', 'email' => hash('sha256', 'other@example.test')]);
    $this->post($url)->assertForbidden();
    $url = URL::signedRoute('reservations.choose', ['locale' => 'en', 'claim' => $this->claim, 'choice' => 'keep', 'email' => hash('sha256', $this->claim->claimer_email)]);
    $this->actingAs($this->list->creator)->get($url)->assertForbidden();
});

it('delivers the same expiring history link in both mail alternatives', function (string $locale) {
    $mail = (new ReservationLinkMail($this->claim->claimer_email, $locale))->locale($locale);
    $url = $mail->content()->with['historyUrl'];
    $sent = $mail->to($this->claim->claimer_email)->send((new MailManager(app()))->mailer('array'));
    $message = $sent->getSymfonySentMessage()->getOriginalMessage();
    expect($message->getHtmlBody())->not->toBeEmpty()
        ->and($message->getTextBody())->toContain($url);
    $this->get($url)->assertRedirect();
    $this->travel(31)->minutes();
    $this->get($url)->assertForbidden();
})->with(['nl', 'fr', 'en']);
