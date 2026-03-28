<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('List OG Image', function () {

    describe('og:image meta tag on public list page', function () {
        it('includes an og:image meta tag pointing to the og-image package route', function () {
            $user = User::factory()->create(['name' => 'Alice']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('og:image', false)
                ->assertSee('data-og-image', false);
        });

        it('includes og:title with owner name', function () {
            $user = User::factory()->create(['name' => 'Sarah']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('Sarah', false);
        });

        it('handles possessive for names ending in s', function () {
            $user = User::factory()->create(['name' => 'James']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee("James&#039;", false);
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

        it('includes the og-image template in the page for the package to screenshot', function () {
            $user = User::factory()->create(['name' => 'Iris']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            // The spatie/laravel-og-image package renders a hidden <template data-og-image> tag
            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('data-og-image', false);
        });

        it('renders list name in the og-image template', function () {
            $user = User::factory()->create(['name' => 'Jane']);
            $list = GiftList::factory()->create(['creator_id' => $user->id, 'name' => 'Christmas 2026']);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('Christmas 2026', false);
        });
    });

});
