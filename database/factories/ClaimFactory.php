<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Claim>
 */
class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'gift_id' => Gift::factory(),
            'user_id' => User::factory(),
            'claimer_email' => null,
            'claimer_name' => null,
            'confirmation_token' => null,
            'confirmed_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'claimer_email' => fake()->email(),
            'claimer_name' => fake()->name(),
            'confirmation_token' => Str::random(64),
            'confirmed_at' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => null,
            'confirmation_token' => Str::random(64),
        ]);
    }
}
