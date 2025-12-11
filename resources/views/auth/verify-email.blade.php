@extends('layouts.guest')

@section('title', __('Verify Email'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-sunny-100 text-sunny-600 rounded-2xl text-2xl mb-4 transform -rotate-3">
            &#9993;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Verify Your Email') }}</h2>
        <p class="text-gray-600 mt-2 leading-relaxed">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-4 bg-teal-50 border border-teal-200 text-teal-700 rounded-xl">
            <div class="flex items-center">
                <span class="text-teal-500 mr-2">&#10003;</span>
                {{ __('A new verification link has been sent to your email address.') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ url('/email/verification-notification') }}">
        @csrf
        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Resend Verification Email') }}
        </button>
    </form>

    <div class="mt-6 pt-6 border-t border-cream-200">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit" class="w-full text-gray-500 hover:text-gray-700 font-medium transition-colors">
                {{ __('Logout') }}
            </button>
        </form>
    </div>
</div>
@endsection
