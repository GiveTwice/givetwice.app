<?php

use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Queue::fake();
});

function ogImageHash(TestResponse $response): string
{
    preg_match('#<meta property="og:image" content="[^"]*/og-image/([a-f0-9]{32})\.jpeg">#', $response->getContent(), $match);

    expect($match)->not->toBeEmpty();

    return $match[1];
}

function ogImageScreenshotUrl(string $hash): ?string
{
    return Cache::get("og-image:{$hash}")['url'] ?? null;
}

describe('default OG image', function () {
    it('screenshots the home page of the locale the page was shared in', function (string $locale) {
        $hash = ogImageHash($this->get("/{$locale}/faq")->assertOk());

        expect(ogImageScreenshotUrl($hash))->toBe(url("/{$locale}"));
    })->with(['en', 'nl', 'fr']);

    it('gives every locale its own image', function () {
        $hashes = collect(['en', 'nl', 'fr'])->map(fn (string $locale) => ogImageHash($this->get("/{$locale}/faq")));

        expect($hashes->unique())->toHaveCount(3);
    });

    it('shares one image between the pages of a locale', function () {
        expect(ogImageHash($this->get('/nl/login')))
            ->toBe(ogImageHash($this->get('/nl/faq')))
            ->toBe(ogImageHash($this->get('/nl')));
    });

    it('never screenshots a page behind a login', function () {
        $hash = ogImageHash($this->actingAs(User::factory()->create())->get('/nl/dashboard')->assertOk());

        expect(ogImageScreenshotUrl($hash))->toBe(url('/nl'));
    });

    it('renders the template in the locale of the screenshot url', function (string $locale) {
        $this->get("/{$locale}?ogimage")
            ->assertOk()
            ->assertSee("<div lang=\"{$locale}\"", false)
            ->assertDontSee('<header', false);
    })->with(['en', 'nl', 'fr']);
});

describe('public list OG image', function () {
    it('screenshots the list in the locale it was shared in', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $list->users()->attach($user->id);

        $hashes = collect(['en', 'nl', 'fr'])->mapWithKeys(fn (string $locale) => [
            $locale => ogImageHash($this->get($list->getPublicUrl($locale))->assertOk()),
        ]);

        expect($hashes->unique())->toHaveCount(3);
        $hashes->each(fn (string $hash, string $locale) => expect(ogImageScreenshotUrl($hash))->toBe($list->getPublicUrl($locale)));
    });
});
