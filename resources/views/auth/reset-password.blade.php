@extends('layouts.guest')

@section('title', __('Reset Password'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">{{ __('Reset Password') }}</h2>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <ul class="list-disc list-inside">
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
            <label for="email" class="block text-gray-700 mb-2">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email', $email ?? '') }}" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 mb-2">{{ __('New Password') }}</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 mb-2">{{ __('Confirm Password') }}</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            {{ __('Reset Password') }}
        </button>
    </form>
</div>
@endsection
