<?php

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Notification::fake();
    config(['services.turnstile.secret_key' => 'test-secret']);
});

function signUp(array $extra = []): TestResponse
{
    return test()->post('/register', [
        'name' => 'Test',
        'email' => 'person@example.com',
        'password' => 'a-Very-long-passw0rd!',
        ...$extra,
    ]);
}

describe('Registration Turnstile check', function () {
    it('creates the account when Cloudflare accepts the token', function () {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        signUp(['cf-turnstile-response' => 'good-token'])->assertSessionHasNoErrors();

        expect(User::query()->where('email', 'person@example.com')->exists())->toBeTrue();
        Http::assertSent(fn (Request $request) => $request['secret'] === 'test-secret' && $request['response'] === 'good-token');
    });

    it('refuses the account when Cloudflare rejects the token', function () {
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        signUp(['cf-turnstile-response' => 'forged'])->assertSessionHasErrors('cf-turnstile-response');

        expect(User::query()->where('email', 'person@example.com')->exists())->toBeFalse();
    });

    it('refuses the account without asking Cloudflare when the token is missing', function () {
        Http::fake();

        signUp()->assertSessionHasErrors('cf-turnstile-response');

        Http::assertNothingSent();
    });

    it('refuses the account when Cloudflare cannot be reached', function () {
        Http::fake(fn () => throw new ConnectionException('timed out'));

        signUp(['cf-turnstile-response' => 'good-token'])->assertSessionHasErrors('cf-turnstile-response');

        expect(User::query()->where('email', 'person@example.com')->exists())->toBeFalse();
    });

    it('skips the check when no secret key is configured', function () {
        config(['services.turnstile.secret_key' => null]);
        Http::fake();

        signUp()->assertSessionHasNoErrors();

        Http::assertNothingSent();
    });
});
