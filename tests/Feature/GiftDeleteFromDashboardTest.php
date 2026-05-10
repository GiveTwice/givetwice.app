<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('Delete gift from dashboard', function () {
    it('renders a delete button on each gift tile for the owner', function () {
        $owner = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $owner->id]);
        $list->users()->attach($owner->id);

        $gift = Gift::factory()->create(['user_id' => $owner->id]);
        $gift->lists()->attach($list->id);

        $response = $this->actingAs($owner)->get('/en/dashboard');

        $response->assertStatus(200);
        $response->assertSee('open-confirm-delete-gift-'.$gift->id, false);
        $response->assertSee('action="'.url('/en/gifts/'.$gift->id).'"', false);
    });

    it('deletes the gift via the destroy route', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->delete('/en/gifts/'.$gift->id)
            ->assertRedirect('/en/dashboard');

        expect(Gift::find($gift->id))->toBeNull();
    });

    it('does not allow non-owners to delete a gift', function () {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger)
            ->delete('/en/gifts/'.$gift->id)
            ->assertForbidden();

        expect(Gift::find($gift->id))->not->toBeNull();
    });
});
