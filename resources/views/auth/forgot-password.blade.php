@extends('layouts.guest')

@section('title', __('Forgot Password'))

@section('robots', 'noindex, nofollow')

@section('content')
<div class="bg-white p-8 sm:p-10 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-sunny-100 text-sunny-600 rounded-2xl text-2xl mb-4 transform rotate-3">
            &#128273;
        </div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Forgot Password') }}</h1>
        <p class="text-gray-600 mt-2">
            {{ __('Enter your email address and we will send you a password reset link.') }}
        </p>
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

    <form method="POST" action="{{ url('/forgot-password') }}">
        @csrf

        <div class="mb-6">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="form-input">
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
