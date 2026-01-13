<?php

namespace Database\Factories;

use App\Models\GiftList;
use App\Models\ListInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ListInvitation>
 */
class ListInvitationFactory extends Factory
{
    protected $model = ListInvitation::class;

    public function definition(): array
    {
        return [
            'list_id' => GiftList::factory(),
            'inviter_id' => User::factory(),
            'invitee_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
        ];
    }

    public function forExistingUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'invitee_id' => User::factory(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'declined_at' => now(),
        ]);
    }
}
