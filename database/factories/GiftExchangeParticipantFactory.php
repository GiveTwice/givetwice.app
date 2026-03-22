<?php

namespace Database\Factories;

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftExchangeParticipant>
 */
class GiftExchangeParticipantFactory extends Factory
{
    protected $model = GiftExchangeParticipant::class;

    public function definition(): array
    {
        return [
            'exchange_id' => GiftExchange::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(64),
        ];
    }

    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'viewed_at' => now(),
        ]);
    }

    public function joined(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => now(),
        ]);
    }
}
