<?php

namespace App\Actions;

use App\Events\GiftExchangeDrawCompleted;
use App\Models\GiftExchange;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PerformDrawAction
{
    public function execute(GiftExchange $exchange): GiftExchange
    {
        if ($exchange->isDrawn()) {
            throw new InvalidArgumentException('Draw has already been completed for this exchange.');
        }

        $participants = $exchange->participants()->get();

        if ($participants->count() < 3) {
            throw new InvalidArgumentException('At least 3 participants are required to perform a draw.');
        }

        return DB::transaction(function () use ($exchange, $participants) {
            $shuffled = $participants->shuffle();

            /*
             * Create a single cycle: [0]→[1]→[2]→...→[n]→[0]
             * Each participant buys for the next one in the shuffled list.
             * The last participant buys for the first.
             *
             *   A → B → C → D → A
             *   └───────────────┘
             */
            for ($i = 0; $i < $shuffled->count(); $i++) {
                /** @var \App\Models\GiftExchangeParticipant $buyer */
                $buyer = $shuffled[$i];
                /** @var \App\Models\GiftExchangeParticipant $receiver */
                $receiver = $shuffled[($i + 1) % $shuffled->count()];

                $buyer->update(['assigned_to_participant_id' => $receiver->id]);
            }

            $exchange->update([
                'status' => 'drawn',
                'draw_completed_at' => now(),
            ]);

            event(new GiftExchangeDrawCompleted($exchange->fresh()));

            return $exchange->fresh();
        });
    }
}
