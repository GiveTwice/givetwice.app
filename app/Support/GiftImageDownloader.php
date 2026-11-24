<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Downloaders\Downloader;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;
use Throwable;

/**
 * Downloads remote images through the same proxy and browser user agent as the product page fetch.
 *
 * Shops behind Cloudflare, like IKEA, answer 403 to a request from a server IP.
 * The product page got through the proxy, so its image has to come through it too.
 */
class GiftImageDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        try {
            Http::withUserAgent(config('services.gift_fetcher.user_agent'))
                ->withOptions(array_filter([
                    'proxy' => config('services.gift_fetcher.proxy'),
                    'verify' => config('media-library.media_downloader_ssl'),
                ], fn (mixed $value): bool => $value !== null))
                ->connectTimeout(10)
                ->timeout(15)
                ->sink($temporaryFile)
                ->get($url)
                ->throw();
        } catch (Throwable) {
            @unlink($temporaryFile);

            throw UnreachableUrl::create($url);
        }

        return $temporaryFile;
    }
}
