<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();

    $this->user = User::factory()->create();
    $this->list = GiftList::factory()->create(['creator_id' => $this->user->id]);
    $this->user->lists()->attach($this->list->id);
});

describe('Gift store flow — URL input', function () {
    it('creates a pending gift and redirects to fetching page', function () {
        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'https://www.bol.com/nl/p/some-product/123',
                'list_id' => $this->list->id,
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift)->not->toBeNull();
        expect($gift->url)->toBe('https://www.bol.com/nl/p/some-product/123');
        expect($gift->fetch_status)->toBe('pending');
        expect($gift->title)->toBeNull();
    });

    it('prepends https:// to bare domain input', function () {
        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'bol.com/nl/p/some-product/123',
                'list_id' => $this->list->id,
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift->url)->toBe('https://bol.com/nl/p/some-product/123');
        expect($gift->fetch_status)->toBe('pending');
    });

    it('rejects duplicate URLs on the same list', function () {
        Gift::factory()->create([
            'user_id' => $this->user->id,
            'url' => 'https://example.com/product',
        ])->lists()->attach($this->list->id);

        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'https://example.com/product',
                'list_id' => $this->list->id,
            ])
            ->assertSessionHasErrors('input');
    });
});

describe('Gift store flow — text input', function () {
    it('creates a skipped gift with title and redirects to edit', function () {
        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'Contribution to my new bike',
                'list_id' => $this->list->id,
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift)->not->toBeNull();
        expect($gift->url)->toBeNull();
        expect($gift->title)->toBe('Contribution to my new bike');
        expect($gift->fetch_status)->toBe('skipped');
    });

    it('treats input with spaces as text', function () {
        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'a good book about Laravel',
                'list_id' => $this->list->id,
            ])
            ->assertRedirect();

        $gift = Gift::latest()->first();

        expect($gift->fetch_status)->toBe('skipped');
        expect($gift->title)->toBe('a good book about Laravel');
    });

    it('allows duplicate titles for text gifts', function () {
        Gift::factory()->skipped()->create([
            'user_id' => $this->user->id,
            'title' => 'A nice book',
        ])->lists()->attach($this->list->id);

        $this->actingAs($this->user)
            ->post('/en/gifts', [
                'input' => 'A nice book',
                'list_id' => $this->list->id,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    });
});
