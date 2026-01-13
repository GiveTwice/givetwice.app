<?php

namespace Database\Factories;

use App\Models\GiftList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftList>
 */
class GiftListFactory extends Factory
{
    protected $model = GiftList::class;

    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
