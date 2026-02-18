<?php

namespace Database\Seeders;

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    public function run(): void
    {
        $mattias = User::where('email', 'm@ttias.be')->first();
        $john = User::where('email', 'john@doe.tld')->first();

        $mattiasDefaultList = GiftList::where('creator_id', $mattias->id)->where('is_default', true)->first();
        $mattiasBirthdayList = GiftList::where('creator_id', $mattias->id)->where('name', 'Birthday Ideas')->first();
        $johnDefaultList = GiftList::where('creator_id', $john->id)->where('is_default', true)->first();

        $gifts = [
            [
                'url' => 'https://www.asadventure.com/nl/p/gripgrab-handschoenen-ride-ii-windproof-winter-D13JAB0130.html?colour=11616',
                'title' => 'Wintergloves',
            ],
            [
                'url' => 'https://www.asadventure.com/nl/p/vaude-bike-windproof-cap-iii-9253D32009.html?colour=11660',
                'title' => 'Helmet',
            ],
            [
                'url' => 'https://www.dierendonck.be/collections/cadeaubonnen/products/cadeaubon-webshop',
                'title' => 'Gift voucher for a butcher store',
            ],
            [
                'url' => 'https://www.bbqexperiencecenter.be/nl/rib-rack-rvs-kamado-joe/',
                'title' => 'BBQ rib rack',
            ],
            [
                'url' => 'https://www.amazon.com.be/-/nl/mini-drone-volwassenen-volger-drone-verwijdering-onderwerptracking/dp/B07FTRYVT5/?th=1',
                'title' => 'DJI Neo Drone',
            ],
            [
                'url' => 'https://www.dille-kamille.be/nl/ovenwant-bio-katoen-oud-roze-gemeleerd-00017871.html',
                'title' => 'Oven mitts',
            ],
            [
                'url' => 'https://www.bol.com/be/nl/p/metio-kunstschort-voor-volwassenen-schilderschort-met-lange-mouwen-voor-schilderen-creatieve-werkzaamheden-katoen-creme/9300000225709438/',
                'title' => 'Protective skirt for painting',
            ],
            [
                'url' => 'https://www.oilvinegar.be/products/novello-eerste-oogst-extra-vierge-olijfolie-2022-2023-500ml',
                'title' => 'Bottle of olive oil',
            ],
        ];

        // Create gifts for Mattias - split between default and birthday list
        $sortOrder = 1;
        foreach (array_slice($gifts, 0, 3) as $giftData) {
            $gift = Gift::withoutEvents(function () use ($mattias, $giftData) {
                return Gift::create([
                    'user_id' => $mattias->id,
                    'url' => $giftData['url'],
                    'title' => $giftData['title'],
                    'fetch_status' => 'pending',
                ]);
            });

            $mattiasDefaultList->gifts()->attach($gift->id, [
                'sort_order' => $sortOrder++,
                'added_at' => now(),
            ]);
        }

        // Add some gifts to Mattias's Birthday Ideas list
        $sortOrder = 1;
        foreach (array_slice($gifts, 3, 2) as $giftData) {
            $gift = Gift::withoutEvents(function () use ($mattias, $giftData) {
                return Gift::create([
                    'user_id' => $mattias->id,
                    'url' => $giftData['url'],
                    'title' => $giftData['title'],
                    'fetch_status' => 'pending',
                ]);
            });

            $mattiasBirthdayList->gifts()->attach($gift->id, [
                'sort_order' => $sortOrder++,
                'added_at' => now(),
            ]);
        }

        // Create gifts for John (single list mode)
        $sortOrder = 1;
        foreach (array_slice($gifts, 5, 3) as $giftData) {
            $gift = Gift::withoutEvents(function () use ($john, $giftData) {
                return Gift::create([
                    'user_id' => $john->id,
                    'url' => $giftData['url'],
                    'title' => $giftData['title'],
                    'fetch_status' => 'pending',
                ]);
            });

            $johnDefaultList->gifts()->attach($gift->id, [
                'sort_order' => $sortOrder++,
                'added_at' => now(),
            ]);
        }

        // Failed gift: HTTP 403 with headers and body (e.g. bot protection)
        $failedHttp = Gift::withoutEvents(function () use ($mattias) {
            return Gift::create([
                'user_id' => $mattias->id,
                'url' => 'https://www.nike.com/be/t/air-max-90-shoes-kRsBnD/CN8490-001',
                'title' => null,
                'fetch_status' => 'failed',
                'fetch_attempts' => 4,
                'fetched_at' => now()->subHours(2),
                'fetch_error' => [
                    'summary' => 'HTTP 403: Forbidden',
                    'status_code' => 403,
                    'headers' => [
                        'Server' => ['AkamaiGHost'],
                        'Content-Type' => ['text/html'],
                        'Cache-Control' => ['max-age=0, no-cache, no-store'],
                        'X-Akamai-Transformed' => ['9 - 0 pmb=mNONE,1'],
                        'Date' => ['Tue, 18 Feb 2026 10:23:41 GMT'],
                    ],
                    'body' => '<html><head><title>Access Denied</title></head><body><h1>Access Denied</h1><p>You don\'t have permission to access "https://www.nike.com/be/t/air-max-90-shoes-kRsBnD/CN8490-001" on this server.</p><p>Reference #18.e4c83117.1708251821.1a2b3c4d</p></body></html>',
                ],
            ]);
        });

        $mattiasDefaultList->gifts()->attach($failedHttp->id, [
            'sort_order' => $sortOrder + 1,
            'added_at' => now(),
        ]);

        // Failed gift: browser timeout with debug data
        $failedBrowser = Gift::withoutEvents(function () use ($mattias) {
            return Gift::create([
                'user_id' => $mattias->id,
                'url' => 'https://www.zalando.be/nike-sportswear-air-force-1-sneakers-ni112o0bt-a11.html',
                'title' => null,
                'fetch_status' => 'failed',
                'fetch_attempts' => 4,
                'fetched_at' => now()->subMinutes(45),
                'fetch_error' => [
                    'summary' => 'Browser error: Navigation timeout of 15000ms exceeded waiting for load event',
                    'status_code' => 200,
                    'final_url' => 'https://www.zalando.be/nike-sportswear-air-force-1-sneakers-ni112o0bt-a11.html?_rfl=nl',
                    'headers' => [
                        'Content-Type' => ['text/html; charset=utf-8'],
                        'X-Zalando-Request-Uri' => ['/nike-sportswear-air-force-1-sneakers-ni112o0bt-a11.html'],
                        'X-Request-Id' => ['a1b2c3d4-e5f6-7890-abcd-ef1234567890'],
                    ],
                    'body' => '<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><title>Nike Sportswear AIR FORCE 1 - Sneakers - white</title><script>window.__INITIAL_STATE__={"product":{"sku":"NI112O0BT-A11","name":"AIR FORCE 1 \'07","brand":"Nike Sportswear","price":{"original":1… [truncated]',
                ],
            ]);
        });

        $mattiasBirthdayList->gifts()->attach($failedBrowser->id, [
            'sort_order' => $sortOrder + 2,
            'added_at' => now(),
        ]);
    }
}
