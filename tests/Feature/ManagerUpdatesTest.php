<?php

use App\Jobs\SendListUpdate;
use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

describe('manager content updates', function () {
    beforeEach(function () {
        Mail::fake();
        Queue::fake()->except([SendListUpdate::class]);
        $this->mom = User::factory()->create();
        $this->partner = User::factory()->create();
        $this->list = GiftList::factory()->create(['creator_id' => $this->mom->id]);
        $this->list->users()->attach([$this->mom->id, $this->partner->id]);
        $this->actingAs($this->partner);
        $this->gift = Gift::factory()->skipped()->create(['user_id' => $this->partner->id]);
        $this->list->gifts()->attach($this->gift);
    });

    it('notifies the other parent once, without mailing the editor', function () {
        $this->gift->update(['title' => 'New title']);
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertSent(FriendDigestMail::class, fn ($mail) => $mail->hasTo($this->mom->email));
        Mail::assertSentCount(1);
    });

    it('never adds reservation changes or buyer information to a parent bundle', function () {
        $buyer = User::factory()->create();
        $this->gift->claims()->create(['user_id' => $buyer->id, 'confirmed_at' => now()]);
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertSent(FriendDigestMail::class, function ($mail) use ($buyer) {
            $data = $mail->digestData->sole();
            expect(array_keys($data))->toBe(['list', 'added_gifts', 'edited_gifts', 'removed_gifts', 'mute_url']);
            foreach ($data['added_gifts'] as $change) {
                expect(array_keys($change->getAttributes()))->not->toContain('claimer_email', 'reservation', 'confirmed_at');
            }

            return $mail->hasTo($this->mom->email) && ! $mail->hasTo($buyer->email);
        });
        Mail::fake();
        $this->gift->claims()->delete();
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertNothingSent();
    });

    it('honours a manager mute and stops after removing their access', function (bool $mute) {
        $this->artisan('friends:send-digest')->assertSuccessful();
        if ($mute) {
            $follow = FollowedList::where('user_id', $this->mom->id)->sole();
            $this->post($follow->muteUrl())->assertOk();
        } else {
            $this->list->update(['creator_id' => $this->partner->id]);
            $this->list->users()->detach($this->mom);
        }
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        Mail::assertNothingSent();
    })->with([true, false]);
});
