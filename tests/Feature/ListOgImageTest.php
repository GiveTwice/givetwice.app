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
        it('includes the og-image template for the package to screenshot', function () {
            $user = User::factory()->create(['name' => 'Alice']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('data-og-image', false)
                ->assertSee('data-og-hash', false);
        });

        it('includes og:title with owner name', function () {
            $user = User::factory()->create(['name' => 'Sarah']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('Sarah', false);
        });

        it('localizes the owner attribution using translation keys', function () {
            $user = User::factory()->create(['name' => 'James']);
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $this->get("/en/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('By James', false);

            $this->get("/nl/v/{$list->id}/{$list->slug}")
                ->assertOk()
                ->assertSee('Van James', false);
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
