<?php

use App\Actions\ConfirmClaimAction;
use App\Events\GiftClaimed;
use App\Events\GiftCreated;
use App\Events\GiftFetchCompleted;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

// Prevent GiftCreated from queuing jobs during tests
beforeEach(function () {
    Queue::fake();
});

describe('GiftClaimed event', function () {
    it('is dispatched when a registered user claims a gift', function () {
        Event::fake([GiftClaimed::class]);

        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create(['user_id' => $owner->id]);
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);
        $gift->lists()->attach($list->id);

        $this->actingAs($claimer)
            ->post("/en/gifts/{$gift->id}/claim");

        Event::assertDispatched(GiftClaimed::class, function ($event) use ($gift) {
            return $event->gift->id === $gift->id;
        });
    });

    it('is dispatched when an anonymous claim is confirmed', function () {
        Event::fake([GiftClaimed::class]);

        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);
        $gift->lists()->attach($list->id);

        $claim = Claim::create([
            'gift_id' => $gift->id,
            'claimer_email' => 'test@example.com',
            'claimer_name' => 'Test User',
        ]);

        $action = new ConfirmClaimAction;
        $action->execute($claim->confirmation_token);

        Event::assertDispatched(GiftClaimed::class, function ($event) use ($gift, $claim) {
            return $event->gift->id === $gift->id && $event->claim->id === $claim->id;
        });
    });

    it('broadcasts to owner private channel', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);
        $claim = Claim::factory()->create(['gift_id' => $gift->id]);

        $event = new GiftClaimed($gift, $claim, $gift->getConfirmedClaimCount());
        $channels = $event->broadcastOn();

        $privateChannels = array_filter($channels, fn ($ch) => $ch instanceof PrivateChannel);
        $privateChannelNames = array_map(fn ($ch) => $ch->name, $privateChannels);

        expect($privateChannelNames)->toContain('private-user.'.$owner->id);
    });

    it('broadcasts to public list channels for all associated lists', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $list1 = GiftList::factory()->create(['creator_id' => $owner->id]);
        $list2 = GiftList::factory()->create(['creator_id' => $owner->id]);
        $gift->lists()->attach([$list1->id, $list2->id]);
        $gift->load('lists');

        $claim = Claim::factory()->create(['gift_id' => $gift->id]);

        $event = new GiftClaimed($gift, $claim, $gift->getConfirmedClaimCount());
        $channels = $event->broadcastOn();

        $publicChannels = array_filter($channels, fn ($ch) => $ch instanceof Channel && ! $ch instanceof PrivateChannel);
        $publicChannelNames = array_map(fn ($ch) => $ch->name, $publicChannels);

        expect($publicChannelNames)->toContain('list.'.$list1->fresh()->slug);
        expect($publicChannelNames)->toContain('list.'.$list2->fresh()->slug);
    });

    it('broadcasts as gift.claimed event', function () {
        $gift = Gift::factory()->create();
        $claim = Claim::factory()->create(['gift_id' => $gift->id]);

        $event = new GiftClaimed($gift, $claim, $gift->getConfirmedClaimCount());

        expect($event->broadcastAs())->toBe('gift.claimed');
    });

    it('includes gift id and title in broadcast payload', function () {
        $gift = Gift::factory()->create(['title' => 'Test Gift']);
        $claim = Claim::factory()->create(['gift_id' => $gift->id]);

        $event = new GiftClaimed($gift, $claim, $gift->getConfirmedClaimCount());
        $payload = $event->broadcastWith();

        expect($payload)->toHaveKey('gift');
        expect($payload['gift']['id'])->toBe($gift->id);
        expect($payload['gift']['title'])->toBe('Test Gift');
        expect($payload['claimed'])->toBeTrue();
    });
});

describe('GiftFetchCompleted event', function () {
    it('broadcasts to owner private channel', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $event = new GiftFetchCompleted($gift);
        $channels = $event->broadcastOn();

        $privateChannels = array_filter($channels, fn ($ch) => $ch instanceof PrivateChannel);
        $privateChannelNames = array_map(fn ($ch) => $ch->name, $privateChannels);

        expect($privateChannelNames)->toContain('private-user.'.$owner->id);
    });

    it('broadcasts to public list channels for all associated lists', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $list1 = GiftList::factory()->create(['creator_id' => $owner->id]);
        $list2 = GiftList::factory()->create(['creator_id' => $owner->id]);
        $gift->lists()->attach([$list1->id, $list2->id]);
        $gift->load('lists');

        $event = new GiftFetchCompleted($gift);
        $channels = $event->broadcastOn();

        $publicChannels = array_filter($channels, fn ($ch) => $ch instanceof Channel && ! $ch instanceof PrivateChannel);
        $publicChannelNames = array_map(fn ($ch) => $ch->name, $publicChannels);

        expect($publicChannelNames)->toContain('list.'.$list1->fresh()->slug);
        expect($publicChannelNames)->toContain('list.'.$list2->fresh()->slug);
    });

    it('does not broadcast to list channels when gift has no lists', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $event = new GiftFetchCompleted($gift);
        $channels = $event->broadcastOn();

        $publicChannels = array_filter($channels, fn ($ch) => $ch instanceof Channel && ! $ch instanceof PrivateChannel);

        expect($publicChannels)->toBeEmpty();
    });

    it('broadcasts as gift.fetch.completed event', function () {
        $gift = Gift::factory()->create();

        $event = new GiftFetchCompleted($gift);

        expect($event->broadcastAs())->toBe('gift.fetch.completed');
    });

    it('includes complete gift data in broadcast payload', function () {
        $gift = Gift::factory()->create([
            'title' => 'Test Product',
            'description' => 'A test description',
            'url' => 'https://example.com/product',
            'price_in_cents' => 1999,
            'currency' => 'EUR',
            'original_image_url' => 'https://example.com/image.jpg',
            'fetch_status' => 'completed',
        ]);

        $event = new GiftFetchCompleted($gift);
        $payload = $event->broadcastWith();

        expect($payload)->toHaveKey('gift');
        expect($payload['gift']['id'])->toBe($gift->id);
        expect($payload['gift']['title'])->toBe('Test Product');
        expect($payload['gift']['description'])->toBe('A test description');
        expect($payload['gift']['url'])->toBe('https://example.com/product');
        expect($payload['gift']['price_in_cents'])->toBe(1999);
        expect($payload['gift']['currency'])->toBe('EUR');
        expect($payload['gift']['image_url_thumb'])->toBe('https://example.com/image.jpg');
        expect($payload['gift']['image_url_card'])->toBe('https://example.com/image.jpg');
        expect($payload['gift']['image_url_large'])->toBe('https://example.com/image.jpg');
        expect($payload['gift']['fetch_status'])->toBe('completed');
        expect($payload['gift']['price_formatted'])->toBe('€ 19.99');
    });
});

describe('Claim confirmation flow', function () {
    it('dispatches GiftClaimed when claim is confirmed via token', function () {
        Event::fake([GiftClaimed::class]);

        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);
        $gift->lists()->attach($list->id);

        $claim = Claim::create([
            'gift_id' => $gift->id,
            'claimer_email' => 'anon@example.com',
        ]);

        $token = $claim->confirmation_token;

        $this->get("/en/claim/confirm/{$token}");

        Event::assertDispatched(GiftClaimed::class);
    });

    it('does not dispatch GiftClaimed for invalid token', function () {
        Event::fake([GiftClaimed::class]);

        $this->get('/en/claim/confirm/invalid-token-12345');

        Event::assertNotDispatched(GiftClaimed::class);
    });

    it('does not dispatch GiftClaimed when gift is already claimed', function () {
        Event::fake([GiftClaimed::class]);

        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        // Create a confirmed claim first
        Claim::factory()->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        // Create a pending claim
        $pendingClaim = Claim::create([
            'gift_id' => $gift->id,
            'claimer_email' => 'latecomer@example.com',
        ]);

        $this->get("/en/claim/confirm/{$pendingClaim->confirmation_token}");

        // Should not dispatch because gift is already claimed
        Event::assertNotDispatched(GiftClaimed::class);
    });
});
