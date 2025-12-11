@extends('layouts.guest')

@section('title', __('Forgot Password'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-sunny-100 text-sunny-600 rounded-2xl text-2xl mb-4 transform rotate-3">
            &#128273;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Forgot Password') }}</h2>
        <p class="text-gray-600 mt-2">
            {{ __('Enter your email address and we will send you a password reset link.') }}
        </p>
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

    <form method="POST" action="{{ url('/forgot-password') }}">
        @csrf

        <div class="mb-6">
            <label for="email" class="block text-gray-700 mb-2 font-medium">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-3 border border-cream-200 rounded-xl focus:outline-none focus:border-coral-400 focus:ring-2 focus:ring-coral-100 transition-colors">
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Send Password Reset Link') }}
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="inline-flex items-center text-coral-600 hover:text-coral-700 font-medium">
            <span class="mr-2">&larr;</span> {{ __('Back to Login') }}
        </a>
    </div>
</div>
@endsection
