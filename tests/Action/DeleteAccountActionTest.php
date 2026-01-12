<?php

use App\Actions\DeleteAccountAction;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->trackQueriesForEfficiency();
});

describe('DeleteAccountAction', function () {

    describe('user deletion', function () {
        it('deletes the user', function () {
            $user = User::factory()->create();
            $userId = $user->id;

            $action = new DeleteAccountAction;
            $action->execute($user);

            expect(User::find($userId))->toBeNull();

            $this->assertQueriesAreEfficient();
        });

        it('deletes user sessions', function () {
            $user = User::factory()->create();

            DB::table('sessions')->insert([
                'id' => 'session-1',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ]);
            DB::table('sessions')->insert([
                'id' => 'session-2',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ]);

            expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(2);

            $action = new DeleteAccountAction;
            $action->execute($user);

            expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
        });
    });

    describe('gift cleanup', function () {
        it('deletes all user gifts', function () {
            $user = User::factory()->create();
            Gift::factory()->count(3)->create(['user_id' => $user->id]);

            expect(Gift::where('user_id', $user->id)->count())->toBe(3);

            $action = new DeleteAccountAction;
            $action->execute($user);

            expect(Gift::where('user_id', $user->id)->count())->toBe(0);
        });
    });

    describe('list cleanup', function () {
        it('deletes all user lists', function () {
            $user = User::factory()->create();
            GiftList::factory()->count(2)->create(['creator_id' => $user->id]);

            expect(GiftList::where('creator_id', $user->id)->count())->toBe(2);

            $action = new DeleteAccountAction;
            $action->execute($user);

            expect(GiftList::where('creator_id', $user->id)->count())->toBe(0);
        });

        it('removes gift-list associations', function () {
            $user = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $gift = Gift::factory()->create(['user_id' => $user->id]);
            $gift->lists()->attach($list->id);

            expect(DB::table('gift_list')->count())->toBe(1);

            $action = new DeleteAccountAction;
            $action->execute($user);

            expect(DB::table('gift_list')->count())->toBe(0);
        });
    });

    describe('claim cleanup', function () {
        it('deletes claims on user gifts', function () {
            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            Claim::factory()->create([
                'gift_id' => $gift->id,
                'user_id' => $claimer->id,
                'confirmed_at' => now(),
            ]);

            expect(Claim::where('gift_id', $gift->id)->count())->toBe(1);

            $action = new DeleteAccountAction;
            $action->execute($owner);

            expect(Claim::where('gift_id', $gift->id)->count())->toBe(0);
        });

        it('nullifies user_id on claims made by deleted user', function () {
            $owner = User::factory()->create();
            $claimer = User::factory()->create();
            $gift = Gift::factory()->create(['user_id' => $owner->id]);

            $claim = Claim::factory()->create([
                'gift_id' => $gift->id,
                'user_id' => $claimer->id,
                'confirmed_at' => now(),
            ]);

            $action = new DeleteAccountAction;
            $action->execute($claimer);

            expect($claim->fresh()->user_id)->toBeNull();
            expect($claim->fresh())->not->toBeNull(); // Claim still exists
        });
    });

    describe('complex scenario', function () {
        it('properly cleans up user with multiple lists, gifts, and claims', function () {
            // Create the user to be deleted
            $userToDelete = User::factory()->create();

            // Create another user who will claim gifts
            $otherUser = User::factory()->create();

            // Create multiple lists for the user
            $list1 = GiftList::factory()->create(['creator_id' => $userToDelete->id, 'name' => 'Birthday']);
            $list2 = GiftList::factory()->create(['creator_id' => $userToDelete->id, 'name' => 'Christmas']);

            // Create multiple gifts
            $gift1 = Gift::factory()->create(['user_id' => $userToDelete->id, 'title' => 'Gift 1']);
            $gift2 = Gift::factory()->create(['user_id' => $userToDelete->id, 'title' => 'Gift 2']);
            $gift3 = Gift::factory()->create(['user_id' => $userToDelete->id, 'title' => 'Gift 3']);

            // Attach gifts to lists (some to multiple lists)
            $gift1->lists()->attach([$list1->id, $list2->id]);
            $gift2->lists()->attach($list1->id);
            $gift3->lists()->attach($list2->id);

            // Create claims on the user's gifts
            $claim1 = Claim::factory()->create([
                'gift_id' => $gift1->id,
                'user_id' => $otherUser->id,
                'confirmed_at' => now(),
            ]);
            $claim2 = Claim::factory()->anonymous()->create([
                'gift_id' => $gift2->id,
                'claimer_email' => 'anonymous@example.com',
            ]);

            // Create a gift owned by other user, claimed by user to be deleted
            $otherUserGift = Gift::factory()->create(['user_id' => $otherUser->id]);
            $claimByDeletedUser = Claim::factory()->create([
                'gift_id' => $otherUserGift->id,
                'user_id' => $userToDelete->id,
                'confirmed_at' => now(),
            ]);

            // Add sessions
            DB::table('sessions')->insert([
                'id' => 'user-session',
                'user_id' => $userToDelete->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'test',
                'last_activity' => now()->timestamp,
            ]);

            // Verify initial state
            expect(User::count())->toBe(2);
            expect(GiftList::count())->toBe(2);
            expect(Gift::count())->toBe(4);
            expect(Claim::count())->toBe(3);
            expect(DB::table('gift_list')->count())->toBe(4);
            expect(DB::table('sessions')->where('user_id', $userToDelete->id)->count())->toBe(1);

            // Execute deletion
            $action = new DeleteAccountAction;
            $action->execute($userToDelete);

            // Verify user is deleted
            expect(User::find($userToDelete->id))->toBeNull();
            expect(User::find($otherUser->id))->not->toBeNull();

            // Verify lists are deleted
            expect(GiftList::count())->toBe(0);

            // Verify gifts are deleted (only userToDelete's gifts)
            expect(Gift::count())->toBe(1);
            expect(Gift::find($otherUserGift->id))->not->toBeNull();

            // Verify claims on deleted gifts are removed
            expect(Claim::find($claim1->id))->toBeNull();
            expect(Claim::find($claim2->id))->toBeNull();

            // Verify claim by deleted user still exists but user_id is null
            $claimByDeletedUser->refresh();
            expect($claimByDeletedUser)->not->toBeNull();
            expect($claimByDeletedUser->user_id)->toBeNull();

            // Verify gift-list pivot is cleaned up
            expect(DB::table('gift_list')->count())->toBe(0);

            // Verify sessions are deleted
            expect(DB::table('sessions')->where('user_id', $userToDelete->id)->count())->toBe(0);
        });

        it('does not affect other users data', function () {
            $userToDelete = User::factory()->create();
            $otherUser = User::factory()->create();

            // Other user's data
            $otherList = GiftList::factory()->create(['creator_id' => $otherUser->id]);
            $otherGift = Gift::factory()->create(['user_id' => $otherUser->id]);
            $otherGift->lists()->attach($otherList->id);

            // User to delete's data
            Gift::factory()->create(['user_id' => $userToDelete->id]);
            GiftList::factory()->create(['creator_id' => $userToDelete->id]);

            $action = new DeleteAccountAction;
            $action->execute($userToDelete);

            // Other user's data should be intact
            expect(User::find($otherUser->id))->not->toBeNull();
            expect(GiftList::find($otherList->id))->not->toBeNull();
            expect(Gift::find($otherGift->id))->not->toBeNull();
            expect(DB::table('gift_list')->where('list_id', $otherList->id)->count())->toBe(1);
        });
    });

});
