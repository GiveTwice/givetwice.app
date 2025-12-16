<?php

use App\Actions\CreatePendingClaimAction;
use App\Exceptions\Claim\AlreadyClaimedException;
use App\Exceptions\Claim\ClaimException;
use App\Exceptions\Claim\ConfirmationResentException;
use App\Mail\ClaimConfirmationMail;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('CreatePendingClaimAction', function () {

    describe('happy path', function () {
        it('creates a pending claim with email', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new CreatePendingClaimAction;
            $claim = $action->execute($gift, 'claimer@example.com');

            expect($claim)->toBeInstanceOf(Claim::class);
            expect($claim->gift_id)->toBe($gift->id);
            expect($claim->claimer_email)->toBe('claimer@example.com');
            expect($claim->confirmed_at)->toBeNull();
        });

        it('stores optional name with the claim', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new CreatePendingClaimAction;
            $claim = $action->execute($gift, 'claimer@example.com', 'John Doe');

            expect($claim->claimer_name)->toBe('John Doe');
        });

        it('generates a confirmation token', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new CreatePendingClaimAction;
            $claim = $action->execute($gift, 'claimer@example.com');

            expect($claim->confirmation_token)->not->toBeNull();
            expect(strlen($claim->confirmation_token))->toBe(64);
        });

        it('sends confirmation email', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new CreatePendingClaimAction;
            $action->execute($gift, 'claimer@example.com');

            Mail::assertSent(ClaimConfirmationMail::class, function ($mail) {
                return $mail->hasTo('claimer@example.com');
            });
        });
    });

    describe('validation', function () {
        it('throws exception when gift is already claimed', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'confirmed_at' => now(),
            ]);

            $action = new CreatePendingClaimAction;

            expect(fn () => $action->execute($gift, 'new@example.com'))
                ->toThrow(AlreadyClaimedException::class);
        });

        it('throws AlreadyClaimedException when gift has any confirmed claim', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'first@example.com',
                'confirmed_at' => now(),
            ]);

            $action = new CreatePendingClaimAction;

            expect(fn () => $action->execute($gift, 'second@example.com'))
                ->toThrow(AlreadyClaimedException::class);
        });
    });

    describe('resend flow', function () {
        it('resends confirmation email for pending claim with same email', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $existingClaim = Claim::factory()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'pending@example.com',
                'confirmed_at' => null,
            ]);

            $action = new CreatePendingClaimAction;

            try {
                $action->execute($gift, 'pending@example.com');
            } catch (ConfirmationResentException) {
            }

            Mail::assertSent(ClaimConfirmationMail::class, function ($mail) use ($existingClaim) {
                return $mail->hasTo('pending@example.com')
                    && $mail->claim->id === $existingClaim->id;
            });
        });

        it('does not create duplicate claim when resending', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'pending@example.com',
                'confirmed_at' => null,
            ]);

            $action = new CreatePendingClaimAction;

            try {
                $action->execute($gift, 'pending@example.com');
            } catch (ConfirmationResentException) {
            }

            expect(Claim::where('claimer_email', 'pending@example.com')->count())->toBe(1);
        });

        it('throws ConfirmationResentException when resending', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'claimer_email' => 'pending@example.com',
                'confirmed_at' => null,
            ]);

            $action = new CreatePendingClaimAction;

            expect(fn () => $action->execute($gift, 'pending@example.com'))
                ->toThrow(ConfirmationResentException::class);
        });
    });

    describe('security', function () {
        it('does not send email when gift is already claimed', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'confirmed_at' => now(),
            ]);

            $action = new CreatePendingClaimAction;

            try {
                $action->execute($gift, 'attacker@example.com');
            } catch (ClaimException) {
            }

            Mail::assertNothingSent();
        });

        it('does not create claim when gift is already claimed', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'confirmed_at' => now(),
            ]);

            $initialCount = Claim::count();

            $action = new CreatePendingClaimAction;

            try {
                $action->execute($gift, 'attacker@example.com');
            } catch (ClaimException) {
            }

            expect(Claim::count())->toBe($initialCount);
        });

        it('claim is not confirmed by default', function () {
            $owner = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $action = new CreatePendingClaimAction;
            $claim = $action->execute($gift, 'claimer@example.com');

            expect($claim->isConfirmed())->toBeFalse();
            expect($gift->fresh()->isClaimed())->toBeFalse();
        });
    });

});
