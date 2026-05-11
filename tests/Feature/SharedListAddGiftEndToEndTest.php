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

describe('shared list — collaborator adds a gift (end-to-end)', function () {

    it('lets a freshly-accepted collaborator add a gift via the form', function () {
        // Owner with their default list.
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $ownerList = GiftList::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Owner Wishlist',
            'is_default' => true,
        ]);
        $ownerList->users()->attach($owner->id, ['joined_at' => now()]);

        // Collaborator (also has own default list, like a real signup).
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);
        $collabOwnList = GiftList::factory()->create([
            'creator_id' => $collaborator->id,
            'name' => 'Collab Own',
            'is_default' => true,
        ]);
        $collabOwnList->users()->attach($collaborator->id, ['joined_at' => now()]);

        // Owner invites collaborator.
        $invitation = ListInvitation::factory()->forExistingUser()->create([
            'list_id' => $ownerList->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $collaborator->id,
            'email' => 'collab@example.com',
        ]);

        // Collaborator accepts.
        $this->actingAs($collaborator)
            ->post(route('lists.invitation.accept', ['locale' => 'en', 'token' => $invitation->token]))
            ->assertRedirect();

        expect($ownerList->fresh()->hasUser($collaborator))->toBeTrue();

        // GET the add-gift form, pre-selecting the shared list.
        $this->actingAs($collaborator)
            ->get('/en/gifts/create?list='.$ownerList->id)
            ->assertStatus(200)
            ->assertSee('Owner Wishlist'); // option visible in the select

        // POST the form to create a gift on the shared list.
        $response = $this->actingAs($collaborator)
            ->post('/en/gifts', [
                'input' => 'https://example.com/widget',
                'list_id' => $ownerList->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $gift = Gift::where('user_id', $collaborator->id)->first();
        expect($gift)->not->toBeNull();
        expect($ownerList->fresh()->gifts()->where('gifts.id', $gift->id)->exists())->toBeTrue();
    });

    it('lets a single-list collaborator add a gift to a non-default shared list', function () {
        // Reproduces the production bug: collaborator only has the shared list,
        // and the owner created that list as non-default (e.g. an event list).
        $owner = User::factory()->create();
        $ownerList = GiftList::factory()->create([
            'creator_id' => $owner->id,
            'name' => 'Owner Event List',
            'is_default' => false,
        ]);
        $ownerList->users()->attach($owner->id, ['joined_at' => now()]);

        $collaborator = User::factory()->create();
        $ownerList->users()->attach($collaborator->id, ['joined_at' => now()]);

        // The form GET is in single-list mode and renders a hidden list_id input.
        $this->actingAs($collaborator)
            ->get('/en/gifts/create?list='.$ownerList->id)
            ->assertStatus(200)
            ->assertSee('name="list_id"', false)
            ->assertSee('value="'.$ownerList->id.'"', false);

        // The form POSTs list_id even in single-list mode.
        $response = $this->actingAs($collaborator)
            ->post('/en/gifts', [
                'input' => 'https://example.com/widget',
                'list_id' => $ownerList->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        expect(Gift::where('user_id', $collaborator->id)->count())->toBe(1);
        expect($ownerList->fresh()->gifts()->count())->toBe(1);
    });

    it('renders the list_id error on the form when no list is found', function () {
        // User with NO lists at all (edge case).
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/en/gifts/create')
            ->post('/en/gifts', [
                'input' => 'https://example.com/widget',
            ]);

        $response->assertRedirect('/en/gifts/create');
        $response->assertSessionHasErrors('list_id');

        // The blade now renders @error('list_id') so the message is visible.
        $this->actingAs($user)
            ->withSession(['errors' => app('session.store')->get('errors')])
            ->get('/en/gifts/create')
            ->assertSee(__('No valid list found. Please create a list first.'));
    });

});
