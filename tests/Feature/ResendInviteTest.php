<?php

use App\Mail\GiftExchangeInviteMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Mail::fake();
    RateLimiter::clear('resend-invite:*');
});

describe('Resend invite', function () {

    describe('authorization', function () {
        it('allows the organizer to resend an invite', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertRedirect();

            Mail::assertQueued(GiftExchangeInviteMail::class);
        });

        it('denies non-organizer from resending', function () {
            $organizer = User::factory()->create();
            $other = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($other)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertForbidden();

            Mail::assertNothingQueued();
        });

        it('requires authentication', function () {
            $exchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertRedirect('/nl/login');

            Mail::assertNothingQueued();
        });
    });

    describe('business rules', function () {
        it('returns error when exchange is not drawn yet', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertSessionHasErrors('resend');

            Mail::assertNothingQueued();
        });

        it('returns error when participant has already viewed their invite', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->viewed()->create(['exchange_id' => $exchange->id]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertSessionHasErrors('resend');

            Mail::assertNothingQueued();
        });

        it('returns 404 when participant does not belong to the exchange', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $otherExchange = GiftExchange::factory()->drawn()->create();
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $otherExchange->id]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertNotFound();
        });
    });

    describe('mail delivery', function () {
        it('queues invite mail to the participant email', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'email' => 'alice@example.com',
            ]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}");

            Mail::assertQueued(
                GiftExchangeInviteMail::class,
                fn ($mail) => $mail->hasTo('alice@example.com')
            );
        });

        it('shows success flash message after resend', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'name' => 'Bob',
            ]);

            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertSessionHas('success');
        });
    });

    describe('rate limiting', function () {
        it('allows one resend per participant', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            // First resend — should succeed
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertSessionMissing('errors');

            Mail::assertQueued(GiftExchangeInviteMail::class, 1);
        });

        it('blocks a second resend within 10 minutes', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            // First resend
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}");

            // Second resend — should be rate limited
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participant->id}")
                ->assertSessionHasErrors('resend');

            Mail::assertQueued(GiftExchangeInviteMail::class, 1);
        });

        it('allows resend to a different participant independently', function () {
            $organizer = User::factory()->create();
            $exchange = GiftExchange::factory()->drawn()->create(['organizer_id' => $organizer->id]);
            $participantA = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $participantB = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            // Exhaust rate limit for A
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participantA->id}");
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participantA->id}");

            // B should still work
            $this->actingAs($organizer)
                ->post("/nl/exchange/{$exchange->slug}/resend-invite/{$participantB->id}")
                ->assertSessionMissing('errors');

            Mail::assertQueued(GiftExchangeInviteMail::class, 2); // A once, B once
        });
    });

});
