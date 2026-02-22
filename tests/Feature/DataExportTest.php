<?php

use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('Data export', function () {
    it('requires authentication', function () {
        $this->post('/en/settings/data-export')
            ->assertRedirect('/en/login');
    });

    it('downloads a JSON file with correct filename', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/en/settings/data-export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertHeader(
            'Content-Disposition',
            'attachment; filename="givetwice-data-export-'.now()->format('Y-m-d').'.json"'
        );
    });

    it('contains account info without sensitive data', function () {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'locale_preference' => 'en',
            'google_id' => 'google-123',
        ]);

        $response = $this->actingAs($user)
            ->post('/en/settings/data-export');

        $data = $response->json();

        expect($data['account']['name'])->toBe('Test User');
        expect($data['account']['email'])->toBe('test@example.com');
        expect($data['account']['locale_preference'])->toBe('en');
        expect($data['account']['email_verified'])->toBeTrue();
        expect($data['account']['two_factor_enabled'])->toBeFalse();

        expect($data['social_accounts']['google_connected'])->toBeTrue();
        expect($data['social_accounts']['facebook_connected'])->toBeFalse();
        expect($data['social_accounts']['apple_connected'])->toBeFalse();

        $json = $response->getContent();
        expect($json)->not->toContain('google-123');
        expect($json)->not->toContain('password');
        expect($json)->not->toContain('two_factor_secret');
        expect($json)->not->toContain('two_factor_recovery_codes');
    });

    it('includes wishlists with public URLs', function () {
        $user = User::factory()->create();
        $list = GiftList::factory()->create(['creator_id' => $user->id, 'name' => 'My Wishlist']);
        $user->lists()->attach($list->id, ['joined_at' => now()]);

        $response = $this->actingAs($user)
            ->post('/en/settings/data-export');

        $data = $response->json();

        expect($data['wishlists'])->toHaveCount(1);
        expect($data['wishlists'][0]['name'])->toBe('My Wishlist');
        expect($data['wishlists'][0]['public_url'])->toContain('/en/v/');
    });

    it('includes gifts with soft-deleted ones', function () {
        $user = User::factory()->create();
        Gift::factory()->create(['user_id' => $user->id, 'title' => 'Active Gift']);
        $deletedGift = Gift::factory()->create(['user_id' => $user->id, 'title' => 'Deleted Gift']);
        $deletedGift->delete();

        $response = $this->actingAs($user)
            ->post('/en/settings/data-export');

        $data = $response->json();

        expect($data['gifts'])->toHaveCount(2);
        $titles = array_column($data['gifts'], 'title');
        expect($titles)->toContain('Active Gift');
        expect($titles)->toContain('Deleted Gift');
    });

    it('includes claims made by user and claims on user gifts', function () {
        $user = User::factory()->create();
        $gift = Gift::factory()->create(['user_id' => $user->id, 'title' => 'My Gift']);

        Claim::factory()->create([
            'gift_id' => Gift::factory()->create()->id,
            'user_id' => $user->id,
        ]);

        Claim::factory()->create([
            'gift_id' => $gift->id,
            'user_id' => null,
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/en/settings/data-export');

        $data = $response->json();

        expect($data['claims_made'])->toHaveCount(1);
        expect($data['claims_on_your_gifts'])->toHaveCount(1);
        expect($data['claims_on_your_gifts'][0]['gift_title'])->toBe('My Gift');
        expect($data['claims_on_your_gifts'][0])->toHaveKeys(['claimer_name', 'claimer_email']);
    });

    it('is rate-limited to 5 requests per 60 minutes', function () {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)
                ->post('/en/settings/data-export')
                ->assertOk();
        }

        $this->actingAs($user)
            ->post('/en/settings/data-export')
            ->assertStatus(429);
    });
});
