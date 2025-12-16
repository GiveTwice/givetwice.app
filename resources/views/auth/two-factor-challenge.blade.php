@extends('layouts.guest')

@section('title', __('Two-factor authentication'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200" x-data="{ useRecoveryCode: false }">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl mb-4">
            <x-icons.shield-check class="w-7 h-7" />
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Two-factor authentication') }}</h2>
        <p class="text-gray-600 mt-1" x-show="!useRecoveryCode">{{ __('Enter the authentication code from your authenticator app.') }}</p>
        <p class="text-gray-600 mt-1" x-show="useRecoveryCode" x-cloak>{{ __('Enter one of your emergency recovery codes.') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert-error mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Authenticator Code Form --}}
    <form method="POST" action="{{ url('/two-factor-challenge') }}" x-show="!useRecoveryCode">
        @csrf

        <div class="mb-6">
            <label for="code" class="form-label">{{ __('Authentication code') }}</label>
            <input
                type="text"
                name="code"
                id="code"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
                maxlength="6"
                required
                autofocus
                class="form-input font-mono text-xl tracking-[0.5em] text-center"
                placeholder="000000"
            >
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Verify') }}
        </button>
    </form>

    {{-- Recovery Code Form --}}
    <form method="POST" action="{{ url('/two-factor-challenge') }}" x-show="useRecoveryCode" x-cloak>
        @csrf

        <div class="mb-6">
            <label for="recovery_code" class="form-label">{{ __('Recovery code') }}</label>
            <input
                type="text"
                name="recovery_code"
                id="recovery_code"
                required
                class="form-input font-mono text-center"
                placeholder="xxxxx-xxxxx"
            >
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Verify') }}
        </button>
    </form>

    <div class="mt-6 text-center">
        <button
            type="button"
            x-on:click="useRecoveryCode = !useRecoveryCode"
            class="text-sm text-gray-600 hover:text-gray-800"
        >
            <span x-show="!useRecoveryCode">{{ __('Use a recovery code') }}</span>
            <span x-show="useRecoveryCode" x-cloak>{{ __('Use authentication code') }}</span>
        </button>
    </div>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ url('/logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                {{ __('Cancel and log out') }}
            </button>
        </form>
    </div>
</div>
@endsection
