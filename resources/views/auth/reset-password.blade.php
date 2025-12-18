@extends('layouts.guest')

@section('title', __('Reset Password'))

@section('robots', 'noindex, nofollow')

@section('content')
<div class="bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl text-2xl mb-4 transform -rotate-3">
            &#128274;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Reset Password') }}</h2>
        <p class="text-gray-600 mt-2">{{ __('Enter your new password below') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert-error mb-6 text-sm">
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-start gap-2">
                            <span class="text-red-400 mt-0.5">&times;</span>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ url('/reset-password') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email ?? '') }}" required autofocus class="form-input">
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <input type="password" name="password" id="password" required class="form-input">
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="form-input">
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Reset Password') }}
        </button>
    </form>
</div>
@endsection
