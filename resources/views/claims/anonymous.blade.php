@extends('layouts.app')

@section('title', __('Claim Gift'))

@section('content')
{{-- Breadcrumb --}}
<div class="breadcrumb">
    @if($list)
        <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="breadcrumb-link">{{ $list->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    @endif
    <span class="text-gray-900 font-medium">{{ __('Claim Gift') }}</span>
</div>

{{-- Header --}}
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Claim Gift') }}</h1>
    <p class="text-gray-600 mt-1">{{ __('Enter your email to claim this gift. We\'ll send you a confirmation link.') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    {{-- Form Section --}}
    <div class="lg:col-span-3">
        <div class="card">
            <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim-anonymous') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="mb-6">
                    <label for="email" class="form-label">
                        {{ __('Email') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="{{ __('your@email.com') }}"
                        class="form-input @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">{{ __('We\'ll send you a confirmation link to verify your claim.') }}</p>
                </div>

                {{-- Name (optional) --}}
                <div class="mb-6">
                    <label for="name" class="form-label">
                        {{ __('Name') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="{{ __('Your name') }}"
                        class="form-input @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Privacy note --}}
                <div class="info-box-success mb-6">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-teal-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="text-sm">
                            <p class="font-medium text-teal-800">{{ __('Your claim is anonymous') }}</p>
                            <p class="text-teal-700">{{ __('The list owner will only see that someone claimed this gift, not who.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="form-actions">
                    @if($list)
                        <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="btn-cancel">
                            {{ __('Cancel') }}
                        </a>
                    @endif
                    <button type="submit" class="inline-flex items-center gap-2 bg-teal-500 text-white px-5 py-2.5 rounded-xl hover:bg-teal-600 transition-colors font-medium shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        {{ __('Send Confirmation Email') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Login prompt --}}
        <div class="mt-6 text-center">
            <p class="text-gray-500">
                {{ __('Already have an account?') }}
                <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-coral-600 hover:text-coral-700 hover:underline font-medium">{{ __('Login') }}</a>
                {{ __('to claim faster') }}
            </p>
        </div>
    </div>

    {{-- Gift Preview Section --}}
    <div class="lg:col-span-2">
        <x-gift-preview :gift="$gift" variant="vertical" />
    </div>
</div>
@endsection
