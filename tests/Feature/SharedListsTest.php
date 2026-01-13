<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('Shared Lists Feature', function () {

    describe('list access', function () {
        it('allows collaborator to view list on dashboard', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->get('/en/dashboard')
                ->assertStatus(200)
                ->assertSee($list->name);
        });

        it('allows collaborator to edit list', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->get('/en/list/'.$list->slug.'/edit')
                ->assertStatus(200);
        });

        it('allows collaborator to add gifts to list', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->post('/en/gifts', [
                    'url' => 'https://example.com/product',
                    'list_id' => $list->id,
                ])
                ->assertRedirect();

            expect(Gift::where('user_id', $collaborator->id)->count())->toBe(1);
        });

        it('prevents non-collaborator from editing list', function () {
            $creator = User::factory()->create();
            $nonCollaborator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $this->actingAs($nonCollaborator)
                ->get('/en/list/'.$list->slug.'/edit')
                ->assertStatus(403);
        });

        it('shows collaborator avatars on dashboard', function () {
            $creator = User::factory()->create(['name' => 'Creator User']);
            $collaborator = User::factory()->create(['name' => 'Collaborator User']);

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $response = $this->actingAs($creator)
                ->get('/en/dashboard');

            $response->assertStatus(200);
            // The collaborators button should link to invite page and show multiple avatars
            $response->assertSee(route('lists.invite', ['locale' => 'en', 'list' => $list->slug]));
            // Both user initials should be visible in the stacked avatars
            $response->assertSee($creator->getInitials());
            $response->assertSee($collaborator->getInitials());
        });
    });

    describe('invitation flow', function () {
        it('shows invite page to collaborators', function () {
            $creator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $this->actingAs($creator)
                ->get('/en/lists/'.$list->slug.'/invite')
                ->assertStatus(200)
                ->assertSee(__('Invite collaborators'));
        });

        it('sends invitation to new email', function () {
            $creator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $this->actingAs($creator)
                ->post(route('lists.invite.store', ['locale' => 'en', 'list' => $list->slug]), [
                    'email' => 'newuser@example.com',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            expect(ListInvitation::where('email', 'newuser@example.com')->exists())->toBeTrue();
        });

        it('prevents inviting self', function () {
            $creator = User::factory()->create(['email' => 'creator@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $this->actingAs($creator)
                ->post(route('lists.invite.store', ['locale' => 'en', 'list' => $list->slug]), [
                    'email' => 'creator@example.com',
                ])
                ->assertRedirect()
                ->assertSessionHas('error');

            expect(ListInvitation::count())->toBe(0);
        });

        it('prevents duplicate pending invitations', function () {
            $creator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            ListInvitation::factory()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'email' => 'existing@example.com',
            ]);

            $this->actingAs($creator)
                ->post(route('lists.invite.store', ['locale' => 'en', 'list' => $list->slug]), [
                    'email' => 'existing@example.com',
                ])
                ->assertRedirect()
                ->assertSessionHas('error');

            expect(ListInvitation::where('email', 'existing@example.com')->count())->toBe(1);
        });
    });

    describe('accepting invitation', function () {
        it('accepts invitation via token', function () {
            $creator = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $this->actingAs($invitee)
                ->post(route('lists.invitation.accept', ['locale' => 'en', 'token' => $invitation->token]))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect($invitation->fresh()->accepted_at)->not->toBeNull();
            expect($list->fresh()->hasUser($invitee))->toBeTrue();
        });

        it('rejects expired invitation', function () {
            $creator = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $invitation = ListInvitation::factory()->expired()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $this->actingAs($invitee)
                ->post(route('lists.invitation.accept', ['locale' => 'en', 'token' => $invitation->token]))
                ->assertRedirect()
                ->assertSessionHas('error');

            expect($list->fresh()->hasUser($invitee))->toBeFalse();
        });
    });

    describe('declining invitation', function () {
        it('declines invitation via token', function () {
            $creator = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $invitation = ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $this->actingAs($invitee)
                ->post(route('lists.invitation.decline', ['locale' => 'en', 'token' => $invitation->token]))
                ->assertRedirect();

            expect($invitation->fresh()->declined_at)->not->toBeNull();
            expect($list->fresh()->hasUser($invitee))->toBeFalse();
        });
    });

    describe('leaving list', function () {
        it('allows collaborator to leave list', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->delete(route('lists.leave', ['locale' => 'en', 'list' => $list->slug]))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect($list->fresh()->hasUser($collaborator))->toBeFalse();
        });

        it('prevents leaving own default list', function () {
            $user = User::factory()->create();
            $list = GiftList::factory()->create([
                'creator_id' => $user->id,
                'is_default' => true,
            ]);
            $list->users()->attach($user->id);

            $this->actingAs($user)
                ->delete(route('lists.leave', ['locale' => 'en', 'list' => $list->slug]))
                ->assertRedirect()
                ->assertSessionHas('error');

            expect($list->fresh()->hasUser($user))->toBeTrue();
        });
    });

    describe('removing collaborator', function () {
        it('allows collaborator to remove another collaborator', function () {
            $creator = User::factory()->create();
            $collaborator1 = User::factory()->create();
            $collaborator2 = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator1->id, $collaborator2->id]);

            $this->actingAs($collaborator1)
                ->delete(route('lists.collaborator.remove', [
                    'locale' => 'en',
                    'list' => $list->slug,
                    'user' => $collaborator2->id,
                ]))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect($list->fresh()->hasUser($collaborator2))->toBeFalse();
        });

        it('prevents removing self via this route', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->delete(route('lists.collaborator.remove', [
                    'locale' => 'en',
                    'list' => $list->slug,
                    'user' => $collaborator->id,
                ]))
                ->assertRedirect()
                ->assertSessionHas('error');

            expect($list->fresh()->hasUser($collaborator))->toBeTrue();
        });

        it('prevents collaborator from removing the list creator', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $this->actingAs($collaborator)
                ->delete(route('lists.collaborator.remove', [
                    'locale' => 'en',
                    'list' => $list->slug,
                    'user' => $creator->id,
                ]))
                ->assertRedirect()
                ->assertSessionHas('error');

            expect($list->fresh()->hasUser($creator))->toBeTrue();
        });
    });

    describe('canceling invitation', function () {
        it('allows collaborator to cancel pending invitation', function () {
            $creator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $invitation = ListInvitation::factory()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'email' => 'pending@example.com',
            ]);

            $this->actingAs($creator)
                ->delete(route('lists.invitation.cancel', [
                    'locale' => 'en',
                    'invitation' => $invitation->id,
                ]))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect(ListInvitation::find($invitation->id))->toBeNull();
        });
    });

    describe('invitation banner', function () {
        it('shows invitation banner when user has pending invitations', function () {
            $creator = User::factory()->create();
            $invitee = User::factory()->create(['email' => 'invitee@example.com']);
            $list = GiftList::factory()->create(['creator_id' => $creator->id, 'name' => 'Test Wishlist']);
            $list->users()->attach($creator->id);

            ListInvitation::factory()->forExistingUser()->create([
                'list_id' => $list->id,
                'inviter_id' => $creator->id,
                'invitee_id' => $invitee->id,
                'email' => 'invitee@example.com',
            ]);

            $this->actingAs($invitee)
                ->get('/en/dashboard')
                ->assertStatus(200)
                ->assertSee('Test Wishlist');
        });
    });

});
