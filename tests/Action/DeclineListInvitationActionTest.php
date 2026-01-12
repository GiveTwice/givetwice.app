<?php

use App\Actions\DeclineListInvitationAction;
use App\Exceptions\ListInvitation\InvalidInvitationException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->trackQueriesForEfficiency();
});

describe('DeclineListInvitationAction', function () {

    describe('happy path', function () {
        it('marks invitation as declined', function () {
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

            $action = new DeclineListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($invitation->fresh()->declined_at)->not->toBeNull();

            $this->assertQueriesAreEfficient();
        });

        it('does not attach user to the list', function () {
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

            $action = new DeclineListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($list->fresh()->users)->toHaveCount(1);
            expect($list->fresh()->hasUser($invitee))->toBeFalse();
        });

        it('allows declining invitation without invitee_id if email matches', function () {
            $inviter = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $invitation = ListInvitation::factory()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => null,
                'email' => 'invitee@example.com',
            ]);

            $action = new DeclineListInvitationAction;
            $action->execute($invitation->token, $invitee);

            expect($invitation->fresh()->declined_at)->not->toBeNull();
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

            $action = new DeclineListInvitationAction;

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

            $action = new DeclineListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $invitee))
                ->toThrow(InvalidInvitationException::class);
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

            $action = new DeclineListInvitationAction;

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

            $action = new DeclineListInvitationAction;

            expect(fn () => $action->execute($invitation->token, $invitee))
                ->toThrow(InvalidInvitationException::class);
        });
    });

});
