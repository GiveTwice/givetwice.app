<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request, string $locale): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('dashboard.locale', ['locale' => $locale]);
    }
}
