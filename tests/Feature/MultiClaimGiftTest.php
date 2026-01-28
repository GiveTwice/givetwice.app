<?php

use App\Actions\ClaimGiftAction;
use App\Actions\CreatePendingClaimAction;
use App\Events\GiftClaimed;
use App\Exceptions\Claim\EmailAlreadyClaimedException;
use App\Exceptions\Claim\UserAlreadyClaimedException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('Gift model multi-claim methods', function () {

    it('isClaimed returns false for multi-claim gifts even with confirmed claims', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => true]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        expect($gift->fresh()->isClaimed())->toBeFalse();
    });

    it('isClaimed returns true for regular gifts with confirmed claims', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => false]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        expect($gift->fresh()->isClaimed())->toBeTrue();
    });

    it('allowsMultipleClaims returns true when enabled', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => true]);

        expect($gift->allowsMultipleClaims())->toBeTrue();
    });

    it('allowsMultipleClaims returns false by default', function () {
        $gift = Gift::factory()->create();

        expect($gift->allowsMultipleClaims())->toBeFalse();
    });

    it('getConfirmedClaimCount returns count of confirmed claims only', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => true]);

        Claim::factory()->count(3)->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'confirmed_at' => null,
        ]);

        expect($gift->getConfirmedClaimCount())->toBe(3);
    });

    it('getConfirmedClaimCount works with loaded claims relation', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => true]);

        Claim::factory()->count(2)->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        $giftWithClaims = Gift::with('claims')->find($gift->id);

        expect($giftWithClaims->getConfirmedClaimCount())->toBe(2);
    });

    it('allow_multiple_claims is properly cast to boolean', function () {
        $gift = Gift::factory()->create(['allow_multiple_claims' => true]);

        expect($gift->allow_multiple_claims)->toBeTrue();
        expect($gift->allow_multiple_claims)->toBeBool();
    });

});

describe('ClaimGiftAction with multi-claim gifts', function () {

    it('allows claiming a multi-claim gift that has existing claims', function () {
        $owner = User::factory()->create();
        $claimer1 = User::factory()->create();
        $claimer2 = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer1->id,
            'confirmed_at' => now(),
        ]);

        $action = new ClaimGiftAction;
        $claim = $action->execute($gift, $claimer2);

        expect($claim)->toBeInstanceOf(Claim::class);
        expect($claim->user_id)->toBe($claimer2->id);
        expect($claim->confirmed_at)->not->toBeNull();
    });

    it('prevents same user from claiming multi-claim gift twice', function () {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer->id,
            'confirmed_at' => now(),
        ]);

        $action = new ClaimGiftAction;

        expect(fn () => $action->execute($gift, $claimer))
            ->toThrow(UserAlreadyClaimedException::class);
    });

    it('broadcasts event with multi-claim data', function () {
        Event::fake([GiftClaimed::class]);

        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        $action = new ClaimGiftAction;
        $action->execute($gift, $claimer);

        Event::assertDispatched(GiftClaimed::class, function ($event) {
            return $event->gift->allow_multiple_claims === true;
        });
    });

});

describe('CreatePendingClaimAction with multi-claim gifts', function () {

    it('allows creating pending claim on multi-claim gift with existing claims', function () {
        $owner = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'claimer_email' => 'first@example.com',
            'confirmed_at' => now(),
        ]);

        $action = new CreatePendingClaimAction;
        $claim = $action->execute($gift, 'second@example.com');

        expect($claim)->toBeInstanceOf(Claim::class);
        expect($claim->claimer_email)->toBe('second@example.com');
    });

    it('prevents same email from claiming multi-claim gift twice', function () {
        $owner = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'claimer_email' => 'claimer@example.com',
            'confirmed_at' => now(),
        ]);

        $action = new CreatePendingClaimAction;

        expect(fn () => $action->execute($gift, 'claimer@example.com'))
            ->toThrow(EmailAlreadyClaimedException::class);
    });

});

describe('GiftController multi-claim field handling', function () {

    it('can create gift with allow_multiple_claims enabled', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $user->lists()->attach($list->id);

        $this->actingAs($user)
            ->post('/en/gifts', [
                'url' => 'https://example.com/giftcard',
                'list_id' => $list->id,
                'allow_multiple_claims' => '1',
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift)->not->toBeNull();
        expect($gift->allow_multiple_claims)->toBeTrue();
    });

    it('creates gift with allow_multiple_claims false by default', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $user->lists()->attach($list->id);

        $this->actingAs($user)
            ->post('/en/gifts', [
                'url' => 'https://example.com/product',
                'list_id' => $list->id,
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift)->not->toBeNull();
        expect($gift->allow_multiple_claims)->toBeFalse();
    });

    it('can update gift to enable allow_multiple_claims', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'allow_multiple_claims' => false,
        ]);
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $user->lists()->attach($list->id);
        $gift->lists()->attach($list->id);

        $this->actingAs($user)
            ->put("/en/gifts/{$gift->id}", [
                'url' => $gift->url,
                'list_id' => $list->id,
                'allow_multiple_claims' => '1',
            ])
            ->assertRedirect();

        expect($gift->fresh()->allow_multiple_claims)->toBeTrue();
    });

    it('can update gift to disable allow_multiple_claims', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'allow_multiple_claims' => true,
        ]);
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $user->lists()->attach($list->id);
        $gift->lists()->attach($list->id);

        $this->actingAs($user)
            ->put("/en/gifts/{$gift->id}", [
                'url' => $gift->url,
                'list_id' => $list->id,
            ])
            ->assertRedirect();

        expect($gift->fresh()->allow_multiple_claims)->toBeFalse();
    });

    it('keeps existing claims when disabling allow_multiple_claims', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);
        $owner->lists()->attach($list->id);
        $gift->lists()->attach($list->id);

        Claim::factory()->count(3)->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        $this->actingAs($owner)
            ->put("/en/gifts/{$gift->id}", [
                'url' => $gift->url,
                'list_id' => $list->id,
            ])
            ->assertRedirect();

        expect($gift->fresh()->claims()->count())->toBe(3);
        expect($gift->fresh()->allow_multiple_claims)->toBeFalse();
    });

});

describe('GiftClaimed event broadcast payload', function () {

    it('includes allow_multiple_claims in broadcast payload', function () {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        $claim = Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer->id,
            'confirmed_at' => now(),
        ]);

        $event = new GiftClaimed($gift->fresh(), $claim);
        $payload = $event->broadcastWith();

        expect($payload['gift']['allow_multiple_claims'])->toBeTrue();
        expect($payload['claimed'])->toBeFalse();
    });

    it('includes claim_count in broadcast payload', function () {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);

        Claim::factory()->count(2)->create([
            'gift_id' => $gift->id,
            'confirmed_at' => now(),
        ]);

        $claim = Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer->id,
            'confirmed_at' => now(),
        ]);

        $event = new GiftClaimed($gift->fresh(), $claim);
        $payload = $event->broadcastWith();

        expect($payload['gift']['claim_count'])->toBe(3);
    });

    it('sets claimed to true for regular gifts', function () {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();

        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => false,
        ]);

        $claim = Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer->id,
            'confirmed_at' => now(),
        ]);

        $event = new GiftClaimed($gift->fresh(), $claim);
        $payload = $event->broadcastWith();

        expect($payload['claimed'])->toBeTrue();
    });

});

describe('Public list availability counts', function () {

    it('counts multi-claim gifts as available even with claims', function () {
        $owner = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);

        $regularGift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => false,
        ]);
        $regularGift->lists()->attach($list->id);

        $multiClaimGift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);
        $multiClaimGift->lists()->attach($list->id);

        Claim::factory()->create([
            'gift_id' => $multiClaimGift->id,
            'confirmed_at' => now(),
        ]);

        $response = $this->get("/en/v/{$list->id}/{$list->slug}");

        $response->assertStatus(200);

        $gifts = $list->gifts()->with('claims')->get();

        $availableGifts = $gifts->filter(
            fn ($gift) => $gift->claims->isEmpty() || $gift->allow_multiple_claims
        )->count();

        expect($availableGifts)->toBe(2);
    });

    it('shows claimed regular gifts after available and multi-claim gifts', function () {
        $owner = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);

        $availableGift = Gift::factory()->create(['user_id' => $owner->id]);
        $availableGift->lists()->attach($list->id);

        $multiClaimGift = Gift::factory()->create([
            'user_id' => $owner->id,
            'allow_multiple_claims' => true,
        ]);
        $multiClaimGift->lists()->attach($list->id);
        Claim::factory()->create(['gift_id' => $multiClaimGift->id, 'confirmed_at' => now()]);

        $claimedGift = Gift::factory()->create(['user_id' => $owner->id]);
        $claimedGift->lists()->attach($list->id);
        Claim::factory()->create(['gift_id' => $claimedGift->id, 'confirmed_at' => now()]);

        $response = $this->get("/en/v/{$list->id}/{$list->slug}");
        $response->assertSuccessful();

        $giftIds = $response->viewData('gifts')->getCollection()->pluck('id')->all();

        expect($giftIds)->toContain($availableGift->id, $multiClaimGift->id, $claimedGift->id);

        $claimedIndex = array_search($claimedGift->id, $giftIds, true);

        expect($claimedIndex)
            ->toBeGreaterThan(array_search($availableGift->id, $giftIds, true))
            ->toBeGreaterThan(array_search($multiClaimGift->id, $giftIds, true));
    });

});
