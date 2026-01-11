@extends('layouts.guest')

@section('title', __('Login'))

@section('description', __('meta.login'))

@section('robots', 'noindex, nofollow')

@section('content')
<div class="bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-cream-200" x-data="{ showEmailForm: {{ old('email') || $errors->any() ? 'true' : 'false' }} }">
    {{-- Header section with generous spacing --}}
    <div class="flex items-center gap-4 mb-8">
        <div class="flex-shrink-0 w-12 h-12 bg-coral-100 text-coral-500 rounded-xl text-2xl flex items-center justify-center transform -rotate-3">
            &#128075;
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ __('Welcome back!') }}</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ __('Sign in to manage your wishlists') }}</p>
        </div>
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

    {{-- Social login options --}}
    <template x-if="!showEmailForm">
        <div class="space-y-3">
            <a href="{{ route('auth.google', ['locale' => app()->getLocale()]) }}"
               class="flex items-center justify-center w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-colors font-medium text-gray-700">
                <img src="/icons/logo-google.svg" alt="Google" class="w-5 h-5 mr-3">
                {{ __('Continue with Google') }}
            </a>

            <button type="button"
                    x-on:click="showEmailForm = true"
                    class="flex items-center justify-center w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-colors font-medium text-gray-700">
                <svg class="w-5 h-5 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ __('Continue with email') }}
            </button>
        </div>
    </template>

    {{-- Email login form (hidden initially) --}}
    <template x-if="showEmailForm">
        <div>
            {{-- Back button --}}
            <button type="button"
                    x-on:click="showEmailForm = false"
                    class="flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('Back') }}
            </button>

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input type="email"
                           name="email"
                           id="email"
                           value="{{ old('email') }}"
                           required
                           x-init="$el.focus()"
                           class="form-input">
                </div>

                <div>
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input type="password"
                           name="password"
                           id="password"
                           required
                           class="form-input">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-coral-500 border-cream-300 rounded focus:ring-coral-400">
                        <label for="remember" class="ml-2 text-gray-600 text-sm">{{ __('Remember me') }}</label>
                    </div>
                    <a href="{{ url('/' . app()->getLocale() . '/forgot-password') }}" class="text-sm text-coral-600 hover:text-coral-700">
                        {{ __('Forgot password?') }}
                    </a>
                </div>

                <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
                    {{ __('Login') }}
                </button>
            </form>
        </div>
    </template>

    @if(config('app.allow_registration'))
        {{-- Register link - generous top spacing --}}
        <div class="mt-8 pt-6 border-t border-cream-200 text-center">
            <span class="text-gray-600 text-sm">{{ __("Don't have an account?") }}</span>
            <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-coral-600 hover:text-coral-700 font-medium ml-1.5 text-sm">
                {{ __('Sign Up') }}
            </a>
        </div>
    @endif
</div>
@endsection
