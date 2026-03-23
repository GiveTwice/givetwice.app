<?php

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('Organizer Flow', function () {

    describe('add participant', function () {
        it('allows organizer to add a participant to a draft exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'New Person',
                    'email' => 'new@example.com',
                ])
                ->assertRedirect()
                ->assertSessionHas('success');

            expect($exchange->participants()->where('email', 'new@example.com')->exists())->toBeTrue();
        });

        it('links participant to existing user', function () {
            $organizer = User::factory()->create();
            $existingUser = User::factory()->create(['email' => 'known@example.com']);
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);

            $this->actingAs($organizer)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'Known User',
                    'email' => 'known@example.com',
                ]);

            $participant = $exchange->participants()->where('email', 'known@example.com')->first();
            expect($participant->user_id)->toBe($existingUser->id);
        });

        it('prevents duplicate email', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'email' => 'existing@example.com',
            ]);

            $this->actingAs($user)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'Duplicate',
                    'email' => 'existing@example.com',
                ])
                ->assertSessionHasErrors('email');
        });

        it('prevents non-organizer from adding participants', function () {
            $organizer = User::factory()->create();
            $other = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);

            $this->actingAs($other)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'Sneaky',
                    'email' => 'sneaky@example.com',
                ])
                ->assertForbidden();
        });

        it('prevents adding to a drawn exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $user->id]);

            $this->actingAs($user)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'Late',
                    'email' => 'late@example.com',
                ])
                ->assertForbidden();
        });

        it('enforces max 50 participants', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(50)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->post(route('exchanges.participants.store', ['locale' => 'en', 'exchange' => $exchange->slug]), [
                    'name' => 'One Too Many',
                    'email' => 'toomany@example.com',
                ])
                ->assertSessionHasErrors('participant');
        });
    });

    describe('remove participant', function () {
        it('allows organizer to remove a participant from a draft exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            $participants = GiftExchangeParticipant::factory()->count(4)->create(['exchange_id' => $exchange->id]);
            $toRemove = $participants->first();

            $this->actingAs($user)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $toRemove->id,
                ]))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect(GiftExchangeParticipant::find($toRemove->id))->toBeNull();
        });

        it('prevents removing when only 3 participants remain', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            $participants = GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $participants->first()->id,
                ]))
                ->assertSessionHasErrors('participant');

            expect($exchange->participants()->count())->toBe(3);
        });

        it('prevents non-organizer from removing participants', function () {
            $organizer = User::factory()->create();
            $other = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($other)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $participant->id,
                ]))
                ->assertForbidden();
        });

        it('prevents removing from a drawn exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $user->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $participant->id,
                ]))
                ->assertForbidden();
        });

        it('cleans up exclusions when removing a participant', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            $participants = GiftExchangeParticipant::factory()->count(4)->create(['exchange_id' => $exchange->id]);

            $toRemove = $participants[0];
            $other = $participants[1];

            // Create exclusions involving the participant to remove
            $exchange->exclusions()->create(['giver_id' => $toRemove->id, 'receiver_id' => $other->id]);
            $exchange->exclusions()->create(['giver_id' => $other->id, 'receiver_id' => $toRemove->id]);

            $this->actingAs($user)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $toRemove->id,
                ]));

            expect($exchange->exclusions()->count())->toBe(0);
        });

        it('returns 404 when participant does not belong to exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $otherExchange = GiftExchange::factory()->create();
            $foreignParticipant = GiftExchangeParticipant::factory()->create(['exchange_id' => $otherExchange->id]);

            $this->actingAs($user)
                ->delete(route('exchanges.participants.destroy', [
                    'locale' => 'en',
                    'exchange' => $exchange->slug,
                    'participant' => $foreignParticipant->id,
                ]))
                ->assertNotFound();
        });
    });

    describe('view assignments', function () {
        it('shows assignments on status page after draw', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $user->id]);

            $p1 = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id, 'name' => 'Alice']);
            $p2 = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id, 'name' => 'Bob']);
            $p3 = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id, 'name' => 'Carol']);

            $p1->update(['assigned_to_participant_id' => $p2->id]);
            $p2->update(['assigned_to_participant_id' => $p3->id]);
            $p3->update(['assigned_to_participant_id' => $p1->id]);

            $this->actingAs($user)
                ->get(route('exchanges.status', ['locale' => 'en', 'exchange' => $exchange->slug]))
                ->assertOk()
                ->assertSee(__('Assignments'))
                ->assertSee('Alice')
                ->assertSee('Bob')
                ->assertSee('Carol');
        });

        it('does not show assignments section on draft exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->get(route('exchanges.status', ['locale' => 'en', 'exchange' => $exchange->slug]))
                ->assertOk()
                ->assertDontSee(__('Assignments'));
        });
    });

    describe('add participant form on status page', function () {
        it('shows add participant form on draft exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->get(route('exchanges.status', ['locale' => 'en', 'exchange' => $exchange->slug]))
                ->assertOk()
                ->assertSee(__('Add participant'));
        });

        it('hides add participant form on drawn exchange', function () {
            $user = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $user->id]);
            GiftExchangeParticipant::factory()->count(3)->create(['exchange_id' => $exchange->id]);

            $this->actingAs($user)
                ->get(route('exchanges.status', ['locale' => 'en', 'exchange' => $exchange->slug]))
                ->assertOk()
                ->assertDontSee(__('Add participant'));
        });
    });
});
