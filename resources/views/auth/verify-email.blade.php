@extends('layouts.guest')

@section('title', __('Verify Email'))

@section('content')
<div class="bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold text-center mb-6">{{ __('Verify Your Email') }}</h2>

    <p class="text-gray-600 mb-6 text-center">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/email/verification-notification') }}">
        @csrf
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            {{ __('Resend Verification Email') }}
        </button>
    </form>

    <form method="POST" action="{{ url('/logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full text-gray-600 hover:text-gray-900">
            {{ __('Logout') }}
        </button>
    </form>
</div>
@endsection
