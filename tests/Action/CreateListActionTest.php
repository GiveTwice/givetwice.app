<?php

use App\Actions\CreateListAction;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

describe('CreateListAction', function () {

    it('creates a list with the given name', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'My Birthday List');

        expect($list)->toBeInstanceOf(GiftList::class);
        expect($list->name)->toBe('My Birthday List');
        expect($list->creator_id)->toBe($user->id);
    });

    it('attaches the creator as a collaborator', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'Test List');

        expect($list->hasUser($user))->toBeTrue();
        expect($list->users)->toHaveCount(1);
    });

    it('sets description when provided', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'Test List', 'A test description');

        expect($list->description)->toBe('A test description');
    });

    it('creates non-default list by default', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'Test List');

        expect($list->is_default)->toBeFalse();
    });

    it('creates default list when specified', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'Test List', isDefault: true);

        expect($list->is_default)->toBeTrue();
    });

    it('generates a slug for the list', function () {
        $user = User::factory()->create();

        $action = new CreateListAction;
        $list = $action->execute($user, 'My Birthday List');

        expect($list->slug)->toContain('my-birthday-list');
    });

});
