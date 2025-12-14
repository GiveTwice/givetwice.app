@extends('layouts.app')

@section('title', __('Gift Already Claimed'))

@section('content')

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">

        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center">

                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-sunny-50 to-sunny-100 border border-sunny-200/50 flex items-center justify-center mb-5">
                    <svg class="w-9 h-9 text-sunny-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                    {{ __('Gift Already Claimed') }}
                </h1>
                <p class="text-gray-500 max-w-sm">
                    {{ __('Someone else has already claimed this gift while you were confirming.') }}
                </p>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-5 bg-cream-50/50 border-t border-cream-100">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-cream-200 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ __('No worries!') }}</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('There might be other gifts available on the list. Go back and choose another one.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 text-center">
        <a href="{{ url('/' . app()->getLocale()) }}" class="btn-primary inline-flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ __('Go to Homepage') }}
        </a>
    </div>
</div>
@endsection
