@extends('layouts.guest')

@section('title', __('Forgot Password'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">{{ __('Forgot Password') }}</h2>

    <p class="text-gray-600 mb-6 text-center">
        {{ __('Enter your email address and we will send you a password reset link.') }}
    </p>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('/forgot-password') }}">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 mb-2">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            {{ __('Send Password Reset Link') }}
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-blue-600 hover:underline">
            {{ __('Back to Login') }}
        </a>
    </div>
</div>
@endsection
