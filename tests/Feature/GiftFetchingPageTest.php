<?php

use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('Gift fetching page', function () {
    it('shows the fetching page for a pending gift', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->pending()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertOk()
            ->assertViewIs('gifts.fetching');
    });

    it('shows the fetching page for a fetching gift', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->fetching()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertOk()
            ->assertViewIs('gifts.fetching');
    });

    it('redirects completed gift to edit with fetch_success context', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $user->id, 'fetch_status' => 'completed']);

        $this->actingAs($user)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertRedirect("/en/gifts/{$gift->id}/edit")
            ->assertSessionHas('gift_context', 'fetch_success');
    });

    it('redirects failed gift to edit with fetch_failed context', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->failed()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertRedirect("/en/gifts/{$gift->id}/edit")
            ->assertSessionHas('gift_context', 'fetch_failed');
    });

    it('redirects skipped gift to edit with manual_entry context', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->skipped()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertRedirect("/en/gifts/{$gift->id}/edit")
            ->assertSessionHas('gift_context', 'manual_entry');
    });

    it('blocks unauthorized users', function () {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $gift = Gift::factory()->pending()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->get("/en/gifts/{$gift->id}/fetching")
            ->assertForbidden();
    });
});
