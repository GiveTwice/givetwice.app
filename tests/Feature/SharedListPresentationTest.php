<?php

use App\Events\GiftClaimed;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;

beforeEach(function () {
    Queue::fake();
    $this->owner = User::factory()->create();
    $this->collaborator = User::factory()->create();
    $this->list = GiftList::factory()->create(['creator_id' => $this->owner->id]);
    $this->list->users()->attach([$this->owner->id, $this->collaborator->id]);
    $this->reserved = Gift::factory()->create(['user_id' => $this->owner->id, 'title' => 'A reserved', 'price_in_cents' => 1999, 'currency' => 'EUR']);
    $this->free = Gift::factory()->create(['user_id' => $this->owner->id, 'title' => 'B free', 'price_in_cents' => 2000, 'currency' => 'EUR']);
    $this->multiple = Gift::factory()->create(['user_id' => $this->owner->id, 'title' => 'C multiple', 'price_in_cents' => null, 'allow_multiple_claims' => true]);
    $this->list->gifts()->attach([$this->reserved->id, $this->free->id, $this->multiple->id]);
    $this->claim = Claim::factory()->create(['gift_id' => $this->reserved->id]);
    Claim::factory()->create(['gift_id' => $this->multiple->id]);
});

describe('reservation visibility', function () {
    it('protects managers by default and restores status only with spoiler mode', function (string $role, bool $spoiler) {
        $this->list->update(['spoiler_mode' => $spoiler]);
        $user = $this->{$role};
        $this->actingAs($user);
        $response = $this->get($this->list->getPublicUrl('en').'?filter=free')->assertOk();
        $gifts = $response->viewData('gifts');
        expect($response->viewData('showReservations'))->toBe($spoiler);
        if ($spoiler) {
            expect($gifts->pluck('id')->all())->toBe([$this->free->id, $this->multiple->id]);
            $response->assertSee('data-reservation-progress', false);
        } else {
            expect($gifts->pluck('id')->all())->toBe([$this->reserved->id, $this->free->id, $this->multiple->id]);
            expect($response->viewData('availableGifts'))->toBeNull();
            $response->assertDontSee('data-reservation-progress', false)->assertDontSee('data-reserved-heading', false);
            foreach ($gifts as $gift) {
                expect($gift->claims)->toBeEmpty();
                expect($gift->getAttributes())->not->toHaveKey('claims_count');
            }
        }
        $dashboard = $this->get('/en/dashboard')->assertOk();
        $list = $dashboard->viewData('lists')->firstWhere('id', $this->list->id);
        expect($list->gifts->firstWhere('id', $this->reserved->id)->claims->count())->toBe($spoiler ? 1 : 0);
        expect($list->getAttributes())->not->toHaveKey('claimed_gifts_count');
        $reservationVisible = null;
        Illuminate\Support\Facades\View::composer('components.shared-gift-tile', function (View $view) use (&$reservationVisible): void {
            $reservationVisible = $view->getData()['isReserved'];
        });
        $this->get('/en/v/'.$this->list->id.'/'.$this->list->slug.'/gifts/'.$this->reserved->id.'/card')->assertOk();
        expect($reservationVisible)->toBe($spoiler);
        $channels = collect((new GiftClaimed($this->reserved, $this->claim, 1))->broadcastOn())->pluck('name')->all();
        expect(in_array('private-user.'.$user->id, $channels, true))->toBe($spoiler);
        expect($channels)->not->toContain('list.'.$this->list->slug);
    })->with(['owner', 'collaborator'])->with([false, true]);

    it('keeps owner preview order independent of reservations', function () {
        $this->actingAs($this->owner);
        $before = $this->get($this->list->getPublicUrl('en'))->viewData('gifts')->pluck('id')->all();
        Claim::factory()->create(['gift_id' => $this->free->id]);
        expect($this->get($this->list->getPublicUrl('en'))->viewData('gifts')->pluck('id')->all())->toBe($before);
        $this->list->update(['spoiler_mode' => true]);
        expect($this->get($this->list->getPublicUrl('en'))->viewData('gifts')->pluck('id')->all())->toBe($before);
    });

    it('keeps claim entry points from revealing status to managers', function (string $role) {
        $this->actingAs($this->{$role});
        foreach ([$this->reserved, $this->free] as $gift) {
            $this->get('/en/gifts/'.$gift->id.'/claim')->assertRedirect($this->list->getPublicUrl('en'));
            $this->post('/en/gifts/'.$gift->id.'/claim')->assertSessionHas('error');
            $this->post('/en/gifts/'.$gift->id.'/claim-anonymous', ['email' => 'test@example.test'])->assertRedirect('/en/dashboard');
        }
        expect($this->reserved->claims()->count())->toBe(1);
        expect($this->free->claims()->count())->toBe(0);
    })->with(['owner', 'collaborator']);

    it('does not broadcast a shared gift to a manager with any protected list', function () {
        $other = GiftList::factory()->create(['spoiler_mode' => true]);
        $other->users()->attach($this->owner);
        $other->gifts()->attach($this->reserved);
        $channels = collect((new GiftClaimed($this->reserved, $this->claim, 1))->broadcastOn())->pluck('name');
        expect($channels)->not->toContain('private-user.'.$this->owner->id);
    });
});

describe('buyer list', function () {
    it('uses saved order regardless of reservation state', function () {
        $response = $this->get($this->list->getPublicUrl('en'))->assertOk();
        expect($response->viewData('gifts')->pluck('id')->all())->toBe([$this->reserved->id, $this->free->id, $this->multiple->id]);
        expect($response->viewData('availableGifts'))->toBe(2);

    });

    it('filters free gifts and euro prices below twenty', function (string $filter, array $expected) {
        $foreign = Gift::factory()->create(['currency' => 'USD', 'price_in_cents' => 100]);
        $this->list->gifts()->attach($foreign);
        $response = $this->get($this->list->getPublicUrl('en').'?filter='.$filter)->assertOk();
        $ids = array_map(fn ($name) => $this->{$name}->id, $expected);
        if ($filter === 'free') {
            $ids[] = $foreign->id;
        }
        expect($response->viewData('gifts')->pluck('id')->all())->toEqualCanonicalizing($ids);
    })->with(['free' => ['free', ['free', 'multiple']], 'budget' => ['under20', ['reserved']]]);

    it('treats pending reservations as free', function () {
        $this->claim->update(['confirmed_at' => null]);
        expect($this->get($this->list->getPublicUrl('en').'?filter=free')->viewData('gifts')->total())->toBe(3);
    });

    it('keeps the OG selection unchanged across reservations and filters', function () {
        $before = view('public.list-og-image', ['list' => $this->list])->render();
        Claim::factory()->create(['gift_id' => $this->free->id]);
        expect(view('public.list-og-image', ['list' => $this->list->fresh()])->render())->toBe($before);
        $all = $this->get($this->list->getPublicUrl('en'))->getContent();
        $filtered = $this->get($this->list->getPublicUrl('en').'?filter=under20')->getContent();
        preg_match('/data-og-hash="([^"]+)"/', $all, $allHash);
        preg_match('/data-og-hash="([^"]+)"/', $filtered, $filteredHash);
        expect($allHash[1])->toBe($filteredHash[1]);
        preg_match('/<meta name="description" content="([^"]+)"/', $all, $allDescription);
        foreach (['free', 'under20'] as $filter) {
            $page = $this->get($this->list->getPublicUrl('en').'?filter='.$filter)->getContent();
            preg_match('/<meta name="description" content="([^"]+)"/', $page, $description);
            expect($description[1])->toBe($allDescription[1]);
        }
    });
});

describe('list and gift controls', function () {
    it('validates occasion and date', function (array $fields, string $error) {
        $this->actingAs($this->owner)->put('/en/list/'.$this->list->slug, ['name' => 'Family', ...$fields])->assertSessionHasErrors($error);
    })->with([
        [['occasion' => 'unknown'], 'occasion'],
        [['occasion_date' => '2026-02-30'], 'occasion_date'],
        [['occasion_date' => 'next Thursday'], 'occasion_date'],
        [['spoiler_mode' => true], 'spoiler_confirmation'],
        [['spoiler_mode' => 'yes'], 'spoiler_mode'],
    ]);

    it('saves an optional occasion and family date and confirms spoilers once', function () {
        $this->actingAs($this->collaborator);
        $url = '/en/list/'.$this->list->slug;
        $this->put($url, ['name' => $this->list->name, 'occasion' => 'sinterklaas', 'occasion_date' => '2026-12-06', 'spoiler_mode' => true, 'spoiler_confirmation' => true])->assertSessionHasNoErrors();
        expect($this->list->fresh()->occasion_date->format('Y-m-d'))->toBe('2026-12-06');
        expect($this->list->fresh()->spoiler_confirmed_at)->not->toBeNull();
        $this->put($url, ['name' => $this->list->name, 'occasion' => null, 'occasion_date' => null, 'spoiler_mode' => false])->assertSessionHasNoErrors();
        $this->put($url, ['name' => $this->list->name, 'spoiler_mode' => true])->assertSessionHasNoErrors();
        expect($this->list->fresh()->spoiler_mode)->toBeTrue();
        expect($this->list->fresh()->occasion_date)->toBeNull();
    });

    it('restricts the most wanted toggle to managers', function (string $role, bool $allowed) {
        $user = $role === 'outsider' ? User::factory()->create() : $this->{$role};
        $response = $this->actingAs($user)->put('/en/gifts/'.$this->multiple->id, ['most_wanted' => true]);
        $allowed ? $response->assertRedirect() : $response->assertForbidden();
        expect($this->multiple->fresh()->most_wanted)->toBe($allowed);
        expect($this->multiple->fresh()->allow_multiple_claims)->toBeTrue();
        if ($allowed) {
            $this->put('/en/gifts/'.$this->multiple->id, ['most_wanted' => false])->assertRedirect();
            expect($this->multiple->fresh()->most_wanted)->toBeFalse();
        }
    })->with([['owner', true], ['collaborator', true], ['outsider', false]]);
});
