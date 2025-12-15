@extends('layouts.app')

@section('title', __('Claim Gift'))

@php
    $siteName = '';
    if ($gift->url) {
        $parsedUrl = parse_url($gift->url);
        $host = $parsedUrl['host'] ?? '';
        $siteName = preg_replace('/^www\./', '', $host);
    }
@endphp

@section('content')

<div class="mb-6 bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">

    <div class="p-5 sm:p-6">
        <div class="flex items-start gap-4 sm:gap-5">

            <div class="flex-shrink-0 w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-cream-50 border border-cream-200 overflow-hidden">
                @if($gift->hasImage())
                    <img src="{{ $gift->getImageUrl('thumb') }}" alt="{{ $gift->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-cream-400">
                        <x-icons.image-placeholder class="w-8 h-8" />
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                @if($list)
                    <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="text-coral-500 text-xs sm:text-sm tracking-wide uppercase font-medium hover:text-coral-600 transition-colors">
                        {{ __('From :name\'s wishlist', ['name' => $list->user->name]) }}
                    </a>
                @else
                    <p class="text-coral-500 text-xs sm:text-sm tracking-wide uppercase font-medium">
                        {{ __('Claim Gift') }}
                    </p>
                @endif
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 line-clamp-2 mt-0.5">
                    {{ $gift->title ?: __('Untitled gift') }}
                </h1>
                @if($gift->hasPrice())
                    <p class="text-coral-600 font-bold mt-1">{{ $gift->formatPrice() }}</p>
                @endif
                @if($siteName)
                    <p class="text-gray-400 text-sm mt-1">{{ $siteName }}</p>
                @endif
            </div>

            <div class="flex-shrink-0 hidden sm:block">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-teal-500 rounded-full">
                    <span class="w-5 h-5 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold text-white">2</span>
                    <span class="text-sm font-medium text-white">{{ __('Claim') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="px-5 sm:px-6 py-3 bg-cream-50/50 border-t border-cream-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 text-sm">
                <span class="flex items-center gap-1.5">
                    <span class="w-6 h-6 bg-coral-100 text-coral-400 rounded-full flex items-center justify-center text-xs font-bold">
                        <x-icons.checkmark class="w-3.5 h-3.5" />
                    </span>
                    <span class="text-gray-400 font-medium">{{ __('Browse') }}</span>
                </span>
                <x-icons.chevron-right class="w-4 h-4 text-gray-300" />
                <span class="flex items-center gap-1.5">
                    <span class="w-6 h-6 bg-teal-500 text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">2</span>
                    <span class="text-gray-900 font-semibold">{{ __('Claim') }}</span>
                </span>
                <x-icons.chevron-right class="w-4 h-4 text-gray-300" />
                <span class="flex items-center gap-1.5">
                    <span class="w-6 h-6 bg-cream-200 text-gray-400 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                    <span class="text-gray-400 font-medium">{{ __('Gift') }}</span>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-cream-200/60 overflow-hidden">

    <div class="px-6 py-5 border-b border-gray-100">
        <h2 class="text-xl font-bold text-gray-900">{{ __('Confirm your claim') }}</h2>
        <p class="text-gray-500 text-sm mt-1">{{ __('Enter your email and we\'ll send you a confirmation link.') }}</p>
    </div>

    <div class="p-6">
        <form action="{{ url('/' . app()->getLocale() . '/gifts/' . $gift->id . '/claim-anonymous') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                <div>
                    <label for="email" class="form-label">
                        {{ __('Email') }} <span class="text-coral-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="{{ __('your@email.com') }}"
                        class="form-input @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
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
            </div>

            <div class="mt-6 flex items-start gap-3 p-4 bg-teal-50 rounded-xl border border-teal-100">
                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <x-icons.lock class="w-4 h-4 text-teal-600" />
                </div>
                <div>
                    <p class="text-sm font-medium text-teal-800">{{ __('Your claim is anonymous') }}</p>
                    <p class="text-sm text-teal-700 mt-0.5">{{ __('The list owner will only see that someone claimed this gift, not who.') }}</p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between gap-4 pt-6 border-t border-gray-100">
                @if($list)
                    <a href="{{ url('/' . app()->getLocale() . '/view/' . $list->slug) }}" class="btn-cancel">
                        {{ __('Cancel') }}
                    </a>
                @else
                    <div></div>
                @endif
                <button type="submit" class="inline-flex items-center gap-2 bg-teal-500 text-white px-6 py-3 rounded-xl hover:bg-teal-600 transition-colors font-semibold shadow-sm">
                    <x-icons.checkmark class="w-5 h-5" />
                    {{ __('Claim This Gift') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mt-6 text-center">
    <p class="text-gray-500 text-sm">
        {{ __('Already have an account?') }}
        <a href="{{ url('/' . app()->getLocale() . '/login') }}" class="text-coral-600 hover:text-coral-700 hover:underline font-medium">{{ __('Login') }}</a>
        {{ __('to claim faster') }}
    </p>
</div>
@endsection
