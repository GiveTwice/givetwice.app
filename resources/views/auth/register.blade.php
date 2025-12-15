@extends('layouts.guest')

@section('title', __('Register'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-sunny-100 text-sunny-600 rounded-2xl text-2xl mb-4 transform rotate-3">
            &#127873;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Create your account') }}</h2>
        <p class="text-gray-600 mt-1">{{ __('Start creating wishlists in minutes') }}</p>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/register') }}">
        @csrf

        <div class="mb-4">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus class="form-input">
        </div>

        <div class="mb-4">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="form-input">
        </div>

        <div class="mb-6">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required class="form-input">
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Create Account') }}
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
                <img src="/icons/logo-google.svg" alt="" class="w-5 h-5 mr-2">
                {{ __('Google') }}
            </a>
            <a href="{{ route('auth.facebook', ['locale' => app()->getLocale()]) }}"
               class="flex items-center justify-center px-4 py-3 border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-cream-300 transition-colors">
                <img src="/icons/logo-facebook.svg" alt="" class="w-5 h-5 mr-2">
                {{ __('Facebook') }}
            </a>
        </div>
    </div>

    <div class="mt-6 text-center">
        <span class="text-gray-600">{{ __('Already have an account?') }}</span>
        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-coral-600 hover:text-coral-700 font-medium ml-1">
            {{ __('Login') }}
        </a>
    </div>

    <div class="mt-6 pt-6 border-t border-cream-200">
        <p class="text-center text-sm text-gray-500 flex items-center justify-center">
            <span class="text-coral-500 mr-2">&#10084;</span>
            {{ __('All affiliate revenue goes to charity') }}
        </p>
    </div>
</div>
@endsection
