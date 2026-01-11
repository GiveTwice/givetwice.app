<?php

use App\Listeners\LinkInvitationsToVerifiedUser;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('LinkInvitationsToVerifiedUser', function () {

    it('links pending invitations to verified user by email', function () {
        $inviter = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
        $list->users()->attach($inviter->id);

        // Create invitation for email before user exists
        $invitation = ListInvitation::factory()->create([
            'list_id' => $list->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => null,
            'email' => 'newuser@example.com',
        ]);

        // Create user and trigger verification
        $newUser = User::factory()->create([
            'email' => 'newuser@example.com',
            'email_verified_at' => null,
        ]);

        $event = new Verified($newUser);
        $listener = new LinkInvitationsToVerifiedUser;
        $listener->handle($event);

        expect($invitation->fresh()->invitee_id)->toBe($newUser->id);
    });

    it('links multiple invitations to the same user', function () {
        $inviter1 = User::factory()->create();
        $inviter2 = User::factory()->create();
        $list1 = GiftList::factory()->create(['creator_id' => $inviter1->id]);
        $list2 = GiftList::factory()->create(['creator_id' => $inviter2->id]);
        $list1->users()->attach($inviter1->id);
        $list2->users()->attach($inviter2->id);

        $invitation1 = ListInvitation::factory()->create([
            'list_id' => $list1->id,
            'inviter_id' => $inviter1->id,
            'invitee_id' => null,
            'email' => 'newuser@example.com',
        ]);

        $invitation2 = ListInvitation::factory()->create([
            'list_id' => $list2->id,
            'inviter_id' => $inviter2->id,
            'invitee_id' => null,
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::factory()->create([
            'email' => 'newuser@example.com',
            'email_verified_at' => null,
        ]);

        $event = new Verified($newUser);
        $listener = new LinkInvitationsToVerifiedUser;
        $listener->handle($event);

        expect($invitation1->fresh()->invitee_id)->toBe($newUser->id);
        expect($invitation2->fresh()->invitee_id)->toBe($newUser->id);
    });

    it('does not affect already accepted invitations', function () {
        $inviter = User::factory()->create();
        $existingInvitee = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
        $list->users()->attach($inviter->id);

        $invitation = ListInvitation::factory()->accepted()->create([
            'list_id' => $list->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $existingInvitee->id,
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::factory()->create([
            'email' => 'newuser@example.com',
            'email_verified_at' => null,
        ]);

        $event = new Verified($newUser);
        $listener = new LinkInvitationsToVerifiedUser;
        $listener->handle($event);

        // Should not change the invitee_id since invitation is already accepted
        expect($invitation->fresh()->invitee_id)->toBe($existingInvitee->id);
    });

    it('does not affect declined invitations', function () {
        $inviter = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
        $list->users()->attach($inviter->id);

        $invitation = ListInvitation::factory()->declined()->create([
            'list_id' => $list->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => null,
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::factory()->create([
            'email' => 'newuser@example.com',
            'email_verified_at' => null,
        ]);

        $event = new Verified($newUser);
        $listener = new LinkInvitationsToVerifiedUser;
        $listener->handle($event);

        // Should not update declined invitations
        expect($invitation->fresh()->invitee_id)->toBeNull();
    });

    it('handles case-insensitive email matching', function () {
        $inviter = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $inviter->id]);
        $list->users()->attach($inviter->id);

        $invitation = ListInvitation::factory()->create([
            'list_id' => $list->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => null,
            'email' => 'newuser@example.com',
        ]);

        $newUser = User::factory()->create([
            'email' => 'NewUser@Example.COM',
            'email_verified_at' => null,
        ]);

        $event = new Verified($newUser);
        $listener = new LinkInvitationsToVerifiedUser;
        $listener->handle($event);

        expect($invitation->fresh()->invitee_id)->toBe($newUser->id);
    });

});
