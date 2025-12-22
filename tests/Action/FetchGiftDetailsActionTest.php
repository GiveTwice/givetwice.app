<?php

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use App\Models\User;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Event::fake([GiftFetchCompleted::class]);
});

function createGuzzleMockClient(array $responses): Client
{
    $mock = new MockHandler($responses);
    $handlerStack = HandlerStack::create($mock);

    return new Client(['handler' => $handlerStack]);
}

function createTestAction(Gift $gift, Client $mockClient): FetchGiftDetailsAction
{
    $action = new class($gift) extends FetchGiftDetailsAction
    {
        public ?Client $mockClient = null;

        protected function createFetcher(): ProductInfoFetcher
        {
            return parent::createFetcher()->setClient($this->mockClient);
        }
    };
    $action->mockClient = $mockClient;

    return $action;
}

function createImageActionMock(bool $fromUrlSuccess = false): ProcessGiftImageAction
{
    $imageAction = Mockery::mock(ProcessGiftImageAction::class);
    $imageAction->shouldReceive('fromUrl')->andReturn($fromUrlSuccess);
    $imageAction->shouldReceive('dispatchCompletedEvent');

    return $imageAction;
}

function sampleProductHtml(array $overrides = []): string
{
    $data = array_merge([
        'name' => 'Test Product',
        'description' => 'A wonderful test product',
        'price' => '29.99',
        'currency' => 'EUR',
        'image' => 'https://example.com/image.jpg',
        'rating' => null,
        'reviewCount' => null,
    ], $overrides);

    $ratingJson = '';
    if ($data['rating'] !== null) {
        $ratingJson = sprintf(
            '"aggregateRating": {"@type": "AggregateRating", "ratingValue": "%s", "reviewCount": "%s"},',
            $data['rating'],
            $data['reviewCount'] ?? 0
        );
    }

    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>{$data['name']}</title>
        <meta property="og:title" content="{$data['name']}">
        <meta property="og:description" content="{$data['description']}">
        <meta property="og:image" content="{$data['image']}">
    </head>
    <body>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Product",
            "name": "{$data['name']}",
            "description": "{$data['description']}",
            "image": "{$data['image']}",
            {$ratingJson}
            "offers": {
                "@type": "Offer",
                "price": "{$data['price']}",
                "priceCurrency": "{$data['currency']}"
            }
        }
        </script>
    </body>
    </html>
    HTML;
}

describe('FetchGiftDetailsAction', function () {

    describe('job configuration', function () {
        it('is configured with 4 retry attempts', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $action = new FetchGiftDetailsAction($gift);

            expect($action->tries)->toBe(4);
        });

        it('is configured with exponential backoff', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $action = new FetchGiftDetailsAction($gift);

            expect($action->backoff)->toBe([5, 30, 60]);
        });

        it('runs on the fetch queue', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            FetchGiftDetailsAction::dispatch($gift);

            Queue::assertPushedOn('fetch', FetchGiftDetailsAction::class);
        });
    });

    describe('happy path', function () {
        it('sets fetch_status to fetching before making request', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'fetch_status' => 'pending',
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml()),
            ]);

            $statusDuringFetch = null;
            $action = new class($gift) extends FetchGiftDetailsAction
            {
                public ?Client $mockClient = null;

                public ?string $statusDuringFetch = null;

                protected function createFetcher(): ProductInfoFetcher
                {
                    $this->statusDuringFetch = $this->gift->fresh()->fetch_status;

                    return parent::createFetcher()->setClient($this->mockClient);
                }
            };
            $action->mockClient = $mockClient;

            $action->handle(createImageActionMock());

            expect($action->statusDuringFetch)->toBe('fetching');
        });

        it('updates gift with fetched product details', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'title' => 'Original Title',
                'description' => null,
                'price_in_cents' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml([
                    'name' => 'Fetched Product Name',
                    'description' => 'Fetched description',
                    'price' => '49.99',
                    'currency' => 'USD',
                ])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            $gift->refresh();
            expect($gift->title)->toBe('Fetched Product Name');
            expect($gift->description)->toBe('Fetched description');
            expect($gift->price_in_cents)->toBe(4999);
            expect($gift->currency)->toBe('USD');
        });

        it('updates rating and review count when available', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'rating' => null,
                'review_count' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml([
                    'rating' => '4.5',
                    'reviewCount' => '127',
                ])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            $gift->refresh();
            expect((float) $gift->rating)->toBe(4.5);
            expect($gift->review_count)->toBe(127);
        });

        it('sets fetch_status to completed after successful fetch', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml()),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->fetch_status)->toBe('completed');
        });

        it('sets fetched_at timestamp after successful fetch', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'fetched_at' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml()),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->fetched_at)->not->toBeNull();
        });

        it('dispatches completed event after successful fetch', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml()),
            ]);

            $action = createTestAction($gift, $mockClient);

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')->andReturn(false);
            $imageAction->shouldReceive('dispatchCompletedEvent')->once();

            $action->handle($imageAction);
        });
    });

    describe('data handling', function () {
        it('truncates long descriptions to 1497 characters', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $longDescription = str_repeat('a', 2000);
            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml(['description' => $longDescription])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect(strlen($gift->fresh()->description))->toBe(1500); // 1497 + '...'
        });

        it('keeps original title if fetched title is empty', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'title' => 'My Original Title',
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml(['name' => ''])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->title)->toBe('My Original Title');
        });

        it('keeps original description if fetched description is null', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'description' => 'My original description',
            ]);

            $html = <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head><title>Test</title></head>
            <body>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "name": "Test Product"
                }
                </script>
            </body>
            </html>
            HTML;

            $mockClient = createGuzzleMockClient([
                new Response(200, [], $html),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->description)->toBe('My original description');
        });

        it('keeps original currency if fetched currency is null', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'currency' => 'EUR',
            ]);

            $html = <<<'HTML'
            <!DOCTYPE html>
            <html>
            <head><title>Test</title></head>
            <body>
                <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Product",
                    "name": "Test Product",
                    "offers": {
                        "@type": "Offer",
                        "price": "19.99"
                    }
                }
                </script>
            </body>
            </html>
            HTML;

            $mockClient = createGuzzleMockClient([
                new Response(200, [], $html),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->currency)->toBe('EUR');
        });
    });

    describe('image handling', function () {
        it('tries to add image from fetched URLs', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml(['image' => 'https://example.com/product.jpg'])),
            ]);

            $action = createTestAction($gift, $mockClient);

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')
                ->withArgs(fn ($g, $url) => $g->id === $gift->id && $url === 'https://example.com/product.jpg')
                ->once()
                ->andReturn(true);
            $imageAction->shouldReceive('dispatchCompletedEvent');

            $action->handle($imageAction);
        });

        it('updates original_image_url when image download succeeds', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'original_image_url' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml(['image' => 'https://example.com/success.jpg'])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock(fromUrlSuccess: true));

            expect($gift->fresh()->original_image_url)->toBe('https://example.com/success.jpg');
        });

        it('does not update original_image_url when image download fails', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'original_image_url' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(200, [], sampleProductHtml(['image' => 'https://example.com/fail.jpg'])),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock(fromUrlSuccess: false));

            expect($gift->fresh()->original_image_url)->toBeNull();
        });
    });

    describe('error handling', function () {
        it('sets fetch_status to failed on client error (4xx)', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(404, [], 'Not Found'),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->fetch_status)->toBe('failed');
        });

        it('sets fetch_status to failed on server error (5xx)', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(500, [], 'Internal Server Error'),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->fetch_status)->toBe('failed');
        });

        it('sets fetched_at even when fetch fails', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'fetched_at' => null,
            ]);

            $mockClient = createGuzzleMockClient([
                new Response(404, [], 'Not Found'),
            ]);

            $action = createTestAction($gift, $mockClient);
            $action->handle(createImageActionMock());

            expect($gift->fresh()->fetched_at)->not->toBeNull();
        });

        it('dispatches completed event even when fetch fails', function () {
            $user = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $user->id]);

            $mockClient = createGuzzleMockClient([
                new Response(404, [], 'Not Found'),
            ]);

            $action = createTestAction($gift, $mockClient);

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('dispatchCompletedEvent')->once();

            $action->handle($imageAction);
        });
    });

});
