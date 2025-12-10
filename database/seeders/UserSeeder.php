<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Mattias',
            'email' => 'm@ttias.be',
            'password' => 'localdevelopment',
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'John',
            'email' => 'john@doe.tld',
            'password' => 'localdevelopment',
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }
}
