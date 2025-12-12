<?php

use App\Actions\FetchGiftDetailsAction;
use App\Events\GiftCreated;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

describe('Gift creation', function () {
    it('emits GiftCreated event when a gift is created', function () {
        Event::fake([GiftCreated::class]);

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/123',
        ]);

        Event::assertDispatched(GiftCreated::class, function ($event) use ($gift) {
            return $event->gift->id === $gift->id;
        });
    });

    it('dispatches FetchGiftDetailsAction when gift is created', function () {
        Queue::fake();

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/456',
        ]);

        Queue::assertPushed(FetchGiftDetailsAction::class, function ($job) use ($gift) {
            return $job->gift->id === $gift->id;
        });
    });

    it('dispatches FetchGiftDetailsAction to the fetch queue', function () {
        Queue::fake();

        $user = User::factory()->create();

        Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/789',
        ]);

        Queue::assertPushedOn('fetch', FetchGiftDetailsAction::class);
    });

    it('creates gift with pending fetch status by default', function () {
        Queue::fake();

        $user = User::factory()->create();

        $gift = Gift::create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product/abc',
        ]);

        expect($gift->fetch_status)->toBe('pending');
        expect($gift->isPending())->toBeTrue();
    });
});
