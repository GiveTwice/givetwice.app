@extends('layouts.app')

@section('title', __('Gift Already Claimed'))

@section('content')
<div class="max-w-md mx-auto">
    {{-- Warning Card --}}
    <div class="bg-white rounded-2xl border border-cream-200 overflow-hidden">
        {{-- Warning Header --}}
        <div class="bg-gradient-to-br from-sunny-50 to-sunny-100 p-8 text-center border-b border-sunny-200">
            <div class="w-20 h-20 bg-sunny-400 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-sunny-800 mb-2">{{ __('Gift Already Claimed') }}</h1>
            <p class="text-sunny-700 max-w-sm mx-auto">
                {{ __('Someone else has already claimed this gift while you were confirming.') }}
            </p>
        </div>

        {{-- Actions --}}
        <div class="p-6">
            <div class="bg-cream-50 border border-cream-200 rounded-xl p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-gray-600">
                        <p>{{ __('Don\'t worry! There might be other gifts available on the list. Go back and choose another one.') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <a href="{{ url('/' . app()->getLocale()) }}" class="btn-primary justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('Go to Homepage') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
