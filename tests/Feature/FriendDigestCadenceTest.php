<?php

use App\Jobs\SendListUpdate;
use App\Mail\FriendDigestMail;
use App\Models\FollowedList;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->travelTo(now('Europe/Brussels')->setDate(2026, 11, 3)->setTime(12, 0));
    Mail::fake();
    Queue::fake();
    $this->recipient = User::factory()->create();
    $this->pendingList = function (array $attributes = []): FollowedList {
        $list = GiftList::factory()->create($attributes);
        $follow = $this->recipient->followedLists()->create(['list_id' => $list->id]);
        $this->actingAs($list->creator);
        $gift = Gift::factory()->skipped()->create(['user_id' => $list->creator_id]);
        $list->gifts()->attach($gift);

        return $follow;
    };
});

it('dispatches once per recipient and bundles followed and managed lists without duplicates', function () {
    $first = ($this->pendingList)();
    $second = ($this->pendingList)();
    $second->list->users()->attach($this->recipient);
    $this->travel(10)->minutes();
    $this->artisan('friends:send-digest')->assertSuccessful();
    Queue::assertPushed(SendListUpdate::class, fn ($job) => $job->followId === $first->id);
    expect(Queue::pushed(SendListUpdate::class)->filter(fn ($job) => FollowedList::find($job->followId)->user_id === $this->recipient->id))->toHaveCount(1);
    $job = new SendListUpdate($first->id);
    $job->handle();
    $job->handle();
    Mail::assertSent(FriendDigestMail::class, function ($mail) use ($first, $second) {
        expect($mail->digestData->pluck('list.id')->all())->toBe([$first->list_id, $second->list_id]);

        return $mail->hasTo($this->recipient->email);
    });
    Mail::assertSentCount(1);
    expect($this->recipient->fresh()->last_friend_digest_at)->toEqual(now());
});

it('enforces the weekly and near-occasion caps at dispatch and delivery', function (?int $occasionDays, int $hours, bool $due) {
    $follow = ($this->pendingList)(['occasion_date' => $occasionDays === null ? null : now()->addDays($occasionDays)]);
    $this->travel(10)->minutes();
    $this->recipient->update(['last_friend_digest_at' => now()->subHours($hours)]);
    $this->artisan('friends:send-digest')->assertSuccessful();
    expect(Queue::pushed(SendListUpdate::class)->contains(fn ($job) => $job->followId === $follow->id))->toBe($due);
    (new SendListUpdate($follow->id))->handle();
    expect(Mail::sent(FriendDigestMail::class)->count())->toBe($due ? 1 : 0);
})->with([
    'weekly capped' => [null, 167, false], 'weekly due' => [null, 168, true],
    'near capped' => [30, 23, false], 'near due' => [30, 24, true],
    'today' => [0, 24, true], 'distant' => [31, 24, false], 'past' => [-1, 24, false],
]);

it('waits overnight and sends at the opening of the Brussels window including summer time', function (string $date) {
    $follow = ($this->pendingList)();
    $this->travelTo(Carbon::parse($date.' 20:00', 'Europe/Brussels'));
    $this->artisan('friends:send-digest')->assertSuccessful();
    Queue::assertNothingPushed();
    (new SendListUpdate($follow->id))->handle();
    Mail::assertNothingSent();
    $this->travelTo(now('Europe/Brussels')->addDay()->setTime(8, 59));
    (new SendListUpdate($follow->id))->handle();
    Mail::assertNothingSent();
    $this->travel(1)->minute();
    (new SendListUpdate($follow->id))->handle();
    Mail::assertSent(FriendDigestMail::class, 1);
})->with(['2026-11-03', '2027-06-03']);

it('keeps an actively edited list pending while sending another quiet list', function () {
    $quiet = ($this->pendingList)();
    $this->travel(5)->minutes();
    $active = ($this->pendingList)();
    $this->travel(5)->minutes();
    (new SendListUpdate($quiet->id))->handle();
    Mail::assertSent(FriendDigestMail::class, fn ($mail) => $mail->digestData->pluck('list.id')->all() === [$quiet->list_id]);
    expect($active->fresh()->last_change_id)->toBe(0);
});

it('counts the recipients own recent edits toward the quiet period', function () {
    $follow = ($this->pendingList)();
    $this->travel(9)->minutes();
    $this->actingAs($this->recipient);
    $follow->list->gifts->first()->update(['title' => 'Still editing']);
    $this->travel(1)->minute();
    (new SendListUpdate($follow->id))->handle();
    Mail::assertNothingSent();
});

it('does not dispatch or deliver for muted unverified or disabled recipients', function (string $reason) {
    $follow = ($this->pendingList)();
    match ($reason) {
        'muted' => $follow->update(['notifications' => false]),
        'unverified' => $this->recipient->update(['email_verified_at' => null]),
        'disabled' => $this->recipient->update(['friend_notifications_enabled' => false]),
    };
    $this->travel(10)->minutes();
    $this->artisan('friends:send-digest')->assertSuccessful();
    Queue::assertNotPushed(SendListUpdate::class, fn ($job) => $job->followId === $follow->id);
    (new SendListUpdate($follow->id))->handle();
    Mail::assertNothingSent();
})->with(['muted', 'unverified', 'disabled']);
