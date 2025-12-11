@extends('layouts.guest')

@section('title', __('Reset Password'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-teal-100 text-teal-600 rounded-2xl text-2xl mb-4 transform -rotate-3">
            &#128274;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Reset Password') }}</h2>
        <p class="text-gray-600 mt-2">{{ __('Enter your new password below') }}</p>
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

    <form method="POST" action="{{ url('/reset-password') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2 font-medium">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email ?? '') }}" required autofocus
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2 font-medium">{{ __('New Password') }}</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-gray-700 mb-2 font-medium">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Reset Password') }}
        </button>
    </form>
</div>
@endsection
