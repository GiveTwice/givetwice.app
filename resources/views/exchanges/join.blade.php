@extends('layouts.app')

@section('title', __('Join :name', ['name' => $exchange->name]))

@section('content')
<div class="max-w-lg mx-auto">

    <div class="text-center pt-6 pb-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">🎲 {{ __('Join :name', ['name' => $exchange->name]) }}</h1>
        <p class="text-gray-600">{{ __('You\'ve been invited to a gift exchange!') }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-6">

        @if(session('success'))
            <div class="alert-success mb-6">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap gap-4 mb-6 text-sm text-gray-500 justify-center">
            @if($exchange->event_date)
                <span>📅 {{ $exchange->event_date->format('M j, Y') }}</span>
            @endif
            @if($exchange->formatBudget())
                <span>💰 {{ $exchange->formatBudget() }}</span>
            @endif
            <span>👥 {{ $exchange->participants->count() }} {{ __('participants') }}</span>
        </div>

        <form method="POST" action="{{ route('exchanges.join.store', ['locale' => app()->getLocale(), 'joinToken' => $joinToken]) }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="name" class="form-label">{{ __('Your name') }}</label>
                    <input type="text" name="name" id="name" class="form-input"
                           value="{{ old('name', auth()->user()?->name) }}" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="form-label">{{ __('Your email') }}</label>
                    <input type="email" name="email" id="email" class="form-input"
                           value="{{ old('email', auth()->user()?->email) }}" required>
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary w-full">{{ __('Join group') }}</button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-5 border border-coral-100 text-center mb-8">
        <p class="text-gray-600 text-sm">
            ❤️ {{ __('When someone buys from a wishlist, we donate our commission. You pay nothing extra.') }}
            <a href="{{ route('about', ['locale' => app()->getLocale()]) }}" class="text-coral-500 hover:text-coral-600 font-medium">{{ __('How it works') }} →</a>
        </p>
    </div>
</div>
@endsection
