<?php

use App\Actions\InviteToListAction;
use App\Events\ListInvitationCreated;
use App\Exceptions\ListInvitation\AlreadyCollaboratorException;
use App\Exceptions\ListInvitation\CannotInviteSelfException;
use App\Exceptions\ListInvitation\InvitationAlreadyPendingException;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->trackQueriesForEfficiency();
});

describe('InviteToListAction', function () {

    describe('happy path', function () {
        it('creates an invitation for a new email', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'newuser@example.com');

            expect($invitation)->toBeInstanceOf(ListInvitation::class);
            expect($invitation->list_id)->toBe($list->id);
            expect($invitation->inviter_id)->toBe($inviter->id);
            expect($invitation->email)->toBe('newuser@example.com');
            expect($invitation->token)->toHaveLength(64);
            expect($invitation->invitee_id)->toBeNull();

            $this->assertQueriesAreEfficient();
        });

        it('creates an invitation for an existing user and sets invitee_id', function () {
            $inviter = User::factory()->create();
            $existingUser = User::factory()->create(['email' => 'existing@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'existing@example.com');

            expect($invitation->invitee_id)->toBe($existingUser->id);
        });

        it('dispatches ListInvitationCreated event', function () {
            Event::fake([ListInvitationCreated::class]);

            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;
            $action->execute($list, $inviter, 'test@example.com');

            Event::assertDispatched(ListInvitationCreated::class, function ($event) {
                return $event->invitation->email === 'test@example.com';
            });
        });

        it('normalizes email to lowercase', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'TEST@EXAMPLE.COM');

            expect($invitation->email)->toBe('test@example.com');
        });

        it('deletes declined invitation before creating new one', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            // Create a declined invitation
            ListInvitation::factory()->declined()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'email' => 'test@example.com',
            ]);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'test@example.com');

            expect(ListInvitation::where('email', 'test@example.com')->count())->toBe(1);
            expect($invitation->declined_at)->toBeNull();
        });

        it('deletes expired invitation before creating new one', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            // Create an expired invitation
            ListInvitation::factory()->expired()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'email' => 'test@example.com',
            ]);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'test@example.com');

            expect(ListInvitation::where('email', 'test@example.com')->count())->toBe(1);
            expect($invitation->expires_at->isFuture())->toBeTrue();
        });

        it('sets expiration date 30 days in the future', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;
            $invitation = $action->execute($list, $inviter, 'test@example.com');

            expect((int) now()->diffInDays($invitation->expires_at))->toBeGreaterThanOrEqual(29);
        });
    });

    describe('validation', function () {
        it('throws exception when inviting self', function () {
            $inviter = User::factory()->create(['email' => 'inviter@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;

            expect(fn () => $action->execute($list, $inviter, 'inviter@example.com'))
                ->toThrow(CannotInviteSelfException::class);
        });

        it('throws exception when user is already a collaborator', function () {
            $inviter = User::factory()->create();
            $collaborator = User::factory()->create(['email' => 'collab@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach([$inviter->id, $collaborator->id]);

            $action = new InviteToListAction;

            expect(fn () => $action->execute($list, $inviter, 'collab@example.com'))
                ->toThrow(AlreadyCollaboratorException::class);
        });

        it('throws exception when invitation is already pending', function () {
            $inviter = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            ListInvitation::factory()->create([
                'list_id' => $list->id,
                'inviter_id' => $inviter->id,
                'email' => 'pending@example.com',
            ]);

            $action = new InviteToListAction;

            expect(fn () => $action->execute($list, $inviter, 'pending@example.com'))
                ->toThrow(InvitationAlreadyPendingException::class);
        });
    });

    describe('security', function () {
        it('does not dispatch event when validation fails', function () {
            Event::fake([ListInvitationCreated::class]);

            $inviter = User::factory()->create(['email' => 'inviter@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;

            try {
                $action->execute($list, $inviter, 'inviter@example.com');
            } catch (CannotInviteSelfException) {
            }

            Event::assertNotDispatched(ListInvitationCreated::class);
        });

        it('does not create invitation when validation fails', function () {
            $inviter = User::factory()->create(['email' => 'inviter@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
            $list->users()->attach($inviter->id);

            $action = new InviteToListAction;

            try {
                $action->execute($list, $inviter, 'inviter@example.com');
            } catch (CannotInviteSelfException) {
            }

            expect(ListInvitation::count())->toBe(0);
        });
    });

});
