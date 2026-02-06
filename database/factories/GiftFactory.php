<?php

namespace Database\Factories;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gift>
 */
class GiftFactory extends Factory
{
    protected $model = Gift::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => fake()->url(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price_in_cents' => fake()->numberBetween(100, 50000),
            'currency' => fake()->randomElement(['EUR', 'USD', 'GBP']),
            'original_image_url' => null,
            'fetch_status' => 'completed',
            'fetched_at' => now(),
            'rating' => null,
            'review_count' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'fetch_status' => 'pending',
            'fetched_at' => null,
            'title' => null,
            'description' => null,
            'price_in_cents' => null,
        ]);
    }

    public function fetching(): static
    {
        return $this->state(fn (array $attributes) => [
            'fetch_status' => 'fetching',
            'fetched_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'fetch_status' => 'failed',
            'fetch_error' => ['summary' => 'HTTP 404: Not Found', 'status_code' => 404],
            'fetch_attempts' => 1,
            'fetched_at' => now(),
        ]);
    }

    public function withFallbackImage(string $url = 'https://example.com/image.jpg'): static
    {
        return $this->state(fn (array $attributes) => [
            'original_image_url' => $url,
        ]);
    }
}
