<?php

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

it('returns the session expiry recovery view with the correct status', function () {
    config(['app.debug' => false]);
    $recoveryViewRendered = false;
    View::composer('errors::419', function () use (&$recoveryViewRendered): void {
        $recoveryViewRendered = true;
    });

    Route::get('/nl/session-expiry-preview', function (): never {
        throw new TokenMismatchException;
    });

    $this->get('/nl/session-expiry-preview')
        ->assertStatus(419);

    expect($recoveryViewRendered)->toBeTrue();
});
