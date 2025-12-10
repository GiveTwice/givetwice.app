@extends('layouts.guest')

@section('title', __('Login'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">{{ __('Login') }}</h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/login') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4 flex items-center">
            <input type="checkbox" name="remember" id="remember" class="mr-2">
            <label for="remember" class="text-gray-700">{{ __('Remember me') }}</label>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            {{ __('Login') }}
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ url('/' . app()->getLocale() . '/forgot-password') }}" class="text-blue-600 hover:underline">
            {{ __('Forgot your password?') }}
        </a>
    </div>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <a href="{{ route('auth.google', ['locale' => app()->getLocale()]) }}"
               class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                {{ __('Google') }}
            </a>
            <a href="{{ route('auth.facebook', ['locale' => app()->getLocale()]) }}"
               class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                {{ __('Facebook') }}
            </a>
        </div>
    </div>

    <div class="mt-6 text-center">
        <span class="text-gray-600">{{ __("Don't have an account?") }}</span>
        <a href="{{ url('/' . app()->getLocale() . '/register') }}" class="text-blue-600 hover:underline">
            {{ __('Register') }}
        </a>
    </div>
</div>
@endsection
