<?php

namespace Database\Seeders;

use App\Actions\CreateListAction;
use App\Models\User;
use Illuminate\Database\Seeder;

class GiftListSeeder extends Seeder
{
    public function run(): void
    {
        $mattias = User::where('email', 'm@ttias.be')->first();
        $john = User::where('email', 'john@doe.tld')->first();

        $action = new CreateListAction;

        // Mattias: default list + extra list (to test multi-list mode)
        $action->execute($mattias, 'My Wishlist', isDefault: true);
        $action->execute($mattias, 'Birthday Ideas');

        // John: just the default list (single-list mode)
        $action->execute($john, 'My Wishlist', isDefault: true);
    }
}
