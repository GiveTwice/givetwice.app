<?php

use App\Actions\AcceptListInvitationAction;
use App\Actions\ClaimGiftAction;
use App\Actions\CreateListAction;
use App\Actions\InviteToListAction;
use App\Actions\ReserveGiftAction;
use App\Mail\FriendDigestMail;
use App\Mail\ListInvitationMail;
use App\Mail\ReservationMail;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

it('replays the report week with the intended email count for each persona', function () {
    Mail::fake();
    Notification::fake();
    Queue::fake()->except([\App\Jobs\SendListUpdate::class]);
    $this->travelTo(now()->setDate(2026, 11, 2)->setTime(8, 0));
    $mom = User::factory()->create(['locale_preference' => 'nl']);
    $partner = User::factory()->create(['locale_preference' => 'nl']);
    $lien = User::factory()->create(['locale_preference' => 'nl']);
    $mom->sendEmailVerificationNotification();
    $list = app(CreateListAction::class)->execute($mom, 'Noah', isDefault: true);
    $invitation = app(InviteToListAction::class)->execute($list, $mom, $partner->email);
    app(AcceptListInvitationAction::class)->execute($invitation->token, $partner);
    $liensList = app(CreateListAction::class)->execute($lien, 'Lien', isDefault: true);
    $liensGift = Gift::factory()->skipped()->create(['user_id' => $lien->id]);
    $liensList->gifts()->attach($liensGift);
    $mom->followListIfEligible($liensList);
    $lien->followListIfEligible($list);
    $this->artisan('friends:send-digest')->assertSuccessful();
    $gifts = [];
    $grandma = null;
    foreach (range(2, 8) as $day) {
        $this->travelTo(now()->setDate(2026, 11, $day)->setTime(19, 0));
        $this->actingAs($mom);
        foreach (range(1, [2 => 3, 3 => 3, 4 => 2][$day] ?? 1) as $number) {
            if ($day > 4) {
                break;
            }
            $gift = Gift::factory()->skipped()->create(['user_id' => $mom->id]);
            $list->gifts()->attach($gift);
            $gifts[] = $gift;
        }
        if ($day === 2) {
            $grandma = app(ReserveGiftAction::class)->execute($gifts[0], 'grandma@example.test');
            app(ClaimGiftAction::class)->execute($gifts[1], $lien);
        }
        if ($day === 3) {
            $grandma->delete();
        }
        if ($day === 5) {
            $this->actingAs($partner);
            $gifts[3]->update(['owner_note' => 'Blue']);
            $gifts[4]->update(['title' => 'Updated gift']);
        }
        if ($day === 6) {
            $this->actingAs($lien);
            $liensGift->update(['title' => 'Updated idea']);
        }
        $this->travel(11)->minutes();
        $this->artisan('friends:send-digest')->assertSuccessful();
        $this->artisan('friends:send-digest')->assertSuccessful();
    }
    Notification::assertSentTo($mom, VerifyEmail::class);
    $totals = [];
    foreach (['mom' => $mom->email, 'partner' => $partner->email, 'grandma' => 'grandma@example.test', 'lien' => $lien->email] as $persona => $email) {
        $total = $persona === 'mom' ? 1 : 0;
        foreach ([FriendDigestMail::class, ListInvitationMail::class, ReservationMail::class] as $type) {
            $total += Mail::queued($type, fn ($mail) => $mail->hasTo($email))->count();
            $total += Mail::sent($type, fn ($mail) => $mail->hasTo($email))->count();
        }
        $totals[$persona] = $total;
    }
    expect($totals)->toBe(['mom' => 3, 'partner' => 4, 'grandma' => 1, 'lien' => 5]);
    Mail::assertSent(FriendDigestMail::class, fn ($mail) => $mail->locale === 'nl');
});
