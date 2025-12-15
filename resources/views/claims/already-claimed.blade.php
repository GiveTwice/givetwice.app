@extends('layouts.app')

@section('title', __('Gift Already Claimed'))

@section('content')

<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-cream-200/60 shadow-sm overflow-hidden">

        <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center text-center">

                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-sunny-50 to-sunny-100 border border-sunny-200/50 flex items-center justify-center mb-5">
                    <x-icons.warning class="w-9 h-9 text-sunny-500" />
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
                    <x-icons.info-circle class="w-4 h-4 text-gray-500" />
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
            <x-icons.home class="w-5 h-5" />
            {{ __('Go to Homepage') }}
        </a>
    </div>
</div>
@endsection
