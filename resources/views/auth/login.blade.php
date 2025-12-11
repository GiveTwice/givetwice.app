@extends('layouts.guest')

@section('title', __('Login'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-coral-100 text-coral-500 rounded-2xl text-2xl mb-4 transform -rotate-3">
            &#128075;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Welcome back!') }}</h2>
        <p class="text-gray-600 mt-1">{{ __('Sign in to manage your wishlists') }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2 font-medium">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2 font-medium">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-coral-500 border-cream-300 rounded focus:ring-coral-400">
                <label for="remember" class="ml-2 text-gray-600 text-sm">{{ __('Remember me') }}</label>
            </div>
            <a href="{{ url('/' . app()->getLocale() . '/forgot-password') }}" class="text-sm text-coral-600 hover:text-coral-700">
                {{ __('Forgot your password?') }}
            </a>
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Login') }}
        </button>
    </form>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-cream-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-3 bg-white text-gray-500">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <a href="{{ route('auth.google', ['locale' => app()->getLocale()]) }}"
               class="flex items-center justify-center px-4 py-3 border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-colors">
                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('Google') }}
            </a>
            <a href="{{ route('auth.facebook', ['locale' => app()->getLocale()]) }}"
               class="flex items-center justify-center px-4 py-3 border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
                    <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                </svg>
                {{ __('Facebook') }}
            </a>
        </div>
    </div>

    <div class="mt-6 text-center">
        <span class="text-gray-600">{{ __("Don't have an account?") }}</span>
        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-coral-600 hover:text-coral-700 font-medium ml-1">
            {{ __('Sign Up') }}
        </a>
    </div>
</div>
@endsection
