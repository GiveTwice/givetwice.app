<?php

use App\Models\Gift;
use App\Models\GiftList;
use App\Models\ListChange;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

describe('list content changes', function () {
    beforeEach(function () {
        Queue::fake();
        $this->manager = User::factory()->create();
        $this->list = GiftList::factory()->create();
        $this->list->users()->attach($this->manager);
        $this->actingAs($this->manager);
        $this->gift = Gift::factory()->skipped()->create();
    });

    it('records additions from both relationship directions with the current actor', function (bool $fromList) {
        if ($fromList) {
            $this->list->gifts()->attach($this->gift);
        } else {
            $this->gift->lists()->attach($this->list);
        }
        $change = ListChange::sole();
        expect($change->kind)->toBe('added')->and($change->actor_id)->toBe($this->manager->id)
            ->and($change->list_id)->toBe($this->list->id)->and($change->title)->toBe($this->gift->title);
    })->with([true, false]);

    it('records edits in each list without recording fetch state or reservations', function () {
        $second = GiftList::factory()->create();
        $this->gift->lists()->attach([$this->list->id, $second->id]);
        $this->gift->update(['fetch_status' => 'failed']);
        expect(ListChange::count())->toBe(2);
        $this->gift->update(['title' => 'Changed', 'owner_note' => 'Blue']);
        expect(ListChange::where('kind', 'edited')->count())->toBe(2);
        $this->gift->claims()->create(['user_id' => User::factory()->create()->id, 'confirmed_at' => now()]);
        expect(ListChange::count())->toBe(4);
    });

    it('records removal on detach and soft deletion without losing the title', function (bool $detach) {
        $this->list->gifts()->attach($this->gift);
        if ($detach) {
            $this->list->gifts()->detach($this->gift);
        } else {
            $this->gift->delete();
        }
        $change = ListChange::where('kind', 'removed')->sole();
        expect($change->title)->toBe($this->gift->title)->and($change->actor_id)->toBe($this->manager->id);
    })->with([true, false]);
});
