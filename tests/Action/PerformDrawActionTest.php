<?php

use App\Actions\PerformDrawAction;
use App\Events\GiftExchangeDrawCompleted;
use App\Models\GiftExchange;
use App\Models\GiftExchangeExclusion;
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

    describe('exclusions', function () {
        it('respects a single exclusion (couple cannot draw each other)', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(6)->create(['exchange_id' => $exchange->id]);

            $alice = $participants[0];
            $bob = $participants[1];

            // Alice cannot give to Bob, Bob cannot give to Alice
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $alice->id,
                'receiver_id' => $bob->id,
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $bob->id,
                'receiver_id' => $alice->id,
            ]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            $alice->refresh();
            $bob->refresh();

            expect($alice->assigned_to_participant_id)->not->toBe($bob->id);
            expect($bob->assigned_to_participant_id)->not->toBe($alice->id);
        });

        it('respects multiple exclusion pairs', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(8)->create(['exchange_id' => $exchange->id]);

            // Two couples: (0,1) and (2,3)
            $couples = [[$participants[0], $participants[1]], [$participants[2], $participants[3]]];

            foreach ($couples as [$a, $b]) {
                GiftExchangeExclusion::factory()->create([
                    'exchange_id' => $exchange->id,
                    'giver_id' => $a->id,
                    'receiver_id' => $b->id,
                ]);
                GiftExchangeExclusion::factory()->create([
                    'exchange_id' => $exchange->id,
                    'giver_id' => $b->id,
                    'receiver_id' => $a->id,
                ]);
            }

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            foreach ($couples as [$a, $b]) {
                $a->refresh();
                $b->refresh();
                expect($a->assigned_to_participant_id)->not->toBe($b->id);
                expect($b->assigned_to_participant_id)->not->toBe($a->id);
            }
        });

        it('works with one-directional exclusion', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(5)->create(['exchange_id' => $exchange->id]);

            // Alice cannot give to Bob (but Bob CAN give to Alice)
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $participants[0]->id,
                'receiver_id' => $participants[1]->id,
            ]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            $participants[0]->refresh();
            expect($participants[0]->assigned_to_participant_id)->not->toBe($participants[1]->id);
        });

        it('still produces valid cycle with exclusions', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(6)->create(['exchange_id' => $exchange->id]);

            // Add a couple exclusion
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $participants[0]->id,
                'receiver_id' => $participants[1]->id,
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $participants[1]->id,
                'receiver_id' => $participants[0]->id,
            ]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            $drawn = $result->participants()->get();

            // No self-draws
            foreach ($drawn as $p) {
                expect($p->assigned_to_participant_id)->not->toBe($p->id);
            }

            // Everyone gives and receives exactly once
            $assignedIds = $drawn->pluck('assigned_to_participant_id')->sort()->values();
            $participantIds = $drawn->pluck('id')->sort()->values();
            expect($assignedIds->toArray())->toBe($participantIds->toArray());

            // Single cycle
            $byId = $drawn->keyBy('id');
            $visited = collect();
            $current = $byId->first();
            for ($i = 0; $i < $drawn->count(); $i++) {
                $visited->push($current->id);
                $current = $byId[$current->assigned_to_participant_id];
            }
            expect($visited->unique()->count())->toBe($drawn->count());
            expect($current->id)->toBe($byId->first()->id);
        });

        it('works with no exclusions (backwards compatible)', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(5)->create(['exchange_id' => $exchange->id]);

            $action = new PerformDrawAction;
            $result = $action->execute($exchange);

            expect($result->isDrawn())->toBeTrue();

            $participants = $result->participants()->get();
            foreach ($participants as $participant) {
                expect($participant->assigned_to_participant_id)->not->toBeNull();
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

        it('guarantees exclusions are respected across 50 iterations with couples', function () {
            for ($i = 0; $i < 50; $i++) {
                $exchange = GiftExchange::factory()->create();
                $count = rand(6, 12);
                $participants = GiftExchangeParticipant::factory()->count($count)->create(['exchange_id' => $exchange->id]);

                // Create one couple exclusion (first two participants)
                $alice = $participants[0];
                $bob = $participants[1];
                GiftExchangeExclusion::factory()->create([
                    'exchange_id' => $exchange->id,
                    'giver_id' => $alice->id,
                    'receiver_id' => $bob->id,
                ]);
                GiftExchangeExclusion::factory()->create([
                    'exchange_id' => $exchange->id,
                    'giver_id' => $bob->id,
                    'receiver_id' => $alice->id,
                ]);

                $action = new PerformDrawAction;
                $result = $action->execute($exchange);

                $alice->refresh();
                $bob->refresh();

                expect($alice->assigned_to_participant_id)->not->toBe(
                    $bob->id,
                    "Exclusion violated (Alice->Bob) on iteration $i"
                );
                expect($bob->assigned_to_participant_id)->not->toBe(
                    $alice->id,
                    "Exclusion violated (Bob->Alice) on iteration $i"
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

        it('throws when a participant is excluded from all others', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            // Exclude participant 0 from giving to both others
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $participants[0]->id,
                'receiver_id' => $participants[1]->id,
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $participants[0]->id,
                'receiver_id' => $participants[2]->id,
            ]);

            $action = new PerformDrawAction;
            $action->execute($exchange);
        })->throws(InvalidArgumentException::class, 'excluded from giving to all other participants');

        it('throws RuntimeException when exclusions are too restrictive for a valid cycle', function () {
            $exchange = GiftExchange::factory()->create();
            $participants = GiftExchangeParticipant::factory()->count(4)->create(['exchange_id' => $exchange->id]);

            // Create heavy cross-exclusions: each participant can only give to one specific person
            // This creates a situation where the only valid assignments are fixed,
            // but they may not form a single cycle
            // 0 can only give to 1, 1 can only give to 0, 2 can only give to 3, 3 can only give to 2
            // This forces two sub-cycles: (0->1->0) and (2->3->2) — no single cycle possible
            $ids = $participants->pluck('id');

            // 0 cannot give to 2 or 3
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[0],
                'receiver_id' => $ids[2],
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[0],
                'receiver_id' => $ids[3],
            ]);
            // 1 cannot give to 2 or 3
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[1],
                'receiver_id' => $ids[2],
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[1],
                'receiver_id' => $ids[3],
            ]);
            // 2 cannot give to 0 or 1
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[2],
                'receiver_id' => $ids[0],
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[2],
                'receiver_id' => $ids[1],
            ]);
            // 3 cannot give to 0 or 1
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[3],
                'receiver_id' => $ids[0],
            ]);
            GiftExchangeExclusion::factory()->create([
                'exchange_id' => $exchange->id,
                'giver_id' => $ids[3],
                'receiver_id' => $ids[1],
            ]);

            $action = new PerformDrawAction;
            $action->execute($exchange);
        })->throws(RuntimeException::class, 'Could not find a valid draw');
    });
});
