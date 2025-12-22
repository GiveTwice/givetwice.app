<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse(Request $request): Response
    {
        $locale = $request->user()->locale_preference ?? config('app.locale', 'en');

        return redirect()->intended("/{$locale}/verify-email");
    }
}
