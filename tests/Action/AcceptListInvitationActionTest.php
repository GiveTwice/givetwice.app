<?php

use App\Actions\AcceptListInvitationAction;
use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Exceptions\ListInvitation\InvitationExpiredException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->trackQueriesForEfficiency();
});

describe('AcceptListInvitationAction', function () {

    describe('happy path', function () {
        it('marks invitation as accepted', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($invitation->fresh()->accepted_at)->not->toBeNull();

            $this->assertQueriesAreEfficient();
        });

        it('attaches user to the list', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($list->fresh()->users)->toHaveCount(2);
            expect($list->fresh()->hasUser($invitee))->toBeTrue();
        });

        it('returns the list after accepting', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;
            $result = $action->execute($invitation->token, $invitee);

            expect($result)->toBeInstanceOf(GiftList::class);
            expect($result->id)->toBe($list->id);
        });

        it('allows accepting invitation without invitee_id if email matches', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            // Invitation created before user registered
            $invitation = ListInvitation::factory()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => null,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($invitation->fresh()->accepted_at)->not->toBeNull();
            expect($list->fresh()->hasUser($invitee))->toBeTrue();
        });
    });

    describe('validation', function () {
        it('throws exception when invitation belongs to different user', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $wrongUser))
                ->toThrow(InvalidInvitationException::class);
        });

        it('throws exception when invitation is expired', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->expired()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $invitee))
                ->toThrow(InvitationExpiredException::class);
        });

        it('throws exception when invitation is already accepted', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->accepted()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $invitee))
                ->toThrow(InvalidInvitationException::class);
        });

        it('throws exception when invitation is already declined', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->declined()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $invitee))
                ->toThrow(InvalidInvitationException::class);
        });
    });

    describe('security', function () {
        it('does not attach user when validation fails', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $wrongUser = User::factory()->create(['email' => 'wrong@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $action = new AcceptListInvitationAction;

            try {
                $action->execute($invitation->token, $wrongUser);
            } catch (InvalidInvitationException) {
            }

            expect($list->fresh()->users)->toHaveCount(1);
            expect($list->fresh()->hasUser($wrongUser))->toBeFalse();
        });
    });

});
