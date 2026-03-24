<?php

use App\Events\GiftExchangeDrawCompleted;
use App\Mail\GiftExchangeInviteMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Support\Facades\Mail;

describe('SendGiftExchangeInvites listener', function () {

    beforeEach(function () {
        Mail::fake();
    });

    it('queues an invite mail to every participant when draw completes', function () {
        $exchange = GiftExchange::factory()->drawn()->create();
        $participants = GiftExchangeParticipant::factory()->count(4)->create([
            'exchange_id' => $exchange->id,
        ]);

        event(new GiftExchangeDrawCompleted($exchange));

        Mail::assertQueued(GiftExchangeInviteMail::class, 4);

        foreach ($participants as $participant) {
            Mail::assertQueued(
                GiftExchangeInviteMail::class,
                fn ($mail) => $mail->hasTo($participant->email)
            );
        }
    });

    it('passes the correct participant and exchange to each mail', function () {
        $exchange = GiftExchange::factory()->drawn()->create();
        $participant = GiftExchangeParticipant::factory()->create([
            'exchange_id' => $exchange->id,
        ]);

        event(new GiftExchangeDrawCompleted($exchange));

        Mail::assertQueued(
            GiftExchangeInviteMail::class,
            fn ($mail) => $mail->participant->id === $participant->id
                && $mail->exchange->id === $exchange->id
        );
    });

    it('sends no mails when the exchange has no participants', function () {
        $exchange = GiftExchange::factory()->drawn()->create();

        event(new GiftExchangeDrawCompleted($exchange));

        Mail::assertNothingQueued();
    });

    it('sends exactly one mail per participant, not per assignment', function () {
        $exchange = GiftExchange::factory()->drawn()->create();
        GiftExchangeParticipant::factory()->count(3)->create([
            'exchange_id' => $exchange->id,
        ]);

        event(new GiftExchangeDrawCompleted($exchange));

        Mail::assertQueued(GiftExchangeInviteMail::class, 3);
    });

});
