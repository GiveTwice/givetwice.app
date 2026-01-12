@extends('layouts.app')

@section('title', __('List invitation'))

@section('robots', 'noindex, nofollow')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-teal-100 to-teal-200 mb-4">
            <x-icons.users class="w-10 h-10 text-teal-600" />
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
            {{ __('You\'re invited!') }}
        </h1>
        <p class="text-gray-500 mt-2 text-lg">
            {{ __(':name wants to collaborate with you.', ['name' => $inviter->name]) }}
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-cream-400 overflow-hidden">

        <div class="p-6 sm:p-8">
            <div class="flex items-start gap-4">
                @if($inviter->hasProfileImage())
                    <img src="{{ $inviter->getProfileImageUrl('thumb') }}" alt="{{ $inviter->name }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-coral-400 to-coral-500 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold">{{ $inviter->getInitials() }}</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">{{ $inviter->name }}</p>
                    <p class="text-sm text-gray-500">{{ __('invited you to collaborate on:') }}</p>
                </div>
            </div>

            <div class="mt-6 p-4 bg-teal-50 rounded-xl border border-teal-200">
                <p class="font-bold text-gray-900 text-lg">{{ $list->name }}</p>
                @if($list->description)
                    <p class="text-gray-600 text-sm mt-1">{{ $list->description }}</p>
                @endif
            </div>

            <div class="mt-6 p-4 bg-cream-50 rounded-xl">
                <p class="text-sm text-gray-600">
                    {{ __('As a collaborator, you\'ll be able to add, edit, and manage gifts on this list together.') }}
                </p>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
            <div class="flex flex-col sm:flex-row gap-3">
                <form action="{{ route('lists.invitation.decline', ['locale' => app()->getLocale(), 'token' => $invitation->token]) }}" method="POST" class="sm:order-1">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                        {{ __('Decline') }}
                    </button>
                </form>

                <form action="{{ route('lists.invitation.accept', ['locale' => app()->getLocale(), 'token' => $invitation->token]) }}" method="POST" class="flex-1 sm:order-2">
                    @csrf
                    <button type="submit" class="w-full px-6 py-3 bg-teal-500 text-white rounded-xl hover:bg-teal-600 transition-colors font-semibold shadow-sm hover:shadow">
                        {{ __('Accept invitation') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <p class="text-center text-sm text-gray-400 mt-6">
        {{ __('This invitation expires :date.', ['date' => $invitation->expires_at->diffForHumans()]) }}
    </p>

</div>
@endsection
