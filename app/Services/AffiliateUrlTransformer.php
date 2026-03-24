<?php

namespace App\Services;

class AffiliateUrlTransformer
{
    public function transform(string $url, ?int $giftId = null, ?int $listId = null): string
    {
        if (! config('services.skimlinks.enabled')) {
            return $url;
        }

        $publisherId = config('services.skimlinks.publisher_id');

        if (! $publisherId) {
            return $url;
        }

        $xcust = collect([
            $giftId ? "g{$giftId}" : null,
            $listId ? "l{$listId}" : null,
        ])->filter()->implode('_');

        $params = [
            'id' => $publisherId,
            'url' => $url,
        ];

        if ($xcust) {
            $params['xcust'] = $xcust;
        }

        return 'https://go.skimresources.com/?'.http_build_query($params);
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.skimlinks.enabled')
            && (bool) config('services.skimlinks.publisher_id');
    }
}
