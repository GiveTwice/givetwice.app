<?php

namespace Database\Factories;

use App\Models\GiftExchange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftExchange>
 */
class GiftExchangeFactory extends Factory
{
    protected $model = GiftExchange::class;

    public function definition(): array
    {
        return [
            'organizer_id' => User::factory(),
            'name' => fake()->words(3, true),
            'budget_amount' => fake()->randomElement([1500, 2000, 2500, 5000]),
            'budget_currency' => 'EUR',
            'event_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'status' => 'draft',
            'locale' => 'nl',
        ];
    }

    public function drawn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'drawn',
            'draw_completed_at' => now(),
        ]);
    }
}
