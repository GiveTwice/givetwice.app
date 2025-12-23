<?php

use App\Actions\FetchGiftDetailsAction;
use App\Actions\ProcessGiftImageAction;
use App\Events\GiftFetchCompleted;
use App\Models\Gift;
use App\Models\User;
use GiveTwice\ProductInfoFetcher\DataTransferObjects\ProductInfo;
use GiveTwice\ProductInfoFetcher\ProductInfoFetcher;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Event::fake([GiftFetchCompleted::class]);
});

function mockFetcher(ProductInfo $productInfo): ProductInfoFetcher
{
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchAndParse')->andReturn($productInfo);

    return $fetcher;
}

function mockFetcherThrows(Throwable $exception): ProductInfoFetcher
{
    $fetcher = Mockery::mock(ProductInfoFetcher::class);
    $fetcher->shouldReceive('fetchAndParse')->andThrow($exception);

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
            $fetcher->shouldReceive('fetchAndParse')->andReturnUsing(function () use ($gift, &$statusDuringFetch) {
                $statusDuringFetch = $gift->fresh()->fetch_status;

                return new ProductInfo(name: 'Test');
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

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(
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
                ->setFetcher(mockFetcher(new ProductInfo(name: 'Test')))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetch_status)->toBe('completed');
        });

        it('sets fetched_at timestamp', function () {
            $gift = Gift::factory()->for(User::factory())->create(['fetched_at' => null]);

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(name: 'Test')))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetched_at)->not->toBeNull();
        });

        it('dispatches completed event', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('fromUrl')->andReturn(false);
            $imageAction->shouldReceive('dispatchCompletedEvent')->once();

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcher(new ProductInfo(name: 'Test')))
                ->handle($imageAction);
        });
    });

    describe('data handling', function () {
        it('truncates long descriptions to 1500 characters', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(name: ''));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->title)->toBe('My Original Title');
        });

        it('keeps original description if fetched description is null', function () {
            $gift = Gift::factory()->for(User::factory())->create(['description' => 'My original description']);

            $fetcher = mockFetcher(new ProductInfo(name: 'Test', description: null));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->description)->toBe('My original description');
        });

        it('keeps original currency if fetched currency is null', function () {
            $gift = Gift::factory()->for(User::factory())->create(['currency' => 'EUR']);

            $fetcher = mockFetcher(new ProductInfo(name: 'Test', priceInCents: 1999, priceCurrency: null));

            (new FetchGiftDetailsAction($gift))
                ->setFetcher($fetcher)
                ->handle(mockImageAction());

            expect($gift->fresh()->currency)->toBe('EUR');
        });
    });

    describe('image handling', function () {
        it('tries to add image from fetched URLs', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(
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

            $fetcher = mockFetcher(new ProductInfo(
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

    describe('error handling', function () {
        it('sets fetch_status to failed on client error (4xx)', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $exception = new ClientException(
                'Not Found',
                new Request('GET', 'https://example.com'),
                new Response(404)
            );

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcherThrows($exception))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetch_status)->toBe('failed');
        });

        it('sets fetch_status to failed on server error (5xx)', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $exception = new ServerException(
                'Internal Server Error',
                new Request('GET', 'https://example.com'),
                new Response(500)
            );

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcherThrows($exception))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetch_status)->toBe('failed');
        });

        it('sets fetched_at even when fetch fails', function () {
            $gift = Gift::factory()->for(User::factory())->create(['fetched_at' => null]);

            $exception = new ClientException(
                'Not Found',
                new Request('GET', 'https://example.com'),
                new Response(404)
            );

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcherThrows($exception))
                ->handle(mockImageAction());

            expect($gift->fresh()->fetched_at)->not->toBeNull();
        });

        it('dispatches completed event even when fetch fails', function () {
            $gift = Gift::factory()->for(User::factory())->create();

            $exception = new ClientException(
                'Not Found',
                new Request('GET', 'https://example.com'),
                new Response(404)
            );

            $imageAction = Mockery::mock(ProcessGiftImageAction::class);
            $imageAction->shouldReceive('dispatchCompletedEvent')->once();

            (new FetchGiftDetailsAction($gift))
                ->setFetcher(mockFetcherThrows($exception))
                ->handle($imageAction);
        });
    });

});
