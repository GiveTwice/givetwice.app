<?php

use App\Events\GiftClaimed;
use App\Events\GiftCreated;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Queue;
use Spatie\SlackAlerts\Facades\SlackAlert;

beforeEach(function () {
    Queue::fake();
    SlackAlert::fake();
});

describe('Slack notification on user registration', function () {
    it('sends a slack notification when a new user registers via email', function () {
        $user = User::factory()->create();

        event(new Registered($user));

        SlackAlert::expectMessageSentContaining($user->email);
        SlackAlert::expectMessageSentContaining('via email');
    });

    it('sends a slack notification when a new user registers via Google', function () {
        $user = User::factory()->create(['google_id' => '123456']);

        event(new Registered($user));

        SlackAlert::expectMessageSentContaining('via Google');
    });

    it('sends a slack notification when a new user registers via Facebook', function () {
        $user = User::factory()->create(['facebook_id' => '123456']);

        event(new Registered($user));

        SlackAlert::expectMessageSentContaining('via Facebook');
    });
});

describe('Slack notification on gift created', function () {
    it('sends a slack notification when a gift is created', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'title' => 'Cool Headphones',
        ]);

        event(new GiftCreated($gift));

        SlackAlert::expectMessageSentContaining($user->email);
        SlackAlert::expectMessageSentContaining('Cool Headphones');
    });

    it('uses url when gift has no title', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $user->id,
            'title' => null,
            'url' => 'https://example.com/product',
        ]);

        event(new GiftCreated($gift));

        SlackAlert::expectMessageSentContaining('https://example.com/product');
    });
});

describe('Slack notification on gift claimed', function () {
    it('sends a slack notification when a registered user claims a gift', function () {
        $owner = User::factory()->create();
        $claimer = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Nice Book',
        ]);
        $claim = Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => $claimer->id,
            'confirmed_at' => now(),
        ]);

        event(new GiftClaimed($gift, $claim, 1));

        SlackAlert::expectMessageSentContaining($claimer->email);
        SlackAlert::expectMessageSentContaining('Nice Book');
        SlackAlert::expectMessageSentContaining($owner->email);
    });

    it('sends a slack notification when an anonymous user claims a gift', function () {
        $owner = User::factory()->create();
        $gift = Gift::factory()->create([
            'user_id' => $owner->id,
            'title' => 'Nice Book',
        ]);
        $claim = Claim::factory()->anonymous()->create([
            'gift_id' => $gift->id,
            'claimer_email' => 'anon@example.com',
            'confirmed_at' => now(),
        ]);

        event(new GiftClaimed($gift, $claim, 1));

        SlackAlert::expectMessageSentContaining('anon@example.com');
        SlackAlert::expectMessageSentContaining('Nice Book');
    });
});
