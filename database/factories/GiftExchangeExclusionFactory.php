<?php

namespace Database\Factories;

use App\Models\GiftExchange;
use App\Models\GiftExchangeExclusion;
use App\Models\GiftExchangeParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GiftExchangeExclusion>
 */
class GiftExchangeExclusionFactory extends Factory
{
    protected $model = GiftExchangeExclusion::class;

    public function definition(): array
    {
        return [
            'exchange_id' => GiftExchange::factory(),
            'giver_id' => GiftExchangeParticipant::factory(),
            'receiver_id' => GiftExchangeParticipant::factory(),
        ];
    }
}
