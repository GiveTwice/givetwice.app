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
    $this->gifts = Gift::factory()->count(4)->skipped()->create();
    foreach ($this->gifts as $index => $gift) {
        $this->list->gifts()->attach($gift, ['sort_order' => $index]);
    }
    $this->url = route('gifts.order', ['locale' => 'en', 'list' => $this->list]);
    $this->order = fn (): array => $this->list->gifts()->orderBy('gifts.id')->pluck('gifts.id')->all();
});

it('persists a dragged order and uses it in the owner and shared views', function () {
    Claim::factory()->anonymous()->confirmed()->create(['gift_id' => $this->gifts[1]->id]);
    $expected = [$this->gifts[2]->id, $this->gifts[0]->id, $this->gifts[3]->id, $this->gifts[1]->id];

    $this->actingAs($this->parent)->putJson($this->url, ['gifts' => $expected])->assertNoContent();

    $this->get('/en/dashboard')->assertViewHas('lists', fn ($lists) => $lists->sole()->gifts->pluck('id')->all() === $expected);
    auth()->logout();
    $this->get($this->list->getPublicUrl('en'))->assertViewHas('gifts', fn ($gifts) => $gifts->pluck('id')->all() === $expected);
});

it('reorders a subset of gifts inside the places they already take', function () {
    [$first, $second, $third, $fourth] = $this->gifts->pluck('id')->all();

    $this->actingAs($this->parent)->putJson($this->url, ['gifts' => [$fourth, $second]])->assertNoContent();

    expect(($this->order)())->toBe([$first, $fourth, $third, $second]);
});

it('rejects outsiders, gifts from another list and malformed orders', function (array $payload) {
    $this->actingAs($this->parent)->putJson($this->url, $payload)->assertUnprocessable();

    expect(($this->order)())->toBe($this->gifts->pluck('id')->all());
})->with([
    'gift from another list' => fn () => ['gifts' => [$this->gifts[1]->id, Gift::factory()->skipped()->create()->id]],
    'duplicate gift' => fn () => ['gifts' => [$this->gifts[0]->id, $this->gifts[0]->id]],
    'empty order' => fn () => ['gifts' => []],
    'not a list of ids' => fn () => ['gifts' => ['first']],
]);

it('forbids people who cannot edit the list', function () {
    $this->actingAs(User::factory()->create())
        ->putJson($this->url, ['gifts' => $this->gifts->reverse()->pluck('id')->all()])
        ->assertForbidden();

    expect(($this->order)())->toBe($this->gifts->pluck('id')->all());
});

it('appends after the saved maximum and renumbers ties on the next reorder', function () {
    $this->list->gifts()->updateExistingPivot($this->gifts[3]->id, ['sort_order' => 20]);
    $this->actingAs($this->parent)->post('/en/gifts', ['input' => 'New wish', 'list_id' => $this->list->id])->assertRedirect();
    $new = Gift::latest('id')->first();
    expect($this->list->gifts()->find($new->id)->pivot->sort_order)->toBe(21);

    $this->putJson($this->url, ['gifts' => [$new->id, $this->gifts[3]->id]])->assertNoContent();

    expect(($this->order)())->toBe([...$this->gifts->take(3)->pluck('id')->all(), $new->id, $this->gifts[3]->id])
        ->and($this->list->gifts()->get()->pluck('pivot.sort_order')->all())->toBe([0, 1, 2, 3, 4]);
});
