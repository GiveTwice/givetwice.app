<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use App\Support\ListColour;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Queue::fake();
    $this->owner = User::factory()->create();
});

function managedList(User $user, array $attributes = []): GiftList
{
    $list = GiftList::factory()->create(['creator_id' => $user->id, ...$attributes]);
    $user->lists()->attach($list);

    return $list;
}

function sidebarListLinks(string $html): array
{
    preg_match('/<div[^>]*data-sidebar-children[^>]*>(.*?)<\/div>/s', $html, $block);
    preg_match_all('/<a href="([^"]+)"([^>]*)>/s', $block[1] ?? '', $links, PREG_SET_ORDER);

    return collect($links)->map(fn ($link) => ['href' => $link[1], 'active' => str_contains($link[2], 'aria-current="page"')])->all();
}

it('shows every list the user manages, oldest first, and none of anyone else', function () {
    $older = managedList($this->owner, ['created_at' => now()->subDays(2)]);
    $shared = GiftList::factory()->create(['created_at' => now()->subDay()]);
    $shared->users()->attach($this->owner);
    $newer = managedList($this->owner);
    GiftList::factory()->create();

    $this->actingAs($this->owner)->get('/en/dashboard')->assertOk()
        ->assertViewHas('lists', fn ($lists) => $lists->pluck('id')->all() === [$older->id, $shared->id, $newer->id]);
});

it('previews the same first four gifts as the list page, with the full count and no reservations', function () {
    $list = managedList($this->owner, ['spoiler_mode' => true]);
    $gifts = Gift::factory()->skipped()->count(6)->create();
    foreach ($gifts->reverse()->values() as $index => $gift) {
        $list->gifts()->attach($gift, ['sort_order' => min($index, 3)]);
    }
    $gifts[0]->claims()->create(['user_id' => User::factory()->create()->id, 'confirmed_at' => now()]);

    $preview = $this->actingAs($this->owner)->get('/en/dashboard')->viewData('lists')->sole();
    $listPage = $this->get('/en/list/'.$list->slug)->viewData('list');

    expect($preview->gifts_count)->toBe(6)
        ->and($preview->gifts->pluck('id')->all())->toBe($listPage->gifts->take(4)->pluck('id')->all())
        ->and($preview->gifts->first()->relationLoaded('claims'))->toBeFalse();
});

it('shows an uploaded collaborator photo on the list card', function () {
    Storage::fake('public');
    $list = managedList($this->owner);
    $collaborator = User::factory()->create();
    $list->users()->attach($collaborator);
    $collaborator->addMedia(UploadedFile::fake()->image('me.jpg', 300, 300))->toMediaCollection('profile');

    $this->actingAs($this->owner)->get('/en/dashboard')
        ->assertSee($collaborator->fresh()->getProfileImageUrl('thumb'), false);
});

it('does not run more queries for more lists', function () {
    $queriesFor = function (int $listCount): int {
        $user = User::factory()->create();
        foreach (range(1, $listCount) as $ignored) {
            $list = managedList($user);
            $list->users()->attach(User::factory()->create());
            $list->gifts()->attach(Gift::factory()->skipped()->create());
        }
        $this->actingAs($user);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/en/dashboard')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    expect($queriesFor(6))->toBe($queriesFor(2));
});

it('links up to five lists in the sidebar on every app page, plus all lists', function () {
    $lists = collect(range(1, 6))->map(fn ($day) => managedList($this->owner, ['created_at' => now()->subDays(7 - $day)]));

    $links = sidebarListLinks($this->actingAs($this->owner)->get('/en/settings')->assertOk()->getContent());

    expect(collect($links)->pluck('href')->all())->toBe([
        ...$lists->take(5)->map(fn ($list) => route('list.show', ['locale' => 'en', 'list' => $list]))->all(),
        route('dashboard.locale', ['locale' => 'en']),
        route('lists.create', ['locale' => 'en']),
    ]);
});

it('keeps the open list in the sidebar and marks it, even when it is the sixth', function () {
    $lists = collect(range(1, 6))->map(fn ($day) => managedList($this->owner, ['created_at' => now()->subDays(7 - $day)]));
    $sixth = $lists->last();

    $links = collect(sidebarListLinks($this->actingAs($this->owner)->get('/en/list/'.$sixth->slug)->getContent()));

    expect($links->take(5)->pluck('href')->all())->toContain(route('list.show', ['locale' => 'en', 'list' => $sixth]))
        ->and($links->where('active', true)->pluck('href')->all())->toBe([route('list.show', ['locale' => 'en', 'list' => $sixth])]);
});

it('replaces the fourth photo with the number of gifts not shown', function (int $giftCount, ?string $moreLabel, int $photoCount) {
    $list = managedList($this->owner);
    foreach (range(1, $giftCount) as $position) {
        $list->gifts()->attach(Gift::factory()->skipped()->create(['original_image_url' => "https://example.test/{$position}.jpg"]), ['sort_order' => $position]);
    }

    $html = $this->actingAs($this->owner)->get('/en/dashboard')->getContent();

    preg_match('/data-more-gifts[^>]*>\s*<span[^>]*>([^<]+)</', $html, $more);
    expect($more[1] ?? null)->toBe($moreLabel)
        ->and(preg_match_all('#src="https://example\.test/\d+\.jpg"#', $html))->toBe($photoCount);
})->with([
    'four gifts fit' => [4, null, 4],
    'five gifts' => [5, '+2', 3],
    'thirteen gifts' => [13, '+10', 3],
]);

it('gives a list the same colour on the dashboard, in the sidebar and on its own page', function () {
    $lists = collect(range(1, 3))->map(fn ($day) => managedList($this->owner, ['created_at' => now()->subDays(4 - $day)]));
    $second = $lists->get(1);
    $colour = ListColour::forPosition(1);

    $dashboard = $this->actingAs($this->owner)->get('/en/dashboard')->getContent();
    $listPage = $this->get('/en/list/'.$second->slug)->getContent();

    expect($dashboard)->toMatch('#href="'.preg_quote(route('list.show', ['locale' => 'en', 'list' => $second]), '#').'"\s+class="[^"]*'.preg_quote($colour['card'], '#').'#')
        ->and($listPage)->toMatch('#<span class="ribbon-squiggle relative mr-8 inline-block[^"]* '.preg_quote($colour['underline'], '#').'">#')
        ->and($listPage)->toMatch('#<a href="'.preg_quote(route('list.show', ['locale' => 'en', 'list' => $second]), '#').'"[^>]*><span class="[^"]*'.preg_quote($colour['dot'], '#').'#');
});
