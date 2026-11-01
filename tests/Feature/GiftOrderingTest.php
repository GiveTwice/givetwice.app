<?php

use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->parent = User::factory()->create();
    $this->list = GiftList::factory()->create(['creator_id' => $this->parent->id]);
    $this->list->users()->attach($this->parent);
    $this->gifts = Gift::factory()->count(3)->skipped()->create();
    foreach ($this->gifts as $index => $gift) {
        $this->list->gifts()->attach($gift, ['sort_order' => $index]);
    }
    $this->url = fn ($gift) => route('gifts.order', ['locale' => 'en', 'list' => $this->list, 'gift' => $gift]);
});

it('persists a managers move and uses the same order in owner and shared views', function () {
    Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gifts[1]->id]);
    $this->actingAs($this->parent)->post(($this->url)($this->gifts[1]), ['direction' => 'up'])->assertRedirect();
    $expected = [$this->gifts[1]->id, $this->gifts[0]->id, $this->gifts[2]->id];
    $this->get('/en/dashboard')->assertViewHas('lists', fn ($lists) => $lists->sole()->gifts->pluck('id')->all() === $expected);
    auth()->logout();
    $this->get($this->list->getPublicUrl('en'))->assertViewHas('gifts', fn ($gifts) => $gifts->pluck('id')->all() === $expected);
});

it('rejects outsiders, foreign gifts and invalid moves, and handles the first boundary', function () {
    $this->actingAs(User::factory()->create())->post(($this->url)($this->gifts[0]), ['direction' => 'up'])->assertForbidden();
    $this->actingAs($this->parent)->post(($this->url)(Gift::factory()->skipped()->create()), ['direction' => 'up'])->assertNotFound();
    $this->post(($this->url)($this->gifts[0]), ['direction' => 'left'])->assertSessionHasErrors('direction');
    $this->post(($this->url)($this->gifts[0]), ['direction' => 'up'])->assertRedirect();
    expect($this->list->gifts()->pluck('gifts.id')->all())->toBe($this->gifts->pluck('id')->all());
});

it('appends after the saved maximum and preserves ties through subsequent moves', function () {
    $this->list->gifts()->updateExistingPivot($this->gifts[2]->id, ['sort_order' => 20]);
    $this->actingAs($this->parent)->post('/en/gifts', ['input' => 'New wish', 'list_id' => $this->list->id])->assertRedirect();
    $new = Gift::latest('id')->first();
    expect($this->list->gifts()->find($new->id)->pivot->sort_order)->toBe(21);
    $this->post(($this->url)($new), ['direction' => 'up'])->assertRedirect();
    expect($this->list->gifts()->pluck('gifts.id')->all())->toBe([$this->gifts[0]->id, $this->gifts[1]->id, $new->id, $this->gifts[2]->id]);
    expect($this->list->gifts()->get()->pluck('pivot.sort_order')->all())->toBe([0, 1, 2, 3]);
});
