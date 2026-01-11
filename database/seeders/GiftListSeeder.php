<?php

namespace Database\Seeders;

use App\Models\GiftList;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiftListSeeder extends Seeder
{
    public function run(): void
    {
        $mattias = User::where('email', 'm@ttias.be')->first();
        $john = User::where('email', 'john@doe.tld')->first();

        // Mattias: default list + extra list (to test multi-list mode)
        $mattiasDefault = GiftList::create([
            'creator_id' => $mattias->id,
            'name' => 'My Wishlist',
            'is_default' => true,
        ]);
        $mattiasDefault->users()->attach($mattias->id, ['joined_at' => now()]);

        $mattiasBirthday = GiftList::create([
            'creator_id' => $mattias->id,
            'name' => 'Birthday Ideas',
            'is_default' => false,
        ]);
        $mattiasBirthday->users()->attach($mattias->id, ['joined_at' => now()]);

        // John: just the default list (single-list mode)
        $johnDefault = GiftList::create([
            'creator_id' => $john->id,
            'name' => 'My Wishlist',
            'is_default' => true,
        ]);
        $johnDefault->users()->attach($john->id, ['joined_at' => now()]);
    }
}
