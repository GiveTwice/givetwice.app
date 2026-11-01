<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('switches between managed lists and falls back safely for another users list', function () {
    Queue::fake();
    $parent = User::factory()->create();
    $first = GiftList::factory()->create(['creator_id' => $parent->id]);
    $second = GiftList::factory()->create();
    $parent->lists()->attach([$first->id, $second->id]);
    $foreign = GiftList::factory()->create();
    $gift = Gift::factory()->skipped()->create();
    $second->gifts()->attach($gift);
    $gift->claims()->create(['user_id' => User::factory()->create()->id, 'confirmed_at' => now()]);
    $this->actingAs($parent)->get('/en/dashboard?list='.$second->id)->assertOk()
        ->assertViewHas('selectedListId', $second->id)
        ->assertViewHas('lists', fn ($lists) => $lists->find($second->id)->gifts->sole()->claims->isEmpty());
    $this->get('/en/dashboard')->assertOk()->assertViewHas('selectedListId', $second->id);
    $this->get('/en/dashboard?list='.$foreign->id)->assertOk()->assertViewHas('selectedListId', $first->id);
});
