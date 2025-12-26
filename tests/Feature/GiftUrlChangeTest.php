<?php

use App\Actions\FetchGiftDetailsAction;
use App\Events\GiftUrlChanged;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

describe('Gift URL change', function () {
    beforeEach(function () {
        Queue::fake();
    });

    it('dispatches GiftUrlChanged event when url is updated', function () {
        Event::fake([GiftUrlChanged::class]);

        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/old-product',
        ]);

        $gift->update(['url' => 'https://example.com/new-product']);

        Event::assertDispatched(GiftUrlChanged::class, function ($event) use ($gift) {
            return $event->gift->id === $gift->id;
        });
    });

    it('does not dispatch GiftUrlChanged when url remains the same', function () {
        Event::fake([GiftUrlChanged::class]);

        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product',
        ]);

        $gift->update(['title' => 'New Title']);

        Event::assertNotDispatched(GiftUrlChanged::class);
    });

    it('dispatches FetchGiftDetailsAction when url is updated', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/old-product',
        ]);

        Queue::assertPushed(FetchGiftDetailsAction::class);
        Queue::fake();

        $gift->update(['url' => 'https://example.com/new-product']);

        Queue::assertPushed(FetchGiftDetailsAction::class, function ($job) use ($gift) {
            return $job->gift->id === $gift->id;
        });
    });

    it('does not dispatch FetchGiftDetailsAction when other fields are updated', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/product',
        ]);

        Queue::assertPushed(FetchGiftDetailsAction::class);
        Queue::fake();

        $gift->update(['title' => 'Updated Title', 'description' => 'Updated description']);

        Queue::assertNotPushed(FetchGiftDetailsAction::class);
    });
});
