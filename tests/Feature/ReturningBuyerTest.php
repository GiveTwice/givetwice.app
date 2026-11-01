<?php

use App\Mail\ReservationMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->list = GiftList::factory()->create();
    $this->list->users()->attach($this->list->creator_id);
    $this->gift = Gift::factory()->skipped()->create();
    $this->list->gifts()->attach($this->gift);
    $this->claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
});

it('recognizes only a proved browser reservation and keeps the response private', function () {
    $url = $this->list->getPublicUrl('en');
    $this->withCookie('reservation_email', $this->claim->claimer_email)->get($url)->assertOk()
        ->assertViewHas('myReservationGiftIds', fn ($ids) => $ids->isEmpty());
    $this->withCookie('reservation_'.$this->gift->id, $this->claim->confirmation_token)->get($url)->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertViewHas('myReservationGiftIds', fn ($ids) => $ids->all() === [$this->gift->id]);
    $this->actingAs($this->list->creator)->get($url)->assertOk()
        ->assertViewHas('myReservationGiftIds', fn ($ids) => $ids->isEmpty());
});

it('explains expired and already cancelled undo links without changing any reservation', function () {
    $url = (new ReservationMail($this->claim, 'en'))->undoUrl;
    $historyUrl = $this->claim->historyUrl('en');
    $this->travel(31)->days();
    $this->get($historyUrl)->assertForbidden()->assertViewIs('claims.link-unavailable');
    expect($this->claim->fresh())->not->toBeNull();
    $this->travelBack();
    $this->claim->delete();
    $this->get($url)->assertGone()->assertViewIs('claims.link-unavailable');
});
