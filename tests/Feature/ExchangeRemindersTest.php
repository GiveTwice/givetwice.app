<?php

use App\Jobs\SendExchangeReminder;
use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

describe('exchange reminders', function () {
    beforeEach(function () {
        Mail::fake();
        $this->exchange = GiftExchange::factory()->create([
            'status' => 'drawn', 'draw_completed_at' => now()->subDays(4),
            'event_date' => now()->addMonth(), 'locale' => 'nl',
        ]);
    });

    it('sends each reminder only once across repeated schedule runs', function () {
        $unopened = GiftExchangeParticipant::factory()->create(['exchange_id' => $this->exchange->id]);
        $viewed = GiftExchangeParticipant::factory()->create([
            'exchange_id' => $this->exchange->id, 'viewed_at' => now()->subDays(2),
        ]);
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        $this->travel(1)->days();
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertSent(ExchangeRevealReminderMail::class, 1);
        Mail::assertSent(ExchangeWishlistNudgeMail::class, 1);
        expect($unopened->fresh()->reveal_reminder_sent_at)->not->toBeNull()
            ->and($viewed->fresh()->wishlist_nudge_sent_at)->not->toBeNull();
        Mail::assertSent(ExchangeRevealReminderMail::class, fn ($mail) => $mail->locale === 'nl');
    });

    it('requires confirmation to mute and stops both kinds of reminder', function () {
        $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $this->exchange->id]);
        $url = $participant->reminderMuteUrl();
        $this->get($url)->assertOk();
        expect($participant->fresh()->reminders_muted_at)->toBeNull();
        $this->post($url)->assertOk();
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        $participant->update(['viewed_at' => now()->subDays(2)]);
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertNothingSent();
        $this->post($url.'&tampered=1')->assertForbidden();
    });

    it('nudges an empty default list but skips filled lists and expired links', function () {
        $user = User::factory()->create();
        GiftList::factory()->create(['creator_id' => $user->id, 'is_default' => true]);
        $participant = GiftExchangeParticipant::factory()->create([
            'exchange_id' => $this->exchange->id, 'user_id' => $user->id, 'viewed_at' => now()->subDays(2),
        ]);
        GiftExchangeParticipant::factory()->create([
            'exchange_id' => $this->exchange->id, 'token_generated_at' => now()->subDays(91),
        ]);
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertSent(ExchangeWishlistNudgeMail::class, fn ($mail) => $mail->participant->is($participant));
        Mail::assertSentCount(1);
    });
});

it('rechecks mute and eligibility at delivery and ignores retried or deleted participants', function () {
    Mail::fake();
    $exchange = GiftExchange::factory()->create(['status' => 'drawn', 'draw_completed_at' => now()->subDays(4), 'event_date' => now()->addMonth()]);
    $participant = GiftExchangeParticipant::factory()->create(['exchange_id' => $exchange->id]);
    $job = new SendExchangeReminder($participant->id);
    $participant->update(['reminders_muted_at' => now()]);
    $job->handle();
    Mail::assertNothingSent();
    $participant->update(['reminders_muted_at' => null]);
    $job->handle();
    $job->handle();
    Mail::assertSentCount(1);
    $participant->delete();
    $job->handle();
    Mail::assertSentCount(1);
});
