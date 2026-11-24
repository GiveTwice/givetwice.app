<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->owner = User::factory()->create();
    $this->list = GiftList::factory()->create(['creator_id' => $this->owner->id]);
    $this->owner->lists()->attach($this->list);
});

it('lands on the new list page after creating a list', function () {
    $response = $this->actingAs($this->owner)->post('/en/lists', ['name' => 'Kerstlijstje']);

    $list = $this->owner->lists()->where('name', 'Kerstlijstje')->sole();
    $response->assertRedirect('/en/list/'.$list->slug);
    $this->get('/en/list/'.$list->slug)->assertOk()->assertViewHas('list', fn ($shown) => $shown->is($list));
});

it('returns to the list page after editing the list', function () {
    $this->actingAs($this->owner)
        ->put('/en/list/'.$this->list->slug, ['name' => 'Sinterklaas'])
        ->assertRedirect('/en/list/'.$this->list->fresh()->slug);
});

it('shows the list to its owner and to collaborators', function () {
    $collaborator = User::factory()->create();
    $this->list->users()->attach($collaborator);
    $gift = Gift::factory()->skipped()->create();
    $this->list->gifts()->attach($gift);

    $this->actingAs($this->owner)->get('/en/list/'.$this->list->slug)->assertOk()
        ->assertViewHas('list', fn ($list) => $list->gifts->sole()->is($gift));
    $this->actingAs($collaborator)->get('/en/list/'.$this->list->slug)->assertOk();
});

it('sends someone who does not manage the list to its public page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/en/list/'.$this->list->slug)
        ->assertRedirect($this->list->getPublicUrl('en'));
});

it('hides reservations on the list page unless spoiler mode is on', function (bool $spoilerMode, int $visibleClaims) {
    $this->list->update(['spoiler_mode' => $spoilerMode]);
    $gift = Gift::factory()->skipped()->create();
    $this->list->gifts()->attach($gift);
    $gift->claims()->create(['user_id' => User::factory()->create()->id, 'confirmed_at' => now()]);

    $this->actingAs($this->owner)->get('/en/list/'.$this->list->slug)
        ->assertViewHas('list', fn ($list) => $list->gifts->sole()->claims->count() === $visibleClaims);
})->with([
    'surprise protected' => [false, 0],
    'spoiler mode' => [true, 1],
]);

it('offers one way to add a gift to an empty list and keeps the header button once it has gifts', function () {
    $addGiftUrl = e(route('gifts.create', ['locale' => 'en', 'list' => $this->list->id]));

    $empty = $this->actingAs($this->owner)->get('/en/list/'.$this->list->slug)->getContent();
    expect(substr_count($empty, 'href="'.$addGiftUrl.'"'))->toBe(1);

    $this->list->gifts()->attach(Gift::factory()->skipped()->create());
    $filled = $this->get('/en/list/'.$this->list->slug)->getContent();
    expect(substr_count($filled, 'href="'.$addGiftUrl.'"'))->toBe(1);
});

it('returns from the add gift page to the list it was opened for', function () {
    $foreign = GiftList::factory()->create();

    $this->actingAs($this->owner)->get('/en/gifts/create?list='.$this->list->id)
        ->assertViewHas('returnList', fn ($list) => $list->is($this->list))
        ->assertViewHas('selectedListId', $this->list->id);
    $this->get('/en/gifts/create?list='.$foreign->id)
        ->assertViewHas('returnList', null)
        ->assertViewHas('selectedListId', $this->list->id);
});

it('ignores a list parameter that is not exactly a list id', function (string $suffix) {
    $this->list->update(['is_default' => true]);
    $second = GiftList::factory()->create(['creator_id' => $this->owner->id]);
    $this->owner->lists()->attach($second);

    $this->actingAs($this->owner)->get('/en/gifts/create?list='.$second->id.$suffix)
        ->assertViewHas('returnList', null)
        ->assertViewHas('selectedListId', $this->list->id);
})->with([
    'slug suffix' => ['-kerstlijstje'],
    'decimal' => ['.5'],
]);

it('returns gift edits and deletes to the list they were started from', function () {
    $second = GiftList::factory()->create(['creator_id' => $this->owner->id, 'created_at' => now()->addMinute()]);
    $this->owner->lists()->attach($second);
    $gift = Gift::factory()->skipped()->create(['user_id' => $this->owner->id]);
    $this->list->gifts()->attach($gift);
    $second->gifts()->attach($gift);
    $foreign = GiftList::factory()->create();
    $this->actingAs($this->owner);

    $this->get("/en/gifts/{$gift->id}/edit?list={$second->id}")->assertViewHas('returnList', fn ($list) => $list->is($second));
    $this->put("/en/gifts/{$gift->id}?list={$second->id}", ['title' => 'Kite'])->assertRedirect('/en/list/'.$second->slug);
    $this->put("/en/gifts/{$gift->id}?list={$foreign->id}", ['title' => 'Kite'])->assertRedirect('/en/list/'.$this->list->slug);
    $this->delete("/en/gifts/{$gift->id}?list={$second->id}")->assertRedirect('/en/list/'.$second->slug);
});

it('ignores a list that does not hold the gift', function () {
    $empty = GiftList::factory()->create(['creator_id' => $this->owner->id]);
    $this->owner->lists()->attach($empty);
    $gift = Gift::factory()->skipped()->create(['user_id' => $this->owner->id]);
    $this->list->gifts()->attach($gift);

    $this->actingAs($this->owner)
        ->put("/en/gifts/{$gift->id}?list={$empty->id}", ['title' => 'Kite'])
        ->assertRedirect('/en/list/'.$this->list->slug);
});

it('sends an edit opened from the public preview back to that list', function () {
    $second = GiftList::factory()->create(['creator_id' => $this->owner->id, 'created_at' => now()->addMinute()]);
    $this->owner->lists()->attach($second);
    $gift = Gift::factory()->skipped()->create(['user_id' => $this->owner->id]);
    $this->list->gifts()->attach($gift);
    $second->gifts()->attach($gift);

    $this->actingAs($this->owner)->get($second->getPublicUrl('en'))
        ->assertSee(url("/en/gifts/{$gift->id}/edit").'?list='.$second->id, false);
    $this->get("/en/gifts/{$gift->id}/edit?list={$second->id}")
        ->assertSee('action="'.url("/en/gifts/{$gift->id}").'?list='.$second->id.'"', false);
});
