<?php

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use App\Models\User;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\ProductInfo;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\TransportAttempt;
use GiveTwice\ProductInfoFetcher\Enum\FetchOutcome;
use GiveTwice\ProductInfoFetcher\HeadlessBrowser\HeadlessFetcher;
use GiveTwice\ProductInfoFetcher\HeadlessBrowser\HeadlessFetchResult;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Spatie\SlackAlerts\Facades\SlackAlert;

beforeEach(function () {
    Queue::fake();
    Event::fake([GiftFetchCompleted::class]);
});

function mockFetcher(ProductInfo $productInfo): ProductInfoFetcher
{
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->once()->andReturn($productInfo);

    return $fetcher;
}

function mockFetcherThrows(Throwable $exception): ProductInfoFetcher
{
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchResult')->andThrow($exception);

    return $fetcher;
}

function mockImageAction(bool $fromUrlSuccess = false): ProcessGiftImageAction
{
    $imageAction = Mockery::mock(ProcessGiftImageAction::class);
    $imageAction->shouldReceive('fromUrl')->andReturn($fromUrlSuccess);
    $imageAction->shouldReceive('dispatchCompletedEvent');

    return $imageAction;
}

describe('FetchGiftDetailsAction', function () {

    describe('job configuration', function () {
        it('has 4 retry attempts', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            expect(new FetchGiftDetailsAction($gift))
                ->tries->toBe(4);
        });

        it('has exponential backoff', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            expect(new FetchGiftDetailsAction($gift))
                ->backoff->toBe([5, 30, 60]);
        });

        it('runs on the fetch queue', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            FetchGiftDetailsAction::dispatch($gift);

            Queue::assertPushedOn('fetch', FetchGiftDetailsAction::class);
        });
    });

    describe('successful fetch', function () {
        it('sets fetch_status to fetching before making request', function () {
            $gift = Gift::factory()->for(User::factory())->create(['fetch_status' => 'pending']);

            $statusDuringFetch = null;
            $fetcher = Mockery::mock(ProductInfoFetcher::class);
            $fetcher->shouldReceive('fetchResult')->andReturnUsing(function () use ($gift, &$statusDuringFetch) {
                $statusDuringFetch = $gift->fresh()->fetch_status;

                return new ProductInfo(outcome: FetchOutcome::Success, name: 'Test');
            });

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($statusDuringFetch)->toBe('fetching');
        });

        it('updates gift with fetched product details', function () {
            $gift = Gift::factory()->for(User::factory())->create([
                'title' => 'Original Title',
                'description' => null,
                'price_in_cents' => null,
            ]);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Fetched Product Name',
                description: 'Fetched description',
                priceInCents: 4999,
                priceCurrency: 'USD',
            ));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh())
                ->title->toBe('Fetched Product Name')
                ->description->toBe('Fetched description')
                ->price_in_cents->toBe(4999)
                ->currency->toBe('USD');
        });

        it('updates rating and review count when available', function () {
            $gift = Gift::factory()->for(User::factory())->create([
                'rating' => null,
                'review_count' => null,
            ]);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Product',
                rating: 4.5,
                reviewCount: 127,
            ));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh())
                ->rating->toBe('4.5')
                ->review_count->toBe(127);
        });

        it('sets fetch_status to completed', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test')))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetch_status)->toBe('completed');
        });

        it('sets fetched_at timestamp', function () {
            $gift = Gift::factory()->for(User::factory())->create(['fetched_at' => null]);

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test')))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetched_at)->not->toBeNull();
        });

        it('clears fetch_error on success', function () {
            $gift = Gift::factory()->for(User::factory())->create([
                'fetch_error' => ['summary' => 'Previous error'],
                'fetch_attempts' => 2,
            ]);

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test')))
                ->handle(mockImageAction());

            expect($gift->fresh())
                ->fetch_error->toBeNull()
                ->fetch_status->toBe('completed');
        });

        it('dispatches completed event', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')->andReturn(false);
            $imageAction->shouldReceive('dispatchCompletedEvent')->once();

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test')))
                ->handle($imageAction);
        });
    });

    describe('data handling', function () {
        it('truncates long descriptions to 1500 characters', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                description: str_repeat('a', 2000),
            ));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect(strlen($gift->fresh()->description))->toBe(1500); // 1497 + '...'
        });

        it('keeps original title if fetched title is empty', function () {
            $gift = Gift::factory()->for(User::factory())->create(['title' => 'My Original Title']);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: '', priceInCents: 1500));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->title)->toBe('My Original Title');
        });

        it('keeps original description if fetched description is null', function () {
            $gift = Gift::factory()->for(User::factory())->create(['description' => 'My original description']);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test', description: null));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->description)->toBe('My original description');
        });

        it('keeps original currency if fetched currency is null', function () {
            $gift = Gift::factory()->for(User::factory())->create(['currency' => 'EUR']);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success, name: 'Test', priceInCents: 1999, priceCurrency: null));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->currency)->toBe('EUR');
        });
    });

    describe('image handling', function () {
        it('tries to add image from fetched URLs', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                allImageUrls: ['https://example.com/product.jpg'],
            ));

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')
                ->withArgs(fn ($g, $url) => $g->id === $gift->id && $url === 'https://example.com/product.jpg')
                ->once()
                ->andReturn(true);
            $imageAction->shouldReceive('dispatchCompletedEvent');

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle($imageAction);
        });

        it('updates original_image_url when image download succeeds', function () {
            $gift = Gift::factory()->for(User::factory())->create(['original_image_url' => null]);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                allImageUrls: ['https://example.com/success.jpg'],
            ));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction(fromUrlSuccess: true));

            expect($gift->fresh()->original_image_url)->toBe('https://example.com/success.jpg');
        });

        it('does not update original_image_url when image download fails', function () {
            $gift = Gift::factory()->for(User::factory())->create(['original_image_url' => null]);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                allImageUrls: ['https://example.com/fail.jpg'],
            ));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction(fromUrlSuccess: false));

            expect($gift->fresh()->original_image_url)->toBeNull();
        });

        it('tries multiple images until one succeeds', function () {
            $gift = Gift::factory()->for(User::factory())->create(['original_image_url' => null]);

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                allImageUrls: [
                    'https://example.com/first.jpg',
                    'https://example.com/second.jpg',
                    'https://example.com/third.jpg',
                ],
            ));

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')
                ->with($gift, 'https://example.com/first.jpg')
                ->once()
                ->andReturn(false);
            $imageAction->shouldReceive('fromUrl')
                ->with($gift, 'https://example.com/second.jpg')
                ->once()
                ->andReturn(true);
            $imageAction->shouldReceive('dispatchCompletedEvent');

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle($imageAction);

            expect($gift->fresh()->original_image_url)->toBe('https://example.com/second.jpg');
        });

        it('skips empty image URLs', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $fetcher = mockFetcher(new ProductInfo(outcome: FetchOutcome::Success,
                name: 'Test',
                allImageUrls: ['', null, 'https://example.com/valid.jpg'],
            ));

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')
                ->with($gift, 'https://example.com/valid.jpg')
                ->once()
                ->andReturn(true);
            $imageAction->shouldReceive('dispatchCompletedEvent');

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle($imageAction);
        });
    });

    describe('outcomes', function () {
        it('uses package detection for HTTP walls, removed pages and storefronts', function (int $status, string $html, string $outcome) {
            $gift = Gift::factory()->pending()->for(User::factory())->create(['url' => 'https://example.com/product']);
            $http = new MockHandler([new Response($status, [], $html)]);
            $fetcher = (new ProductInfoFetcher($gift->url))->setClient(new Client(['handler' => HandlerStack::create($http)]));
            $images = Mockery::mock(ProcessGiftImageAction::class);
            $images->shouldNotReceive('fromUrl');
            $images->shouldReceive('dispatchCompletedEvent')->once();

            (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle($images);

            expect($http->count())->toBe(0);
            expect($gift->fresh())->fetch_status->toBe('failed')->title->toBe($gift->suggestedTitle())
                ->fetch_error->toHaveKey('outcome', $outcome);
        })->with([
            'access denied' => [200, '<title>Access denied</title>', 'blocked'],
            'robot check' => [200, '<title>Robot Check</title>', 'blocked'],
            'captcha' => [200, '<h1>CAPTCHA</h1>', 'blocked'],
            'forbidden' => [403, '<h1>Forbidden</h1>', 'blocked'],
            'rate limited' => [429, '<h1>Too many requests</h1>', 'blocked'],
            'consent' => [200, '<h1>Before you continue</h1>', 'consent'],
            'not found' => [404, '<title>Not found</title>', 'removed'],
            'gone' => [410, '<title>Gone</title>', 'removed'],
            'storefront' => [200, '<title>Online shop</title><meta property="og:image" content="https://example.com/logo.png">', 'incomplete'],
        ]);

        it('finishes terminal outcomes without retrying or importing page metadata', function (FetchOutcome $outcome, ?int $status) {
            $gift = Gift::factory()->pending()->for(User::factory())->create(['title' => 'My wish']);
            $images = Mockery::mock(ProcessGiftImageAction::class);
            $images->shouldNotReceive('fromUrl');
            $images->shouldReceive('dispatchCompletedEvent')->once();
            $product = new ProductInfo(name: 'Wall title', outcome: $outcome, httpStatus: $status);

            (new FetchGiftDetailsAction($gift))->setFetcher(mockFetcher($product))->handle($images);

            expect($gift->fresh())->fetch_status->toBe('failed')
                ->title->toBe('My wish')->fetch_attempts->toBe(1)
                ->fetched_at->not->toBeNull()
                ->fetch_error->toEqual(['summary' => 'Product fetch: '.$outcome->value, 'outcome' => $outcome->value, 'status_code' => $status]);
        })->with([
            'forbidden' => [FetchOutcome::Blocked, 403],
            'rate limited' => [FetchOutcome::Blocked, 429],
            'challenge' => [FetchOutcome::Blocked, 200],
            'consent' => [FetchOutcome::Consent, 200],
            'removed' => [FetchOutcome::Removed, 404],
            'gone' => [FetchOutcome::Removed, 410],
            'incomplete' => [FetchOutcome::Incomplete, 200],
        ]);

        it('retries error outcomes including transport timeouts', function (?int $status, bool $timedOut) {
            $gift = Gift::factory()->pending()->for(User::factory())->create();
            $images = Mockery::mock(ProcessGiftImageAction::class);
            $images->shouldNotReceive('dispatchCompletedEvent');
            $product = new ProductInfo(outcome: FetchOutcome::Error, httpStatus: $status,
                attempts: [new TransportAttempt('http', $status, $gift->url, 15, $timedOut, 'http_error')]);

            expect(fn () => (new FetchGiftDetailsAction($gift))->setFetcher(mockFetcher($product))->handle($images))
                ->toThrow(RuntimeException::class, 'Product fetch: error');
            expect($gift->fresh())->fetch_status->toBe('fetching')->fetch_attempts->toBe(1)
                ->fetch_error->toHaveKey('outcome', 'error');
        })->with([[500, false], [503, false], [null, true], [null, false]]);

        it('marks an exhausted error as failed and emits the completion event', function () {
            $gift = Gift::factory()->fetching()->for(User::factory())->create([
                'fetch_error' => ['summary' => 'Product fetch: error', 'outcome' => 'error'],
            ]);
            SlackAlert::shouldReceive('message')->once();

            (new FetchGiftDetailsAction($gift))->failed(new RuntimeException('Product fetch: error'));

            expect($gift->fresh())->fetch_status->toBe('failed')->fetched_at->not->toBeNull();
            Event::assertDispatched(GiftFetchCompleted::class);
        });

        it('records unexpected failures without leaking exception URLs', function () {
            $gift = Gift::factory()->pending()->for(User::factory())->create();
            $exception = new RuntimeException('Failed https://example.com?secret=value#private');
            Log::spy();

            expect(fn () => (new FetchGiftDetailsAction($gift))->setFetcher(mockFetcherThrows($exception))->handle(mockImageAction()))
                ->toThrow(RuntimeException::class);

            expect($gift->fresh())->fetch_status->toBe('fetching')
                ->fetch_error->toBe(['summary' => 'Unexpected: RuntimeException']);
            Log::shouldHaveReceived('info')->with('Gift fetch attempt', Mockery::on(fn (array $data): bool => $data['outcome'] === 'error' && ! str_contains(json_encode($data), 'secret')))->once();
        });

        it('accepts usable data from a timed out browser without retrying and logs redacted transport diagnostics', function () {
            $gift = Gift::factory()->pending()->for(User::factory())->create(['url' => 'https://example.com/product?token=input#private']);
            $product = new ProductInfo(name: 'Product', priceInCents: 0, imageUrl: 'https://example.com/image.jpg',
                outcome: FetchOutcome::Success, finalUrl: 'https://user:password@example.com/final?token=final#private', httpStatus: 200,
                attempts: [
                    new TransportAttempt('http', 403, 'https://example.com/product?token=http#private', 12),
                    new TransportAttempt('browser', 200, 'https://example.com/final?token=browser#private', 15000, true, 'navigation_timeout'),
                ]);
            Log::spy();

            (new FetchGiftDetailsAction($gift))->setFetcher(mockFetcher($product))->handle(mockImageAction());

            expect($gift->fresh())->fetch_status->toBe('completed')->price_in_cents->toBe(0);
            Log::shouldHaveReceived('info')->with('Gift fetch attempt', Mockery::on(fn (array $data): bool => $data['outcome'] === 'success' && $data['http_status'] === 200
                && $data['final_url'] === 'https://example.com/final'
                && $data['attempts'] === [
                    ['transport' => 'http', 'statusCode' => 403, 'finalUrl' => 'https://example.com/product', 'durationMs' => 12.0, 'timedOut' => false, 'error' => null],
                    ['transport' => 'browser', 'statusCode' => 200, 'finalUrl' => 'https://example.com/final', 'durationMs' => 15000.0, 'timedOut' => true, 'error' => 'navigation_timeout'],
                ] && ! str_contains(json_encode($data), 'token') && ! str_contains(json_encode($data), 'password')))->once();
        });

        it('keeps safeguards for titles the package does not recognize', function (string $title) {
            $gift = Gift::factory()->pending()->for(User::factory())->create(['title' => 'My wish']);
            (new FetchGiftDetailsAction($gift))->setFetcher(mockFetcher(new ProductInfo(name: $title, outcome: FetchOutcome::Success)))
                ->handle(mockImageAction());
            expect($gift->fresh())->title->toBe('My wish')->fetch_status->toBe('failed');
        })->with(['Just a moment', "Women's & Men's Clothing, Shop Online Fashion | SHEIN"]);
    });

    describe('Amazon browser policy', function () {
        it('preserves the configured proxy and the single 403 browser fallback', function () {
            config(['services.gift_fetcher.proxy' => 'http://proxy.example.test:3128']);
            $gift = Gift::factory()->pending()->for(User::factory())->create(['url' => 'https://www.bol.com/product']);
            $action = new class($gift) extends FetchGiftDetailsAction
            {
                public function configuredFetcher(): ProductInfoFetcher
                {
                    return parent::createFetcher();
                }
            };
            $http = new MockHandler([new Response(403, [], '<title>Access denied</title>')]);
            $browser = Mockery::mock(HeadlessFetcher::class);
            $browser->shouldReceive('setTimeout', 'setUserAgent', 'setHeaders')->andReturnSelf();
            $browser->shouldReceive('setProxy')->with('http://proxy.example.test:3128')->once()->andReturnSelf();
            $browser->shouldReceive('fetch')->with($gift->url)->once()
                ->andReturn(new HeadlessFetchResult('<title>Access denied</title>', 403, $gift->url));
            $fetcher = $action->configuredFetcher()->setClient(new Client(['handler' => HandlerStack::create($http)]))->setHeadlessFetcher($browser);

            $action->setFetcher($fetcher)->handle(mockImageAction());

            expect($http->getLastOptions()['proxy'])->toBe('http://proxy.example.test:3128');
            expect($http->count())->toBe(0);
            expect($gift->fresh())->fetch_status->toBe('failed')->fetch_error->toHaveKey('outcome', 'blocked');
        });

        it('makes one direct browser navigation after an HTTP challenge and never loops', function (string $domain, string $browserHtml, string $expectedStatus) {
            $url = 'https://www.'.$domain.'/dp/EXAMPLE?tag=private#fragment';
            $gift = Gift::factory()->pending()->for(User::factory())->create(['url' => $url]);
            $http = new MockHandler([new Response(200, [], '<title>Robot Check</title>')]);
            $browser = Mockery::mock(HeadlessFetcher::class);
            $browser->shouldReceive('setTimeout', 'setUserAgent', 'setHeaders')->andReturnSelf();
            $browser->shouldReceive('fetch')->with($url)->once()->andReturn(new HeadlessFetchResult($browserHtml, 200, $url));
            $fetcher = (new ProductInfoFetcher($url))->setClient(new Client(['handler' => HandlerStack::create($http)]))->setHeadlessFetcher($browser);
            Log::spy();

            (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle(mockImageAction());

            expect($http->count())->toBe(0);
            expect($gift->fresh()->fetch_status)->toBe($expectedStatus);
            Log::shouldHaveReceived('info')->with('Gift fetch attempt', Mockery::on(fn (array $data): bool => array_column($data['attempts'], 'transport') === ['http', 'browser']
                && $data['attempts'][0]['finalUrl'] === 'https://www.'.$domain.'/dp/EXAMPLE'))->once();
        })->with(['amazon.com.be', 'amazon.nl', 'amazon.de', 'amazon.fr'])->with([
            'product' => ['<script type="application/ld+json">{"@type":"Product","name":"Product","image":"https://example.com/image.jpg","offers":{"price":"19.99","priceCurrency":"EUR"}}</script>', 'completed'],
            'challenge' => ['<title>Robot Check</title>', 'failed'],
            'consent' => ['<h1>Before you continue</h1>', 'failed'],
        ]);

        it('does not start a browser for consent, removed, rate limits, non Amazon hosts or an existing browser attempt', function (string $host, FetchOutcome $outcome, int $status, string $transport) {
            $gift = Gift::factory()->pending()->for(User::factory())->create(['url' => 'https://'.$host.'/product']);
            $product = new ProductInfo(outcome: $outcome, httpStatus: $status, attempts: [new TransportAttempt($transport, $status, $gift->url, 10)]);
            $fetcher = mockFetcher($product);
            $fetcher->shouldNotReceive('preferHeadless');

            (new FetchGiftDetailsAction($gift))->setFetcher($fetcher)->handle(mockImageAction());
            expect($gift->fresh()->fetch_status)->toBe('failed');
        })->with([
            ['www.amazon.com.be', FetchOutcome::Consent, 200, 'http'],
            ['www.amazon.com.be', FetchOutcome::Removed, 404, 'http'],
            ['www.amazon.com.be', FetchOutcome::Blocked, 429, 'http'],
            ['www.amazon.com.be', FetchOutcome::Blocked, 200, 'browser'],
            ['www.bol.com', FetchOutcome::Blocked, 200, 'http'],
            ['www.amazon.com.be.example.com', FetchOutcome::Blocked, 200, 'http'],
        ]);
    });
});
