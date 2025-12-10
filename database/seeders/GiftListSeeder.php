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

        // Mattias: just the default list
        GiftList::create([
            'user_id' => $mattias->id,
            'name' => 'My Wishlist',
            'is_default' => true,
            'is_public' => true,
            'filter_type' => 'manual',
        ]);

        // John: default list + Secret Santa list
        GiftList::create([
            'user_id' => $john->id,
            'name' => 'My Wishlist',
            'is_default' => true,
            'is_public' => true,
            'filter_type' => 'manual',
        ]);

        GiftList::create([
            'user_id' => $john->id,
            'name' => 'Secret Santa List',
            'is_default' => false,
            'is_public' => true,
            'filter_type' => 'manual',
        ]);
    }
}
