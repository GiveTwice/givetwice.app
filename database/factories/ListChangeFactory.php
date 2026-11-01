<?php

namespace Database\Factories;

use App\Models\GiftList;
use App\Models\ListChange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListChange>
 */
class ListChangeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'list_id' => GiftList::factory(),
            'actor_id' => User::factory(),
            'gift_id' => 1,
            'kind' => 'edited',
            'title' => fake()->words(3, true),
        ];
    }
}
