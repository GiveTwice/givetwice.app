<?php

use App\Events\GiftClaimed;
use App\Jobs\SendListUpdate;
use App\Jobs\SendReservationAlert;
use App\Mail\FriendDigestMail;
use App\Mail\ReservationAlertMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\ReservationAlert;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Queue::fake()->except([SendListUpdate::class]);
    $this->list = GiftList::factory()->create();
    $this->list->users()->attach($this->list->creator_id);
    $this->gift = Gift::factory()->create(['user_id' => $this->list->creator_id, 'title' => 'Old title', 'url' => 'https://shop.example/old']);
    $this->list->gifts()->attach($this->gift);
    $this->claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id]);
});

it('alerts only reserved gifts on relevant changes', function (string $field, mixed $value, bool $alerted) {
    $this->gift->update([$field => $value]);
    expect(ReservationAlert::count())->toBe($alerted ? 1 : 0);
    $free = Gift::factory()->create();
    $free->update([$field => $value]);
    expect(ReservationAlert::count())->toBe($alerted ? 1 : 0);
})->with([['title', 'New title', true], ['price_in_cents', 1999, true], ['url', 'https://shop.example/new', true], ['owner_note', 'Bordeaux please', true], ['description', 'Scraped description', false], ['original_image_url', 'https://shop.example/photo.jpg', false]]);

it('batches ten minutes into one delayed mail with the first and final values', function () {
    $this->freezeTime();
    $this->gift->update(['title' => 'Second']);
    $this->travel(9)->minutes();
    $this->gift->update(['title' => 'Final', 'price_in_cents' => 1999]);
    Queue::assertPushed(SendReservationAlert::class, 1);
    Queue::assertPushed(SendReservationAlert::class, fn ($job) => $job->delay->equalTo(now()->addMinute()));
    $alert = ReservationAlert::sole();
    expect($alert->before['title'])->toBe('Old title')->and($alert->after['title'])->toBe('Final');
    $this->travel(10)->minutes();
    (new SendReservationAlert($alert->id))->handle();
    (new SendReservationAlert($alert->id))->handle();
    Mail::assertSent(ReservationAlertMail::class, 1);
    Mail::assertSent(ReservationAlertMail::class, fn ($mail) => $mail->hasTo($this->claim->claimer_email));
    expect($alert->fresh()->sent_at)->not->toBeNull();
    $this->travel(2)->minutes();
    $this->gift->update(['owner_note' => 'Another batch']);
    expect(ReservationAlert::count())->toBe(2);
});

it('does not mail reverted changes, cancelled reservations, or corrected addresses', function (string $case) {
    $this->gift->update(['title' => 'Changed']);
    $alert = ReservationAlert::sole();
    match ($case) {
        'reverted' => $this->gift->update(['title' => 'Old title']),
        'cancelled' => $this->claim->delete(),
        'corrected' => $this->claim->update(['claimer_email' => 'corrected@example.test']),
    };
    (new SendReservationAlert($alert->id))->handle();
    Mail::assertNothingOutgoing();
})->with(['reverted', 'cancelled', 'corrected']);

it('confirms edited gift decisions without notifying or broadcasting to the owner', function (string $choice) {
    $this->gift->update(['title' => 'New title']);
    $alert = ReservationAlert::sole();
    Event::fake();
    $this->get($alert->choiceUrl($choice))->assertOk();
    expect($this->claim->fresh()->reservation['title'])->toBe('Old title');
    $this->post($alert->choiceUrl($choice))->assertRedirect();
    expect($this->claim->fresh()?->reservation['title'])->toBe(match ($choice) {
        'new' => 'New title', 'old' => 'Old title', default => null
    });
    Mail::assertNothingOutgoing();
    Event::assertNotDispatched(GiftClaimed::class);
    Event::assertNotDispatched(BroadcastEvent::class);
})->with(['new', 'old', 'release']);

it('records gift and list deletions and confirms opt-out for only that list', function (bool $deleteList) {
    $deleteList ? $this->list->delete() : $this->gift->delete();
    $alert = ReservationAlert::sole();
    expect($alert->after['deleted'])->toBeTrue();
    $this->get($alert->choiceUrl('mute'))->assertOk();
    expect($alert->fresh()->muted_at)->toBeNull();
    $this->post($alert->choiceUrl('mute'))->assertRedirect();
    (new SendReservationAlert($alert->id))->handle();
    Mail::assertNothingOutgoing();
})->with([true, false]);

it('dedupes the digest for a buyer but keeps news for another follower', function (string $state) {
    $buyer = User::factory()->create(['email' => $this->claim->claimer_email, 'last_friend_digest_at' => now()->subDay()]);
    $other = User::factory()->create();
    foreach ([$buyer, $other] as $user) {
        $user->followedLists()->create(['list_id' => $this->list->id, 'notifications' => true]);
    }
    $this->gift->delete();
    $alert = ReservationAlert::sole();
    if ($state === 'sent') {
        (new SendReservationAlert($alert->id))->handle();
    } elseif ($state === 'released') {
        $this->post($alert->choiceUrl('dismiss'))->assertRedirect();
    }
    $this->travel(11)->minutes();
    $this->artisan('friends:send-digest')->assertSuccessful();
    Mail::assertSent(FriendDigestMail::class, 1);
    Mail::assertSent(FriendDigestMail::class, fn ($mail) => $mail->hasTo($other->email));
})->with(['pending', 'sent', 'released']);

it('keeps opt-out after cancellation, applies it to future alerts, and honours follower settings', function () {
    $buyer = User::factory()->create(['email' => $this->claim->claimer_email]);
    $follow = $buyer->followedLists()->create(['list_id' => $this->list->id, 'notifications' => true]);
    $this->gift->update(['title' => 'Changed']);
    $alert = ReservationAlert::sole();
    $url = $alert->choiceUrl('mute');
    $this->claim->delete();
    $this->post($url)->assertRedirect();
    expect($follow->fresh()->notifications)->toBeFalse();
    $claim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gift->id, 'claimer_email' => $buyer->email]);
    $this->gift->update(['title' => 'Again']);
    (new SendReservationAlert($claim->alerts()->sole()->id))->handle();
    Mail::assertNothingOutgoing();
    $otherList = GiftList::factory()->create();
    $otherGift = Gift::factory()->create();
    $otherList->gifts()->attach($otherGift);
    $otherClaim = Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $otherGift->id, 'claimer_email' => $buyer->email]);
    $otherGift->update(['title' => 'Different list']);
    $this->travel(11)->minutes();
    (new SendReservationAlert($otherClaim->alerts()->sole()->id))->handle();
    Mail::assertSent(ReservationAlertMail::class, 1);
});

it('rejects old decision links after another edit and keeps digest dedupe after release', function () {
    $this->gift->update(['title' => 'First']);
    $first = ReservationAlert::sole();
    $this->travel(11)->minutes();
    $this->gift->update(['title' => 'Second']);
    $this->post($first->choiceUrl('old'))->assertConflict();
    $this->post(ReservationAlert::latest('id')->first()->choiceUrl('release'))->assertRedirect();
    expect(ReservationAlert::where('email', $this->claim->claimer_email)->count())->toBe(2);
});

it('bundles two changed reservations for one buyer and list after the last edit', function () {
    $second = Gift::factory()->skipped()->create(['user_id' => $this->list->creator_id]);
    $this->list->gifts()->attach($second);
    Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $second->id, 'claimer_email' => $this->claim->claimer_email]);
    $this->gift->update(['title' => 'Changed first']);
    $this->travel(9)->minutes();
    $second->update(['title' => 'Changed second']);
    $this->travel(1)->minutes();
    $first = ReservationAlert::first();
    (new SendReservationAlert($first->id))->handle();
    Mail::assertNothingSent();
    $this->travel(9)->minutes();
    (new SendReservationAlert($first->id))->handle();
    (new SendReservationAlert(ReservationAlert::latest('id')->first()->id))->handle();
    Mail::assertSent(ReservationAlertMail::class, fn ($mail) => $mail->alerts->count() === 2);
    Mail::assertSentCount(1);
});

it('never sends a reservation bundle to a current manager', function () {
    $this->claim->update(['claimer_email' => $this->list->creator->email]);
    $this->gift->update(['title' => 'Changed']);
    $this->travel(11)->minutes();
    (new SendReservationAlert(ReservationAlert::sole()->id))->handle();
    Mail::assertNothingSent();
});
