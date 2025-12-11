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
        GiftList::create([
            'user_id' => $mattias->id,
            'name' => 'My Wishlist',
            'is_default' => true,
        ]);

        GiftList::create([
            'user_id' => $mattias->id,
            'name' => 'Birthday Ideas',
            'is_default' => false,
        ]);

        // John: just the default list (single-list mode)
        GiftList::create([
            'user_id' => $john->id,
            'name' => 'My Wishlist',
            'is_default' => true,
        ]);
    }
}
