<?php

use App\Events\GiftCreated;
use App\Jobs\RecordAffiliateClick;
use App\Models\Gift;
use App\Services\AffiliateUrlTransformer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

describe('Affiliate redirect', function () {
    it('redirects to the gift url and dispatches click job', function () {
        Queue::fake();

        $gift = Gift::factory()->create(['url' => 'https://www.bol.com/nl/p/some-product/123456789/']);

        $response = $this->get("/go/{$gift->id}");

        $response->assertRedirect();
        Queue::assertPushed(RecordAffiliateClick::class, function ($job) use ($gift) {
            return $job->giftId === $gift->id
                && $job->url === $gift->url
                && $job->retailerDomain === 'bol.com';
        });
    });

    it('returns 404 for gifts without a url', function () {
        $gift = Gift::factory()->create(['url' => null, 'fetch_status' => 'skipped']);

        $response = $this->get("/go/{$gift->id}");

        $response->assertNotFound();
    });

    it('redirects to skimlinks url when enabled', function () {
        Queue::fake();
        config([
            'services.skimlinks.enabled' => true,
            'services.skimlinks.publisher_id' => 'TEST123',
        ]);

        $gift = Gift::factory()->create(['url' => 'https://www.bol.com/nl/p/test/999/']);

        $response = $this->get("/go/{$gift->id}");

        $response->assertRedirect();
        $location = $response->headers->get('Location');
        expect($location)->toContain('go.skimresources.com');
        expect($location)->toContain('TEST123');
        expect($location)->toContain(urlencode('https://www.bol.com/nl/p/test/999/'));
    });

    it('redirects to raw url when skimlinks is disabled', function () {
        Queue::fake();
        config(['services.skimlinks.enabled' => false]);

        $gift = Gift::factory()->create(['url' => 'https://www.coolblue.nl/product/12345/']);

        $response = $this->get("/go/{$gift->id}");

        $response->assertRedirect('https://www.coolblue.nl/product/12345/');
    });

    it('is throttled at 60 requests per minute', function () {
        Queue::fake();

        $gift = Gift::factory()->create(['url' => 'https://www.bol.com/nl/p/test/1/']);

        // The route has throttle:60,1 middleware — just verify it's applied
        $response = $this->get("/go/{$gift->id}");
        $response->assertRedirect();
        expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue();
    });
});

describe('AffiliateUrlTransformer', function () {
    it('returns raw url when disabled', function () {
        config(['services.skimlinks.enabled' => false]);

        $transformer = new AffiliateUrlTransformer;
        $result = $transformer->transform('https://www.bol.com/test');

        expect($result)->toBe('https://www.bol.com/test');
    });

    it('wraps url with skimlinks when enabled', function () {
        config([
            'services.skimlinks.enabled' => true,
            'services.skimlinks.publisher_id' => 'PUB42',
        ]);

        $transformer = new AffiliateUrlTransformer;
        $result = $transformer->transform('https://www.bol.com/test', 10, 5);

        expect($result)
            ->toContain('go.skimresources.com')
            ->toContain('PUB42')
            ->toContain(urlencode('https://www.bol.com/test'))
            ->toContain('g10_l5');
    });

    it('omits xcust when no ids provided', function () {
        config([
            'services.skimlinks.enabled' => true,
            'services.skimlinks.publisher_id' => 'PUB42',
        ]);

        $transformer = new AffiliateUrlTransformer;
        $result = $transformer->transform('https://example.com');

        expect($result)->not->toContain('xcust');
    });

    it('reports enabled status correctly', function () {
        config([
            'services.skimlinks.enabled' => true,
            'services.skimlinks.publisher_id' => 'PUB42',
        ]);

        expect((new AffiliateUrlTransformer)->isEnabled())->toBeTrue();

        config(['services.skimlinks.enabled' => false]);
        expect((new AffiliateUrlTransformer)->isEnabled())->toBeFalse();
    });
});

describe('RecordAffiliateClick job', function () {
    it('creates an affiliate click record', function () {
        Event::fake([GiftCreated::class]);
        $gift = Gift::factory()->create(['url' => 'https://www.bol.com/test']);

        $job = new RecordAffiliateClick(
            giftId: $gift->id,
            listId: null,
            url: 'https://www.bol.com/test',
            affiliateUrl: 'https://go.skimresources.com/?id=PUB&url=https%3A%2F%2Fwww.bol.com%2Ftest',
            retailerDomain: 'bol.com',
            ipHash: hash('sha256', '127.0.0.1'),
            userAgent: 'TestAgent/1.0',
            clickedAt: now(),
        );

        $job->handle();

        $this->assertDatabaseHas('affiliate_clicks', [
            'gift_id' => $gift->id,
            'url' => 'https://www.bol.com/test',
            'retailer_domain' => 'bol.com',
        ]);
    });
});

describe('Gift::buyUrl()', function () {
    it('returns affiliate redirect route for gifts with url', function () {
        Event::fake([GiftCreated::class]);
        $gift = Gift::factory()->create(['url' => 'https://www.bol.com/test']);

        expect($gift->buyUrl())->toBe(route('affiliate.redirect', $gift));
    });

    it('returns null for gifts without url', function () {
        $gift = Gift::factory()->create(['url' => null, 'fetch_status' => 'skipped']);

        expect($gift->buyUrl())->toBeNull();
    });
});
