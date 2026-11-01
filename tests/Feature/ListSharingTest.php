<?php

use App\Helpers\ListShareHelper;
use App\Models\GiftList;

it('shares the localized public list URL with safe WhatsApp encoding', function (string $locale) {
    app()->setLocale($locale);
    $list = GiftList::factory()->create(['name' => 'Noah & Emma']);
    $url = ListShareHelper::whatsappUrl($list);
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    expect(parse_url($url, PHP_URL_HOST))->toBe('wa.me')
        ->and($query['text'])->toEndWith($list->getPublicUrl($locale));
})->with(['nl', 'fr', 'en']);
