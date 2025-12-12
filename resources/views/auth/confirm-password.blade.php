@extends('layouts.guest')

@section('title', __('Confirm Password'))

@section('content')
<div class="bg-white p-8 rounded-2xl shadow-sm border border-cream-200">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-coral-100 text-coral-500 rounded-2xl text-2xl mb-4 transform rotate-3">
            &#128272;
        </div>
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Confirm Password') }}</h2>
        <p class="text-gray-600 mt-2">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </p>
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

    <form method="POST" action="{{ url('/user/confirm-password') }}">
        @csrf

        <div class="mb-6">
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input type="password" name="password" id="password" required autofocus class="form-input">
        </div>

        <button type="submit" class="w-full bg-coral-500 text-white py-3 px-4 rounded-xl hover:bg-coral-600 font-semibold transition-colors shadow-sm">
            {{ __('Confirm') }}
        </button>
    </form>
</div>
@endsection
