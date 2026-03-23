<?php

use App\Actions\PerformDrawAction;
use App\Events\GiftExchangeDrawCompleted;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([GiftExchangeDrawCompleted::class]);
});

describe('PerformDrawAction', function () {

    describe('happy path', function () {
        it('assigns each participant to another in a valid cycle', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(5)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            expect($result->isDrawn())->toBeTrue();
            expect($result->draw_completed_at)->not->toBeNull();

            $participants = $result->participants()->get();
            foreach ($participants as $participant) {
                expect($participant->assigned_to_participant_id)->not->toBeNull();
                expect($participant->assigned_to_participant_id)->not->toBe($participant->id);
            }
        });

        it('marks exchange as drawn with timestamp', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            expect($result->status)->toBe('drawn');
            expect($result->draw_completed_at)->not->toBeNull();
        });

        it('fires GiftExchangeDrawCompleted event', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $action->execute($exchange);

            Event::assertDispatched(GiftExchangeDrawCompleted::class, function ($event) use ($exchange) {
                return $event->exchange->id === $exchange->id;
            });
        });

        it('works with exactly 3 participants (minimum)', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            $participants = $result->participants()->get();
            $assignedIds = $participants->pluck('assigned_to_participant_id')->sort()->values();
            $participantIds = $participants->pluck('id')->sort()->values();

            expect($assignedIds->toArray())->toBe($participantIds->toArray());
        });

        it('works with 20 participants', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(20)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            $participants = $result->participants()->get();
            foreach ($participants as $participant) {
                expect($participant->assigned_to_participant_id)->not->toBeNull();
                expect($participant->assigned_to_participant_id)->not->toBe($participant->id);
            }
        });
    });

    describe('invariants (property-based)', function () {
        it('guarantees no self-draws across 100 iterations', function () {
            for ($i = 0; $i < 100; $i++) {
                $exchange = GiftExchange::factory()->create();
                $count = rand(3, 15);
                GiftExchangeParticipant::factory()->count($count)->create(['exchange_id' => $exchange->id]);

                $action = new PerformDrawAction;
                $result = $action->execute($exchange);

                $participants = $result->participants()->get();
                foreach ($participants as $participant) {
                    expect($participant->assigned_to_participant_id)->not->toBe(
                        $participant->id,
                        "Self-draw detected on iteration $i with $count participants"
                    );
                }
            }
        });

        it('guarantees everyone gives exactly once and receives exactly once', function () {
            for ($i = 0; $i < 50; $i++) {
                $exchange = GiftExchange::factory()->create();
                $count = rand(3, 12);
                GiftExchangeParticipant::factory()->count($count)->create(['exchange_id' => $exchange->id]);

                $action = new PerformDrawAction;
                $result = $action->execute($exchange);

                $participants = $result->participants()->get();
                $assignedIds = $participants->pluck('assigned_to_participant_id')->sort()->values();
                $participantIds = $participants->pluck('id')->sort()->values();

                expect($assignedIds->toArray())->toBe(
                    $participantIds->toArray(),
                    "Not everyone receives exactly once on iteration $i"
                );
            }
        });

        it('guarantees a single cycle (no sub-groups)', function () {
            for ($i = 0; $i < 50; $i++) {
                $exchange = GiftExchange::factory()->create();
                $count = rand(4, 10);
                GiftExchangeParticipant::factory()->count($count)->create(['exchange_id' => $exchange->id]);

                $action = new PerformDrawAction;
                $result = $action->execute($exchange);

                $participants = $result->participants()->get()->keyBy('id');
                $visited = collect();
                $current = $participants->first();

                for ($step = 0; $step < $count; $step++) {
                    $visited->push($current->id);
                    $current = $participants[$current->assigned_to_participant_id];
                }

                expect($visited->unique()->count())->toBe(
                    $count,
                    "Sub-group detected on iteration $i: visited {$visited->unique()->count()} of $count"
                );

                expect($current->id)->toBe(
                    $participants->first()->id,
                    "Cycle doesn't return to start on iteration $i"
                );
            }
        });
    });

    describe('validation', function () {
        it('throws when fewer than 3 participants', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(2)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $action->execute($exchange);
        })->throws(InvalidArgumentException::class, 'At least 3 participants');

        it('throws when draw already completed', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $action->execute($exchange);
        })->throws(InvalidArgumentException::class, 'already been completed');

        it('does not fire event when validation fails', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(2)->create(['exchange_id' => $exchange->id]);

            try {
                $action = new PerformDrawAction;
                $action->execute($exchange);
            } catch (InvalidArgumentException) {
            }

            Event::assertNotDispatched(GiftExchangeDrawCompleted::class);
        });
    });
});
