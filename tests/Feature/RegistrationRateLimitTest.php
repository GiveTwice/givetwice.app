<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    Notification::fake();
});

function registerFrom(string $remoteAddress, string $email, array $headers = [], string $password = 'a-Very-long-passw0rd!'): TestResponse
{
    $response = test()
        ->withServerVariables(['REMOTE_ADDR' => $remoteAddress])
        ->withHeaders($headers)
        ->post('/register', [
            'name' => 'Test',
            'email' => $email,
            'password' => $password,
        ]);

    Auth::logout();

    return $response;
}

describe('Registration rate limit', function () {
    it('refuses a fourth sign-up from one address within an hour', function () {
        foreach (range(1, 3) as $i) {
            registerFrom('203.0.113.7', "person{$i}@example.com")->assertSessionHasNoErrors();
        }

        registerFrom('203.0.113.7', 'person4@example.com')->assertSessionHasErrors('email');

        expect(User::query()->where('email', 'person4@example.com')->exists())->toBeFalse();
    });

    it('refuses a sixth sign-up from one address within a day', function () {
        foreach (range(1, 3) as $i) {
            registerFrom('203.0.113.7', "person{$i}@example.com")->assertSessionHasNoErrors();
        }

        $this->travel(61)->minutes();

        foreach (range(4, 5) as $i) {
            registerFrom('203.0.113.7', "person{$i}@example.com")->assertSessionHasNoErrors();
        }

        registerFrom('203.0.113.7', 'person6@example.com')->assertSessionHasErrors('email');
    });

    it('does not count a sign-up that failed validation', function () {
        foreach (range(1, 5) as $i) {
            registerFrom('203.0.113.7', "typo{$i}@example.com", password: 'short')->assertSessionHasErrors('password');
        }

        registerFrom('203.0.113.7', 'person@example.com')->assertSessionHasNoErrors();
    });

    it('counts every address in one IPv6 /64 together', function () {
        foreach (range(1, 3) as $i) {
            registerFrom("2001:db8:1:2::{$i}", "person{$i}@example.com")->assertSessionHasNoErrors();
        }

        registerFrom('2001:db8:1:2::ffff', 'person4@example.com')->assertSessionHasErrors('email');
    });

    it('limits each visitor behind the same Cloudflare server separately', function () {
        foreach (range(1, 3) as $i) {
            registerFrom('172.70.176.48', "a{$i}@example.com", ['X-Forwarded-For' => '198.51.100.1'])->assertSessionHasNoErrors();
        }

        registerFrom('172.70.176.48', 'b@example.com', ['X-Forwarded-For' => '198.51.100.2'])->assertSessionHasNoErrors();
        registerFrom('172.70.176.48', 'a4@example.com', ['X-Forwarded-For' => '198.51.100.1'])->assertSessionHasErrors('email');
    });

    it('ignores a forwarded address from a connection that is not Cloudflare', function () {
        foreach (range(1, 3) as $i) {
            registerFrom('203.0.113.7', "person{$i}@example.com", ['X-Forwarded-For' => "198.51.100.{$i}"])->assertSessionHasNoErrors();
        }

        registerFrom('203.0.113.7', 'person4@example.com', ['X-Forwarded-For' => '198.51.100.4'])->assertSessionHasErrors('email');
    });
});
