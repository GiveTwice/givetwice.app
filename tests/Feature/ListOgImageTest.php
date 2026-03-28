<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('List OG Image', function () {

    describe('og-image endpoint', function () {
        it('returns 200 with image/png content type', function () {
            $user = User::factory()->create(['name' => 'Alice']);
            $list = GiftList::factory()->create(['creator_id' => $user->id, 'name' => 'Birthday wishlist']);

            $this->get("/en/v/{$list->id}/og-image")
                ->assertOk()
                ->assertHeader('Content-Type', 'image/png');
        });

        it('returns 404 for non-existent list', function () {
            $this->get('/en/v/99999/og-image')
                ->assertNotFound();
        });

        it('serves a valid PNG binary (starts with PNG magic bytes)', function () {
            $user = User::factory()->create(['name' => 'Bob']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);

            $response = $this->get("/en/v/{$list->id}/og-image");
            $response->assertOk();

            // PNG files start with \x89PNG
            $content = $response->getContent();
            expect(substr($content, 0, 4))->toBe("\x89PNG");
        });

        it('works with different locales', function () {
            $user = User::factory()->create(['name' => 'Claire']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);

            $this->get("/nl/v/{$list->id}/og-image")->assertOk();
            $this->get("/fr/v/{$list->id}/og-image")->assertOk();
        });

        it('returns same PNG from cache on second request', function () {
            $user = User::factory()->create(['name' => 'Dave']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);

            $first = $this->get("/en/v/{$list->id}/og-image")->getContent();
            $second = $this->get("/en/v/{$list->id}/og-image")->getContent();

            expect($first)->toBe($second);
        });
    });

    describe('public list OG meta tags', function () {
        it('includes dynamic og:image pointing to the og-image route', function () {
            $user = User::factory()->create(['name' => 'Eve']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $response = $this->get("/en/v/{$list->id}/{$list->slug}");
            $response->assertOk();

            $expectedUrl = route('list.og-image', ['locale' => 'en', 'list' => $list->id]);
            $response->assertSee($expectedUrl, false);
        });

        it('includes og:title with possessive owner name', function () {
            $user = User::factory()->create(['name' => 'Sarah']);
            $list = GiftList::factory()->create(['creator_id' => $user->id, 'name' => 'My Christmas wishlist']);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('Sarah&#039;s wishlist on GiveTwice', false);
        });

        it('handles possessive for names ending in s', function () {
            $user = User::factory()->create(['name' => 'James']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('James&#039; wishlist on GiveTwice', false);
        });

        it('includes og:description with gift count', function () {
            $user = User::factory()->create(['name' => 'Frank']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $gifts = Gift::factory()->count(5)->create(['user_id' => $user->id]);
            $list->gifts()->attach($gifts->pluck('id'));

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('5 gifts', false);
        });

        it('uses singular "gift" for a list with one gift', function () {
            $user = User::factory()->create(['name' => 'Grace']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $gift = Gift::factory()->create(['user_id' => $user->id]);
            $list->gifts()->attach($gift->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('1 gift', false);
        });

        it('includes twitter:card summary_large_image', function () {
            $user = User::factory()->create(['name' => 'Hugo']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('summary_large_image', false);
        });

        it('includes og:image:width and og:image:height', function () {
            $user = User::factory()->create(['name' => 'Iris']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('og:image:width', false)
                ->assertSee('og:image:height', false);
        });
    });

});
