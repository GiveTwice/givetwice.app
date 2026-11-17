<?php

use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\SlackAlerts\Facades\SlackAlert;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    SlackAlert::fake();
    config(['services.turnstile.secret_key' => 'test-secret']);
    $this->list = GiftList::factory()->create();
    $this->list->users()->attach($this->list->creator);
    $this->gift = Gift::factory()->create(['user_id' => $this->list->creator_id]);
    $this->list->gifts()->attach($this->gift);
    $this->url = '/en/gifts/'.$this->gift->id;
    $this->withHeader('X-Reservation-Sheet', '1');
});

it('reserves for a guest when Cloudflare accepts the token', function () {
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

    $this->post($this->url.'/claim-anonymous', ['email' => 'buyer@example.com', 'cf-turnstile-response' => 'good-token'])->assertOk();

    expect($this->gift->fresh()->isClaimed())->toBeTrue();
    Http::assertSent(fn (Request $request) => $request['secret'] === 'test-secret' && $request['response'] === 'good-token');
});

it('refuses a guest reservation when Cloudflare rejects the token', function () {
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

    $this->postJson($this->url.'/claim-anonymous', ['email' => 'buyer@example.com', 'cf-turnstile-response' => 'forged'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cf-turnstile-response');

    expect($this->gift->claims()->exists())->toBeFalse();
});

it('refuses a guest reservation without asking Cloudflare when the token is missing', function () {
    Http::fake();

    $this->postJson($this->url.'/claim-anonymous', ['email' => 'buyer@example.com'])->assertJsonValidationErrors('cf-turnstile-response');

    expect($this->gift->claims()->exists())->toBeFalse();
    Http::assertNothingSent();
});

it('lets a signed-in user reserve without a token', function () {
    Http::fake();

    $this->actingAs(User::factory()->create())->post($this->url.'/claim', [])->assertOk();

    expect($this->gift->fresh()->isClaimed())->toBeTrue();
    Http::assertNothingSent();
});

it('lets a guest change the email on their own reservation without a token', function () {
    Http::fake();
    $claim = Claim::factory()->create(['gift_id' => $this->gift->id, 'confirmation_token' => 'mine']);

    $this->withCookie('reservation_'.$this->gift->id, 'mine')->patch($this->url.'/reservation', ['email' => 'new@example.com'])->assertOk();

    expect($claim->fresh()->claimer_email)->toBe('new@example.com');
    Http::assertNothingSent();
});
