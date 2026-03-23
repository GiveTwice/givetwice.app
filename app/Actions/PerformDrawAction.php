<?php

namespace App\Actions;

use App\Events\GiftExchangeDrawCompleted;
use App\Models\GiftExchange;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class PerformDrawAction
{
    private const MAX_ATTEMPTS = 1000;

    public function execute(GiftExchange $exchange): GiftExchange
    {
        if ($exchange->isDrawn()) {
            throw new InvalidArgumentException('Draw has already been completed for this exchange.');
        }

        $participants = $exchange->participants()->get();

        if ($participants->count() < 3) {
            throw new InvalidArgumentException('At least 3 participants are required to perform a draw.');
        }

        $excludedPairs = $this->buildExcludedPairs($exchange);

        $this->validateExclusionsAreSolvable($participants, $excludedPairs);

        return DB::transaction(function () use ($exchange, $participants, $excludedPairs) {
            $shuffled = $this->findValidCycle($participants, $excludedPairs);

            /*
             * Create a single cycle: [0]->[1]->[2]->...->[n]->[0]
             * Each participant buys for the next one in the shuffled list.
             * The last participant buys for the first.
             *
             *   A -> B -> C -> D -> A
             *   └────────────────────┘
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

            $exchange = $exchange->fresh();

            event(new GiftExchangeDrawCompleted($exchange));

            return $exchange;
        });
    }

    /**
     * Build a set of excluded (giver_id, receiver_id) pairs from the exchange's exclusions.
     *
     * @return array<string, true> Keyed as "giverId:receiverId" for O(1) lookup.
     */
    private function buildExcludedPairs(GiftExchange $exchange): array
    {
        $exclusions = $exchange->exclusions()->get();

        $pairs = [];
        /** @var \App\Models\GiftExchangeExclusion $exclusion */
        foreach ($exclusions as $exclusion) {
            $pairs[$exclusion->giver_id.':'.$exclusion->receiver_id] = true;
        }

        return $pairs;
    }

    /**
     * Check that exclusions don't make a valid draw impossible.
     *
     * A participant who is excluded from giving to ALL others can never be placed
     * in a valid cycle. Detect this early with a clear error message.
     *
     * @param  array<string, true>  $excludedPairs
     */
    private function validateExclusionsAreSolvable(Collection $participants, array $excludedPairs): void
    {
        if ($excludedPairs === []) {
            return;
        }

        $participantIds = $participants->pluck('id');

        foreach ($participants as $participant) {
            $validReceivers = $participantIds->filter(function ($receiverId) use ($participant, $excludedPairs) {
                if ($receiverId === $participant->id) {
                    return false; // Can't give to self
                }

                return ! isset($excludedPairs[$participant->id.':'.$receiverId]);
            });

            if ($validReceivers->isEmpty()) {
                throw new InvalidArgumentException(
                    "Participant \"{$participant->name}\" is excluded from giving to all other participants. The draw is impossible."
                );
            }
        }
    }

    /**
     * Find a valid single-cycle assignment that respects all exclusions.
     *
     * Shuffles participants into a random cycle and checks it against exclusions.
     * Retries up to MAX_ATTEMPTS times. For typical use cases (couples exclusions
     * in groups of 3-50), this converges very quickly.
     *
     * @param  array<string, true>  $excludedPairs
     */
    private function findValidCycle(Collection $participants, array $excludedPairs): Collection
    {
        // No exclusions — single shuffle is always valid
        if ($excludedPairs === []) {
            return $participants->shuffle();
        }

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $shuffled = $participants->shuffle();

            if ($this->cycleRespectsExclusions($shuffled, $excludedPairs)) {
                return $shuffled;
            }
        }

        throw new RuntimeException(
            'Could not find a valid draw after '.self::MAX_ATTEMPTS.' attempts. The exclusion rules may be too restrictive.'
        );
    }

    /**
     * Check if a cycle (shuffled list) respects all exclusion rules.
     *
     * @param  array<string, true>  $excludedPairs
     */
    private function cycleRespectsExclusions(Collection $shuffled, array $excludedPairs): bool
    {
        $count = $shuffled->count();

        for ($i = 0; $i < $count; $i++) {
            $giverId = $shuffled[$i]->id;
            $receiverId = $shuffled[($i + 1) % $count]->id;

            if (isset($excludedPairs[$giverId.':'.$receiverId])) {
                return false;
            }
        }

        return true;
    }
}
