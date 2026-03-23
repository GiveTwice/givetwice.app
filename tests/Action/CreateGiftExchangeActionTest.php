<?php

use App\Actions\CreateGiftExchangeAction;
use App\Models\GiftExchange;
use App\Models\User;

describe('CreateGiftExchangeAction', function () {

    it('creates an exchange with participants', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Family Exchange',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'nl',
            participants: $participants,
            budgetAmount: 2500,
            organizerParticipates: false,
        );

        expect($exchange)->toBeInstanceOf(GiftExchange::class);
        expect($exchange->name)->toBe('Family Exchange');
        expect($exchange->organizer_id)->toBe($organizer->id);
        expect($exchange->status)->toBe('draft');
        expect($exchange->budget_amount)->toBe(2500);
        expect($exchange->locale)->toBe('nl');
        expect($exchange->participants)->toHaveCount(3);
    });

    it('adds organizer as participant when checkbox is checked', function () {
        $organizer = User::factory()->create(['name' => 'Mattias', 'email' => 'mattias@example.com']);
        $participants = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
            organizerParticipates: true,
        );

        expect($exchange->participants)->toHaveCount(3);
        $emails = $exchange->participants->pluck('email')->toArray();
        expect($emails)->toContain('mattias@example.com');
    });

    it('links existing users to participants', function () {
        $organizer = User::factory()->create();
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $participants = [
            ['name' => 'Existing User', 'email' => 'existing@example.com'],
            ['name' => 'New Person', 'email' => 'new@example.com'],
            ['name' => 'Another', 'email' => 'another@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
        );

        $linkedParticipant = $exchange->participants->firstWhere('email', 'existing@example.com');
        expect($linkedParticipant->user_id)->toBe($existingUser->id);

        $newParticipant = $exchange->participants->firstWhere('email', 'new@example.com');
        expect($newParticipant->user_id)->toBeNull();
    });

    it('normalizes email addresses', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'Alice', 'email' => '  ALICE@Example.COM  '],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
        );

        expect($exchange->participants->firstWhere('name', 'Alice')->email)->toBe('alice@example.com');
    });

    it('deduplicates email addresses', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Alice Duplicate', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
        );

        expect($exchange->participants)->toHaveCount(3);
    });

    it('throws when fewer than 3 participants after dedup', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
            organizerParticipates: false,
        );
    })->throws(InvalidArgumentException::class);

    it('generates a unique slug', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'A', 'email' => 'a@example.com'],
            ['name' => 'B', 'email' => 'b@example.com'],
            ['name' => 'C', 'email' => 'c@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'My Group',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
        );

        expect($exchange->slug)->toContain('my-group');
    });

    it('generates tokens for all participants', function () {
        $organizer = User::factory()->create();
        $participants = [
            ['name' => 'A', 'email' => 'a@example.com'],
            ['name' => 'B', 'email' => 'b@example.com'],
            ['name' => 'C', 'email' => 'c@example.com'],
        ];

        $action = new CreateGiftExchangeAction;
        $exchange = $action->execute(
            organizer: $organizer,
            name: 'Test',
            eventDate: now()->addMonth()->format('Y-m-d'),
            locale: 'en',
            participants: $participants,
        );

        foreach ($exchange->participants as $participant) {
            expect($participant->token)->toHaveLength(64);
        }
    });
});
