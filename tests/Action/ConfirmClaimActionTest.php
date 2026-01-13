<?php

use App\Actions\ConfirmClaimAction;
use App\Events\GiftClaimed;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\InvalidTokenException;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->trackQueriesForEfficiency();
});

describe('ConfirmClaimAction', function () {

    describe('happy path', function () {
        it('confirms a pending claim with valid token', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $action = new ConfirmClaimAction;
            $claim = $action->execute($pendingClaim->confirmation_token);

            expect($claim->id)->toBe($pendingClaim->id);
            expect($claim->fresh()->confirmed_at)->not->toBeNull();

            $this->assertQueriesAreEfficient();
        });

        it('returns the confirmed claim', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $action = new ConfirmClaimAction;
            $claim = $action->execute($pendingClaim->confirmation_token);

            expect($claim)->toBeInstanceOf(Claim::class);
            expect($claim->isConfirmed())->toBeTrue();
        });

        it('dispatches GiftClaimed event', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $action = new ConfirmClaimAction;
            $action->execute($pendingClaim->confirmation_token);

            Event::assertDispatched(GiftClaimed::class, function ($event) use ($gift) {
                return $event->gift->id === $gift->id;
            });
        });

        it('marks the gift as claimed after confirmation', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            expect($gift->isClaimed())->toBeFalse();

            $action = new ConfirmClaimAction;
            $action->execute($pendingClaim->confirmation_token);

            expect($gift->fresh()->isClaimed())->toBeTrue();
        });

        it('loads gift lists before dispatching event', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $list = GiftList::factory()->create(['creator_id' => $owner->id]);
            $gift->lists()->attach($list->id);

            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $action = new ConfirmClaimAction;
            $action->execute($pendingClaim->confirmation_token);

            Event::assertDispatched(GiftClaimed::class, function ($event) use ($list) {
                return $event->gift->lists->contains('id', $list->id);
            });
        });
    });

    describe('validation', function () {
        it('throws exception for invalid token', function () {
            $action = new ConfirmClaimAction;

            expect(fn () => $action->execute('invalid-token-12345'))
                ->toThrow(InvalidTokenException::class);
        });

        it('throws exception for empty token', function () {
            $action = new ConfirmClaimAction;

            expect(fn () => $action->execute(''))
                ->toThrow(InvalidTokenException::class);
        });

        it('throws exception for already used token', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $token = $pendingClaim->confirmation_token;

            $action = new ConfirmClaimAction;
            $action->execute($token);

            expect(fn () => $action->execute($token))
                ->toThrow(InvalidTokenException::class);
        });
    });

    describe('race condition handling', function () {
        it('throws exception when gift was claimed while confirmation pending', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->confirmed()->create([
                'gift_id' => $gift->id,
            ]);

            $lateClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'latecomer@example.com',
            ]);

            $action = new ConfirmClaimAction;

            expect(fn () => $action->execute($lateClaim->confirmation_token))
                ->toThrow(AlreadyClaimedException::class);
        });

        it('deletes the pending claim when race condition occurs', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->confirmed()->create([
                'gift_id' => $gift->id,
            ]);

            $lateClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'latecomer@example.com',
            ]);

            $lateClaimId = $lateClaim->id;

            $action = new ConfirmClaimAction;

            try {
                $action->execute($lateClaim->confirmation_token);
            } catch (AlreadyClaimedException) {
            }

            expect(Claim::find($lateClaimId))->toBeNull();
        });

        it('does not dispatch event when race condition occurs', function () {
            Event::fake([GiftClaimed::class]);

            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->confirmed()->create([
                'gift_id' => $gift->id,
            ]);

            $lateClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);

            $action = new ConfirmClaimAction;

            try {
                $action->execute($lateClaim->confirmation_token);
            } catch (AlreadyClaimedException) {
            }

            Event::assertNotDispatched(GiftClaimed::class);
        });
    });

    describe('security', function () {
        it('confirms only the gift associated with the token', function () {
            $owner = User::factory()->create();
            $gift1 = Gift::factory()->create(['user_id' => $owner->id]);
            $gift2 = Gift::factory()->create(['user_id' => $owner->id]);

            $claimForGift1 = Claim::factory()->anonymous()->create([
                'gift_id' => $gift1->id,
            ]);

            $action = new ConfirmClaimAction;
            $claim = $action->execute($claimForGift1->confirmation_token);

            expect($claim->gift_id)->toBe($gift1->id);
            expect($gift1->fresh()->isClaimed())->toBeTrue();
            expect($gift2->fresh()->isClaimed())->toBeFalse();
        });

        it('preserves token after confirmation for redirect lookup', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);
            $pendingClaim = Claim::factory()->anonymous()->create([
                'gift_id' => $gift->id,
            ]);
            $originalToken = $pendingClaim->confirmation_token;

            $action = new ConfirmClaimAction;
            $action->execute($pendingClaim->confirmation_token);

            // Token is preserved so it can be used for the redirect lookup
            expect($pendingClaim->fresh()->confirmation_token)->toBe($originalToken);
        });
    });

});
