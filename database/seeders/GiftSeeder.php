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

        $mattiasDefaultList = GiftList::where('user_id', $mattias->id)->where('is_default', true)->first();
        $mattiasBirthdayList = GiftList::where('user_id', $mattias->id)->where('name', 'Birthday Ideas')->first();
        $johnDefaultList = GiftList::where('user_id', $john->id)->where('is_default', true)->first();

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
    }
}
