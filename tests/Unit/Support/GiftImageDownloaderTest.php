<?php

use App\Support\GiftImageDownloader;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

it('downloads the image with the same browser user agent as the product fetch', function () {
    Http::fake(['shop.test/*' => Http::response('image-bytes', 200, ['Content-Type' => 'image/jpeg'])]);

    $file = (new GiftImageDownloader)->getTempFile('https://shop.test/product.jpg');

    expect(file_get_contents($file))->toBe('image-bytes');
    Http::assertSent(fn (Request $request): bool => $request->header('User-Agent')[0] === config('services.gift_fetcher.user_agent'));

    unlink($file);
});

it('reports a blocked image as unreachable', function () {
    Http::fake(['shop.test/*' => Http::response('Forbidden', 403)]);

    (new GiftImageDownloader)->getTempFile('https://shop.test/product.jpg');
})->throws(UnreachableUrl::class);
