<?php

use App\Events\GiftExchangeDrawCompleted;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('Gift Exchange Feature', function () {

    describe('landing page', function () {
        it('renders the lootjes-trekken landing page', function () {
            $this->get('/nl/lootjes-trekken')
                ->assertOk()
                ->assertViewIs('exchanges.landing');
        });

        it('renders the secret-santa landing page', function () {
            $this->get('/en/secret-santa')
                ->assertOk()
                ->assertViewIs('exchanges.landing');
        });

        it('renders the tirage-au-sort landing page', function () {
            $this->get('/fr/tirage-au-sort')
                ->assertOk()
                ->assertViewIs('exchanges.landing');
        });

        it('returns 404 for invalid exchange types', function () {
            $this->get('/en/invalid-exchange')
                ->assertNotFound();
        });
    });

    describe('create exchange', function () {
        it('creates an exchange in draft status', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->post('/nl/lootjes-trekken', [
                    'name' => 'Familie Test',
                    'event_date' => now()->addMonth()->format('Y-m-d'),
                    'budget_amount' => 25,
                    'budget_currency' => 'EUR',
                    'participants' => [
                        ['name' => 'Alice', 'email' => 'alice@example.com'],
                        ['name' => 'Bob', 'email' => 'bob@example.com'],
                    ],
                    'organizer_participates' => true,
                ])
                ->assertRedirect();

            $exchange = GiftExchange::first();
            expect($exchange)->not->toBeNull();
            expect($exchange->name)->toBe('Familie Test');
            expect($exchange->status)->toBe('draft');
            expect($exchange->budget_amount)->toBe(2500);
            expect($exchange->participants)->toHaveCount(3);
        });

        it('requires authentication', function () {
            $this->post('/nl/lootjes-trekken', [
                'name' => 'Test',
                'event_date' => now()->addMonth()->format('Y-m-d'),
                'participants' => [
                    ['name' => 'A', 'email' => 'a@test.com'],
                    ['name' => 'B', 'email' => 'b@test.com'],
                    ['name' => 'C', 'email' => 'c@test.com'],
                ],
            ])->assertRedirect();

            expect(GiftExchange::count())->toBe(0);
        });

        it('validates required fields', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->post('/nl/lootjes-trekken', [])
                ->assertSessionHasErrors(['name', 'participants']);
        });
    });

    describe('draw names', function () {
        it('performs draw and changes status', function () {
            Event::fake([GiftExchangeDrawCompleted::class]);

            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(4)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->post("/nl/exchange/{$exchange->slug}/draw")
                ->assertRedirect();

            $exchange->refresh();
            expect($exchange->status)->toBe('drawn');
            expect($exchange->draw_completed_at)->not->toBeNull();

            Event::assertDispatched(GiftExchangeDrawCompleted::class);
        });

        it('prevents non-organizer from drawing', function () {
            $organizer = User::factory()->create();
            $other = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($other)
                ->post("/nl/exchange/{$exchange->slug}/draw")
                ->assertForbidden();
        });
    });

    describe('status page', function () {
        it('shows status page to organizer', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->get("/nl/exchange/{$exchange->slug}/status")
                ->assertOk()
                ->assertViewIs('exchanges.status');
        });

        it('denies status page to non-organizer', function () {
            $organizer = User::factory()->create();
            $other = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);

            $this->actingAs($other)
                ->get("/nl/exchange/{$exchange->slug}/status")
                ->assertForbidden();
        });
    });

    describe('reveal page', function () {
        it('shows reveal page with valid token', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $assignedTo = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $participant->update(['assigned_to_participant_id' => $assignedTo->id]);

            $this->get("/en/exchange/{$participant->token}")
                ->assertOk()
                ->assertViewIs('exchanges.reveal');
        });

        it('marks participant as viewed', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $assignedTo = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $participant->update(['assigned_to_participant_id' => $assignedTo->id]);

            expect($participant->fresh()->viewed_at)->toBeNull();

            $this->get("/en/exchange/{$participant->token}");

            expect($participant->fresh()->viewed_at)->not->toBeNull();
        });

        it('returns 404 for invalid token', function () {
            $this->get('/en/exchange/invalid-token-that-does-not-exist')
                ->assertNotFound();
        });

        it('returns 410 for expired token', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'created_at' => now()->subDays(91),
            ]);
            $assignedTo = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $participant->update(['assigned_to_participant_id' => $assignedTo->id]);

            $this->get("/en/exchange/{$participant->token}")
                ->assertGone();
        });

        it('still shows reveal page when already viewed', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->viewed()->create(['exchange_id' => $exchange->id]);
            $assignedTo = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $participant->update(['assigned_to_participant_id' => $assignedTo->id]);

            $this->get("/en/exchange/{$participant->token}")
                ->assertOk()
                ->assertViewIs('exchanges.reveal');
        });

        it('returns 404 for valid token on non-drawn exchange', function () {
            $exchange = GiftExchange::factory()->create(); // draft status
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->get("/en/exchange/{$participant->token}")
                ->assertNotFound();
        });
    });

    describe('join link', function () {
        it('generates join_token on creation', function () {
            $exchange = GiftExchange::factory()->create();

            expect($exchange->join_token)->not->toBeNull();
            expect(strlen($exchange->join_token))->toBe(32);
        });

        it('shows join form for draft exchange', function () {
            $exchange = GiftExchange::factory()->create();

            $this->get("/en/exchange/join/{$exchange->join_token}")
                ->assertOk()
                ->assertViewIs('exchanges.join')
                ->assertSee($exchange->name);
        });

        it('returns 410 for drawn exchange join link', function () {
            $exchange = GiftExchange::factory()->drawn()->create();

            $this->get("/en/exchange/join/{$exchange->join_token}")
                ->assertGone();
        });

        it('allows joining a draft exchange', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->post("/en/exchange/join/{$exchange->join_token}", [
                'name' => 'New Person',
                'email' => 'new@example.com',
            ])->assertRedirect();

            expect($exchange->participants()->where('email', 'new@example.com')->exists())->toBeTrue();
        });

        it('prevents duplicate email from joining', function () {
            $exchange = GiftExchange::factory()->create();
            GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'email' => 'existing@example.com',
            ]);

            $this->post("/en/exchange/join/{$exchange->join_token}", [
                'name' => 'Duplicate',
                'email' => 'existing@example.com',
            ])->assertSessionHasErrors('email');
        });

        it('prevents joining a drawn exchange', function () {
            $exchange = GiftExchange::factory()->drawn()->create();

            $this->post("/en/exchange/join/{$exchange->join_token}", [
                'name' => 'Late Joiner',
                'email' => 'late@example.com',
            ])->assertGone();
        });

        it('links to existing user when joining', function () {
            $user = User::factory()->create(['email' => 'known@example.com']);
            $exchange = GiftExchange::factory()->create();

            $this->post("/en/exchange/join/{$exchange->join_token}", [
                'name' => 'Known User',
                'email' => 'known@example.com',
            ]);

            $participant = $exchange->participants()->where('email', 'known@example.com')->first();
            expect($participant->user_id)->toBe($user->id);
        });
    });
});
