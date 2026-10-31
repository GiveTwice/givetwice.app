<?php

use App\Actions\PerformDrawAction;
use App\Mail\ExchangeRevealReminderMail;
use App\Mail\ExchangeWishlistNudgeMail;
use App\Mail\GiftExchangeInviteMail;
use App\Models\Gift;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

describe('Secret Santa v2', function () {
    beforeEach(function () {
        Mail::fake();
        Queue::fake();
        $this->organizer = User::factory()->create();
        $this->exchange = GiftExchange::factory()->create(['organizer_id' => $this->organizer->id, 'locale' => 'en']);
        $this->participants = GiftExchangeParticipant::factory()->count(4)->create(['exchange_id' => $this->exchange->id, 'email' => null]);
        $this->base = '/en/exchange/'.$this->exchange->slug;
    });

    it('creates separate private links for names without emails', function () {
        $this->actingAs($this->organizer)->post('/en/secret-santa', [
            'name' => 'Family', 'organizer_participates' => true,
            'participants' => [['name' => 'A'], ['name' => 'B', 'email' => null]],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $exchange = GiftExchange::latest('id')->first();
        expect($exchange->participants)->toHaveCount(3)
            ->and($exchange->participants->pluck('token')->unique())->toHaveCount(3)
            ->and($exchange->participants()->whereNull('email')->count())->toBe(2);
    });

    it('enforces total participant limits including the organizer', function (int $count, bool $organizer) {
        $this->actingAs($this->organizer)->post('/en/secret-santa', [
            'name' => 'Family', 'organizer_participates' => $organizer,
            'participants' => array_fill(0, $count, ['name' => 'Guest']),
        ])->assertSessionHasErrors('participants');
    })->with([[2, false], [50, true], [51, false]]);

    it('allows adding and joining without email', function () {
        $this->actingAs($this->organizer)->post($this->base.'/participants', ['name' => 'Added'])->assertSessionHasNoErrors();
        $this->post('/en/exchange/join/'.$this->exchange->join_token, ['name' => 'Joined'])->assertSessionHasNoErrors();
        expect($this->exchange->participants()->whereNull('email')->count())->toBe(6);
    });

    it('saves couples in both directions and honours them in the draw', function () {
        [$first, $second] = $this->participants;
        $this->actingAs($this->organizer)->post($this->base.'/rules', ['giver_id' => $first->id, 'receiver_id' => $second->id, 'mutual' => true])->assertSessionHasNoErrors();
        expect($this->exchange->exclusions()->count())->toBe(2);
        $this->post($this->base.'/draw')->assertSessionHasNoErrors();
        expect($first->fresh()->assigned_to_participant_id)->not->toBe($second->id)
            ->and($second->fresh()->assigned_to_participant_id)->not->toBe($first->id);
        Mail::assertNothingQueued();
    });

    it('supports removing couples and adding one way rules', function () {
        $rule = ['giver_id' => $this->participants[0]->id, 'receiver_id' => $this->participants[1]->id, 'mutual' => true];
        $this->actingAs($this->organizer)->post($this->base.'/rules', $rule);
        $this->post($this->base.'/rules', [...$rule, 'remove' => true]);
        expect($this->exchange->exclusions()->count())->toBe(0);
        $this->post($this->base.'/rules', [...$rule, 'mutual' => false]);
        expect($this->exchange->exclusions()->count())->toBe(1);
    });

    it('rejects foreign participants and self exclusions', function () {
        $this->actingAs($this->organizer)->post($this->base.'/rules', [
            'giver_id' => $this->participants[0]->id, 'receiver_id' => GiftExchangeParticipant::factory()->create()->id, 'mutual' => true,
        ])->assertSessionHasErrors('receiver_id');
        $this->post($this->base.'/rules', ['giver_id' => $this->participants[0]->id, 'receiver_id' => $this->participants[0]->id, 'mutual' => true])->assertSessionHasErrors('receiver_id');
    });

    it('returns a clear validation error for impossible rules without partial assignments', function () {
        foreach ($this->participants->skip(1) as $other) {
            $this->exchange->exclusions()->create(['giver_id' => $this->participants[0]->id, 'receiver_id' => $other->id]);
        }
        $this->actingAs($this->organizer)->post($this->base.'/draw')->assertSessionHasErrors('draw');
        expect($this->exchange->fresh()->isDraft())->toBeTrue()
            ->and($this->exchange->participants()->whereNotNull('assigned_to_participant_id')->count())->toBe(0);
    });

    it('detects impossible matchings even when everyone has an allowed receiver', function () {
        foreach ([0, 1] as $giver) {
            foreach ([0, 1, 3] as $receiver) {
                if ($giver !== $receiver) {
                    $this->exchange->exclusions()->create(['giver_id' => $this->participants[$giver]->id, 'receiver_id' => $this->participants[$receiver]->id]);
                }
            }
        }
        $this->actingAs($this->organizer)->post($this->base.'/draw')->assertSessionHasErrors('draw');
        expect($this->exchange->fresh()->isDraft())->toBeTrue();
    });

    it('keeps assignments out of the organizer response and GET reveal', function () {
        $participant = $this->participants[0];
        $participant->update(['user_id' => $this->organizer->id]);
        app(PerformDrawAction::class)->execute($this->exchange);
        $this->actingAs($this->organizer)->get($this->base.'/status')->assertOk()->assertViewMissing('myDraw')
            ->assertViewHas('exchange', fn ($exchange) => $exchange->participants->every(fn ($p) => ! $p->relationLoaded('assignedTo')));
        $this->get($participant->revealUrl())->assertOk()->assertHeader('Cache-Control', 'no-store, private')->assertViewHas('assignedTo', null)->assertDontSee($participant->fresh()->assignedTo->name);
        expect($participant->fresh()->viewed_at)->toBeNull();
        $this->post($participant->revealUrl())->assertOk()->assertSee($participant->fresh()->assignedTo->name);
        expect($participant->fresh()->hasViewed())->toBeTrue();
        $this->get($participant->revealUrl())->assertDontSee($participant->fresh()->assignedTo->name);
    });

    it('tracks WhatsApp opened separately from sent and renews expired private links', function () {
        app(PerformDrawAction::class)->execute($this->exchange);
        $participant = $this->participants[0];
        $url = $this->base.'/delivery/'.$participant->id;
        $oldUrl = $participant->revealUrl();
        $response = $this->actingAs($this->organizer)->post($url, ['action' => 'whatsapp'])->assertRedirect();
        expect($response->headers->get('Location'))->toStartWith('https://wa.me/?text=')->toContain(rawurlencode($oldUrl));
        expect($participant->fresh()->whatsapp_opened_at)->not->toBeNull()->and($participant->fresh()->sent_at)->toBeNull();
        $this->post($url, ['action' => 'sent']);
        expect($participant->fresh()->sent_at)->not->toBeNull();
        $participant->forceFill(['created_at' => now()->subDays(100), 'viewed_at' => now()])->save();
        $this->post($oldUrl)->assertGone();
        $this->post($url, ['action' => 'regenerate'])->assertRedirect();
        $this->get($oldUrl)->assertNotFound();
        $this->post($oldUrl)->assertNotFound();
        $this->get($participant->fresh()->revealUrl())->assertOk();
        expect($participant->fresh()->sent_at)->toBeNull()->and($participant->fresh()->viewed_at)->toBeNull();
    });

    it('restricts delivery and rules to the organizer and the correct exchange', function () {
        app(PerformDrawAction::class)->execute($this->exchange);
        $url = $this->base.'/delivery/'.$this->participants[0]->id;
        $this->actingAs(User::factory()->create())->post($url, ['action' => 'regenerate'])->assertForbidden();
        $this->post($this->base.'/rules', [])->assertForbidden();
        $this->actingAs($this->organizer)->post($this->base.'/delivery/'.GiftExchangeParticipant::factory()->create()->id, ['action' => 'sent'])->assertNotFound();
        $this->post($this->base.'/rules', [])->assertForbidden();
    });

    it('links an owned wishlist after login and shows it to the giver', function () {
        app(PerformDrawAction::class)->execute($this->exchange);
        $participant = $this->participants[0];
        $url = $participant->revealUrl().'/wishlist';
        $this->get($url)->assertRedirect('/en/login')->assertSessionHas('url.intended', $url);
        $user = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $user->id]);
        $this->actingAs($user)->get($url)->assertOk();
        $this->post($url, ['wishlist_id' => GiftList::factory()->create()->id])->assertSessionHasErrors('wishlist_id');
        $gift = Gift::factory()->create(['user_id' => $user->id]);
        $list->gifts()->attach($gift);
        $this->post($url, ['wishlist_id' => $list->id])->assertRedirect($list->getPublicUrl('en'));
        expect($participant->fresh()->defaultWishlist()->id)->toBe($list->id);
        $this->post($participant->fresh()->assignedBy->revealUrl())->assertSee($list->getPublicUrl('en'))->assertSee($gift->title);
        $this->post($url, ['name' => 'My new wishes'])->assertSessionHasNoErrors();
        expect($participant->fresh()->defaultWishlist()->name)->toBe('My new wishes');
    });

    it('skips email-less participants for invites, reveal reminders and wishlist nudges', function () {
        $participant = $this->participants[0];
        $participant->update(['email' => 'participant@example.com']);
        app(PerformDrawAction::class)->execute($this->exchange);
        Mail::assertQueued(GiftExchangeInviteMail::class, 1);
        $this->exchange->update(['draw_completed_at' => now()->subDays(4), 'event_date' => now()->addMonth()]);
        $this->get($participant->revealUrl());
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertQueued(ExchangeRevealReminderMail::class, 1);
        $this->exchange->participants()->update(['viewed_at' => now()->subDays(2)]);
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertQueued(ExchangeWishlistNudgeMail::class, 1);
        $participant->update(['wishlist_id' => GiftList::factory()->create()->id]);
        $this->artisan('exchanges:send-reminders')->assertSuccessful();
        Mail::assertQueued(ExchangeWishlistNudgeMail::class, 1);
        $this->actingAs($this->organizer)->post($this->base.'/resend-invite/'.$this->participants[1]->id)->assertUnprocessable();
    });
});
