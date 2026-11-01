<?php

use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

describe('list update bundles', function () {
    beforeEach(function () {
        Mail::fake();
        Queue::fake();
        $this->owner = User::factory()->create();
        $this->list = GiftList::factory()->create(['creator_id' => $this->owner->id]);
        $this->follower = User::factory()->create(['locale_preference' => 'nl']);
        $this->follow = FollowedList::create(['user_id' => $this->follower->id, 'list_id' => $this->list->id]);
        $this->actingAs($this->owner);
    });

    it('bundles additions and edits after ten quiet minutes, once', function () {
        $gift = Gift::factory()->skipped()->create(['user_id' => $this->owner->id]);
        $this->list->gifts()->attach($gift);
        $this->travel(9)->minutes();
        $gift->update(['title' => 'New title']);
        $this->travel(2)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertNothingQueued();
        $this->travel(8)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertQueued(FriendDigestMail::class, fn ($mail) => $mail->hasTo($this->follower->email)
            && $mail->locale === 'nl' && $mail->digestData->sole()['edited_gifts']->sole()->gift_id === $gift->id);
        Mail::assertQueuedCount(1);
    });

    it('skips the actor, unverified recipients and both notification opt-outs', function (string $reason) {
        match ($reason) {
            'actor' => $this->actingAs($this->follower),
            'unverified' => $this->follower->update(['email_verified_at' => null]),
            'global' => $this->follower->update(['friend_notifications_enabled' => false]),
            'list' => $this->follow->update(['notifications' => false]),
        };
        $gift = Gift::factory()->skipped()->create();
        $this->list->gifts()->attach($gift);
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertNothingQueued();
    })->with(['actor', 'unverified', 'global', 'list']);

    it('lets a signed recipient mute without logging in, after confirmation', function () {
        $url = $this->follow->muteUrl();
        $this->get($url)->assertOk();
        expect($this->follow->fresh()->notifications)->toBeTrue();
        $this->post($url)->assertOk();
        expect($this->follow->fresh()->notifications)->toBeFalse();
        $this->post($url.'&tampered=1')->assertForbidden();
    });
});
