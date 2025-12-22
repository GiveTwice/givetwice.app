<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $locale = $request->user()->locale_preference ?? config('app.locale', 'en');

        return redirect()->intended("/{$locale}/verify-email");
    }
}
