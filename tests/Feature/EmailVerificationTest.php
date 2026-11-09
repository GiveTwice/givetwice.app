<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

function verificationUrl(User $user): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'locale' => 'en',
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]);
}

describe('Email verification', function () {
    it('verifies the signed-in user from their own link', function () {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(verificationUrl($user))
            ->assertRedirect(route('dashboard.locale', ['locale' => 'en']));

        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
        Event::assertDispatched(Verified::class);
    });

    it('refuses a link that belongs to another user', function () {
        $owner = User::factory()->unverified()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->get(verificationUrl($owner))
            ->assertForbidden();

        expect($owner->fresh()->hasVerifiedEmail())->toBeFalse();
    });
});
