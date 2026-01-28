<?php

use App\Actions\ClaimGiftAction;
use App\Exceptions\Claim\CannotClaimOwnGiftException;
use App\Mail\FriendDigestMail;
use App\Models\Claim;
use App\Models\FollowedList;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
});

describe('Friends Feature', function () {

    describe('followed list creation on claim', function () {
        it('creates followed_list when registered user claims a gift', function () {
            $creator = User::factory()->create();
            $claimer = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $gift = Gift::factory()->create(['user_id' => $creator->id]);
            $gift->lists()->attach($list->id);

            app(ClaimGiftAction::class)->execute($gift, $claimer);

            expect(FollowedList::where('user_id', $claimer->id)->where('list_id', $list->id)->exists())->toBeTrue();
        });

        it('does not create followed_list when user claims own gift', function () {
            $user = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            $gift = Gift::factory()->create(['user_id' => $user->id]);
            $gift->lists()->attach($list->id);

            expect(fn () => app(ClaimGiftAction::class)->execute($gift, $user))
                ->toThrow(CannotClaimOwnGiftException::class);

            expect(FollowedList::where('user_id', $user->id)->count())->toBe(0);
        });

        it('does not create followed_list when user is already a collaborator', function () {
            $creator = User::factory()->create();
            $collaborator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach([$creator->id, $collaborator->id]);

            $gift = Gift::factory()->create(['user_id' => $creator->id]);
            $gift->lists()->attach($list->id);

            app(ClaimGiftAction::class)->execute($gift, $collaborator);

            expect(FollowedList::where('user_id', $collaborator->id)->where('list_id', $list->id)->exists())->toBeFalse();
        });

        it('does not create duplicate followed_list entries', function () {
            $creator = User::factory()->create();
            $claimer = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $gift1 = Gift::factory()->create(['user_id' => $creator->id]);
            $gift1->lists()->attach($list->id);

            $gift2 = Gift::factory()->create(['user_id' => $creator->id]);
            $gift2->lists()->attach($list->id);

            app(ClaimGiftAction::class)->execute($gift1, $claimer);
            app(ClaimGiftAction::class)->execute($gift2, $claimer);

            expect(FollowedList::where('user_id', $claimer->id)->where('list_id', $list->id)->count())->toBe(1);
        });
    });

    describe('followed list creation on email verification', function () {
        it('creates followed_lists when anonymous user verifies email', function () {
            $creator = User::factory()->create();
            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $gift = Gift::factory()->create(['user_id' => $creator->id]);
            $gift->lists()->attach($list->id);

            $claim = Claim::factory()->create([
                'gift_id' => $gift->id,
                'user_id' => null,
                'claimer_email' => 'newuser@example.com',
                'confirmed_at' => now(),
            ]);

            $newUser = User::factory()->create([
                'email' => 'newuser@example.com',
                'email_verified_at' => null,
            ]);

            $newUser->markEmailAsVerified();
            event(new Verified($newUser));

            expect($claim->fresh()->user_id)->toBe($newUser->id);
            expect(FollowedList::where('user_id', $newUser->id)->where('list_id', $list->id)->exists())->toBeTrue();
        });
    });

    describe('friends page', function () {
        it('shows friends page to authenticated user', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/en/friends')
                ->assertOk()
                ->assertSee(__("Friends' wishlists"));
        });

        it('redirects guest to login', function () {
            $this->get('/en/friends')
                ->assertRedirect('/en/login');
        });

        it('shows followed lists on friends page', function () {
            $user = User::factory()->create();
            $creator = User::factory()->create(['name' => 'John Creator']);

            $list = GiftList::factory()->create([
                'creator_id' => $creator->id,
                'name' => 'Johns Birthday List',
            ]);
            $list->users()->attach($creator->id);

            FollowedList::create([
                'user_id' => $user->id,
                'list_id' => $list->id,
            ]);

            $this->actingAs($user)
                ->get('/en/friends')
                ->assertOk()
                ->assertSee('Johns Birthday List')
                ->assertSee('John Creator');
        });

        it('does not show lists where user is collaborator', function () {
            $user = User::factory()->create();
            $creator = User::factory()->create();

            $list = GiftList::factory()->create([
                'creator_id' => $creator->id,
                'name' => 'Collaborated List',
            ]);
            $list->users()->attach([$creator->id, $user->id]);

            FollowedList::create([
                'user_id' => $user->id,
                'list_id' => $list->id,
            ]);

            $this->actingAs($user)
                ->get('/en/friends')
                ->assertOk()
                ->assertDontSee('Collaborated List');
        });

        it('shows empty state when no followed lists', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/en/friends')
                ->assertOk()
                ->assertSee(__("No friends' wishlists yet"));
        });
    });

    describe('notification toggles', function () {
        it('toggles per-list notifications via JSON API', function () {
            $user = User::factory()->create();
            $creator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $followedList = FollowedList::create([
                'user_id' => $user->id,
                'list_id' => $list->id,
                'notifications' => true,
            ]);

            $this->actingAs($user)
                ->postJson("/en/friends/{$followedList->id}/notifications")
                ->assertOk()
                ->assertJson(['success' => true, 'notifications' => false]);

            expect($followedList->fresh()->notifications)->toBeFalse();

            $this->actingAs($user)
                ->postJson("/en/friends/{$followedList->id}/notifications")
                ->assertOk()
                ->assertJson(['success' => true, 'notifications' => true]);

            expect($followedList->fresh()->notifications)->toBeTrue();
        });

        it('prevents toggling another users followed list', function () {
            $user = User::factory()->create();
            $otherUser = User::factory()->create();
            $creator = User::factory()->create();

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            $followedList = FollowedList::create([
                'user_id' => $otherUser->id,
                'list_id' => $list->id,
            ]);

            $this->actingAs($user)
                ->postJson("/en/friends/{$followedList->id}/notifications")
                ->assertForbidden();
        });

        it('toggles global notifications via JSON API', function () {
            $user = User::factory()->create(['friend_notifications_enabled' => true]);

            $this->actingAs($user)
                ->postJson('/en/friends/notifications')
                ->assertOk()
                ->assertJson(['success' => true, 'enabled' => false]);

            expect($user->fresh()->friend_notifications_enabled)->toBeFalse();

            $this->actingAs($user)
                ->postJson('/en/friends/notifications')
                ->assertOk()
                ->assertJson(['success' => true, 'enabled' => true]);

            expect($user->fresh()->friend_notifications_enabled)->toBeTrue();
        });
    });

    describe('friend digest command', function () {
        it('sends digest email when there are changes', function () {
            $creator = User::factory()->create();
            $follower = User::factory()->create([
                'email_verified_at' => now(),
                'friend_notifications_enabled' => true,
                'last_friend_digest_at' => now()->subDays(2),
            ]);

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            FollowedList::create([
                'user_id' => $follower->id,
                'list_id' => $list->id,
                'notifications' => true,
            ]);

            $gift = Gift::factory()->create([
                'user_id' => $creator->id,
                'created_at' => now()->subDay(),
            ]);
            $gift->lists()->attach($list->id);

            $this->artisan('friends:send-digest')
                ->assertExitCode(0);

            Mail::assertQueued(FriendDigestMail::class, function ($mail) use ($follower) {
                return $mail->user->id === $follower->id;
            });
        });

        it('does not send digest when no changes', function () {
            $creator = User::factory()->create();
            $follower = User::factory()->create([
                'email_verified_at' => now(),
                'friend_notifications_enabled' => true,
                'last_friend_digest_at' => now(),
            ]);

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            FollowedList::create([
                'user_id' => $follower->id,
                'list_id' => $list->id,
                'notifications' => true,
            ]);

            $gift = Gift::factory()->create([
                'user_id' => $creator->id,
                'created_at' => now()->subDays(2),
            ]);
            $gift->lists()->attach($list->id);

            $this->artisan('friends:send-digest')
                ->assertExitCode(0);

            Mail::assertNotQueued(FriendDigestMail::class);
        });

        it('does not send digest when global notifications disabled', function () {
            $creator = User::factory()->create();
            $follower = User::factory()->create([
                'email_verified_at' => now(),
                'friend_notifications_enabled' => false,
                'last_friend_digest_at' => now()->subDays(2),
            ]);

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            FollowedList::create([
                'user_id' => $follower->id,
                'list_id' => $list->id,
                'notifications' => true,
            ]);

            $gift = Gift::factory()->create([
                'user_id' => $creator->id,
                'created_at' => now()->subDay(),
            ]);
            $gift->lists()->attach($list->id);

            $this->artisan('friends:send-digest')
                ->assertExitCode(0);

            Mail::assertNotQueued(FriendDigestMail::class);
        });

        it('does not send digest when per-list notifications disabled', function () {
            $creator = User::factory()->create();
            $follower = User::factory()->create([
                'email_verified_at' => now(),
                'friend_notifications_enabled' => true,
                'last_friend_digest_at' => now()->subDays(2),
            ]);

            $list = GiftList::factory()->create(['creator_id' => $creator->id]);
            $list->users()->attach($creator->id);

            FollowedList::create([
                'user_id' => $follower->id,
                'list_id' => $list->id,
                'notifications' => false,
            ]);

            $gift = Gift::factory()->create([
                'user_id' => $creator->id,
                'created_at' => now()->subDay(),
            ]);
            $gift->lists()->attach($list->id);

            $this->artisan('friends:send-digest')
                ->assertExitCode(0);

            Mail::assertNotQueued(FriendDigestMail::class);
        });

        it('does not send digest for own lists', function () {
            $user = User::factory()->create([
                'email_verified_at' => now(),
                'friend_notifications_enabled' => true,
                'last_friend_digest_at' => now()->subDays(2),
            ]);

            $list = GiftList::factory()->create(['creator_id' => $user->id]);
            $list->users()->attach($user->id);

            FollowedList::create([
                'user_id' => $user->id,
                'list_id' => $list->id,
                'notifications' => true,
            ]);

            $gift = Gift::factory()->create([
                'user_id' => $user->id,
                'created_at' => now()->subDay(),
            ]);
            $gift->lists()->attach($list->id);

            $this->artisan('friends:send-digest')
                ->assertExitCode(0);

            Mail::assertNotQueued(FriendDigestMail::class);
        });
    });

});
