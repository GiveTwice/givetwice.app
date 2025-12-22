<?php

use App\Models\GiftList;
use App\Models\User;

describe('Public list slug', function () {
    it('redirects to correct slug when visiting with wrong slug', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Wishlist',
        ]);

        $response = $this->get("/en/v/{$list->id}/wrong-slug");

        $response->assertRedirect("/en/v/{$list->id}/{$list->slug}");
        $response->assertStatus(301);
    });

    it('redirects to correct slug when visiting without slug', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Wishlist',
        ]);

        $response = $this->get("/en/v/{$list->id}");

        $response->assertRedirect("/en/v/{$list->id}/{$list->slug}");
        $response->assertStatus(301);
    });

    it('does not redirect when visiting with correct slug', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Wishlist',
        ]);

        $response = $this->get("/en/v/{$list->id}/{$list->slug}");

        $response->assertOk();
    });

    it('updates slug when list name changes', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
        ]);

        expect($list->slug)->toBe('original-name');

        $list->update(['name' => 'New Name']);

        expect($list->fresh()->slug)->toBe('new-name');
    });

    it('redirects old slug URL to new slug URL after name change', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
        ]);

        $oldSlug = $list->slug;
        expect($oldSlug)->toBe('original-name');

        $list->update(['name' => 'Updated Name']);
        $newSlug = $list->fresh()->slug;
        expect($newSlug)->toBe('updated-name');

        $response = $this->get("/en/v/{$list->id}/{$oldSlug}");

        $response->assertRedirect("/en/v/{$list->id}/{$newSlug}");
        $response->assertStatus(301);
    });
});
