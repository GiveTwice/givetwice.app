<?php

use App\Mail\GiftExchangeInviteMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Support\Str;

describe('GiftExchangeInviteMail', function () {

    describe('envelope', function () {
        it('has the correct subject including exchange name', function () {
            $exchange = GiftExchange::factory()->create(['name' => 'Familie Kerst 2025']);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->envelope()->subject)
                ->toContain('Familie Kerst 2025')
                ->toContain("You've been drawn!");
        });

        it('includes the dice emoji in the subject', function () {
            $exchange = GiftExchange::factory()->create(['name' => 'Test Exchange']);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->envelope()->subject)->toContain('🎲');
        });
    });

    describe('content', function () {
        it('uses the exchange-invite view', function () {
            $exchange = GiftExchange::factory()->create();
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->content()->view)->toBe('emails.exchange-invite');
        });

        it('passes revealUrl built from participant token', function () {
            $exchange = GiftExchange::factory()->create(['locale' => 'en']);
            $token = Str::random(64);
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'token' => $token,
            ]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            $with = $mail->content()->with;
            expect($with['revealUrl'])->toContain($token);
            expect($with['revealUrl'])->toContain('/en/exchange/');
        });

        it('passes participant to the view', function () {
            $exchange = GiftExchange::factory()->create();
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'name' => 'Alice Wonderland',
            ]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            $with = $mail->content()->with;
            expect($with['participant']->name)->toBe('Alice Wonderland');
        });

        it('passes exchange to the view', function () {
            $exchange = GiftExchange::factory()->create(['name' => 'Holiday Party']);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            $with = $mail->content()->with;
            expect($with['exchange']->name)->toBe('Holiday Party');
        });
    });

    describe('rendered output', function () {
        it('renders the participant name in the email body', function () {
            $exchange = GiftExchange::factory()->create(['name' => 'Winter Exchange']);
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'name' => 'Bob Builder',
            ]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->render())->toContain('Bob Builder');
        });

        it('renders the exchange name in the email body', function () {
            $exchange = GiftExchange::factory()->create(['name' => 'Winter Exchange']);
            $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
            $exchange->load('organizer');

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->render())->toContain('Winter Exchange');
        });

        it('renders the revealUrl as a link in the email body', function () {
            $exchange = GiftExchange::factory()->create(['locale' => 'en']);
            $token = Str::random(64);
            $participant = GiftExchangeParticipant::factory()->create([
                'exchange_id' => $exchange->id,
                'token' => $token,
            ]);

            $mail = new GiftExchangeInviteMail($participant, $exchange);

            expect($mail->render())->toContain($token);
        });
    });

});
