<?php

use App\Actions\ClaimGiftAction;
use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\CannotClaimOwnGiftException;
use App\Exceptions\Claim\ClaimException;
use App\Exceptions\Claim\UserAlreadyClaimedException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('ClaimGiftAction', function () {

    describe('happy path', function () {
        it('creates a confirmed claim for the user', function () {
            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;
            $claim = $action->execute($gift, $claimer);

            expect($claim)->toBeInstanceOf(Claim::class);
            expect($claim->gift_id)->toBe($gift->id);
            expect($claim->user_id)->toBe($claimer->id);
            expect($claim->confirmed_at)->not->toBeNull();
        });

        it('dispatches GiftClaimed event', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;
            $action->execute($gift, $claimer);

            Event::assertDispatched(GiftClaimed::class, function ($event) use ($gift) {
                return $event->gift->id === $gift->id;
            });
        });

        it('loads gift lists before dispatching event', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $list = GiftList::factory()->create(['user_id' => $owner->id]);
            $gift->lists()->attach($list->id);

            $action = new ClaimGiftAction;
            $action->execute($gift, $claimer);

            Event::assertDispatched(GiftClaimed::class, function ($event) use ($list) {
                return $event->gift->lists->contains('id', $list->id);
            });
        });

        it('creates claim with confirmed_at set immediately', function () {
            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;
            $claim = $action->execute($gift, $claimer);

            expect($claim->isConfirmed())->toBeTrue();
        });
    });

    describe('validation', function () {
        it('throws exception when user tries to claim their own gift', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;

            expect(fn () => $action->execute($gift, $owner))
                ->toThrow(CannotClaimOwnGiftException::class);
        });

        it('throws exception when gift is already claimed', function () {
            $owner = User::factory()->create();
            $claimer1 = User::factory()->create();
            $claimer2 = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'user_id' => $claimer1->id,
                'confirmed_at' => now(),
            ]);

            $action = new ClaimGiftAction;

            expect(fn () => $action->execute($gift, $claimer2))
                ->toThrow(AlreadyClaimedException::class);
        });

        it('throws exception when user has already claimed this gift', function () {
            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'user_id' => $claimer->id,
                'confirmed_at' => null,
            ]);

            $action = new ClaimGiftAction;

            expect(fn () => $action->execute($gift, $claimer))
                ->toThrow(UserAlreadyClaimedException::class);
        });
    });

    describe('security', function () {
        it('does not dispatch event when claim fails', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;

            try {
                $action->execute($gift, $owner);
            } catch (ClaimException) {
            }

            Event::assertNotDispatched(GiftClaimed::class);
        });

        it('does not create claim record when validation fails', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new ClaimGiftAction;

            try {
                $action->execute($gift, $owner);
            } catch (ClaimException) {
            }

            expect(Claim::count())->toBe(0);
        });
    });

});
