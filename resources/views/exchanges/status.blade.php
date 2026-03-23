@extends('layouts.app')

@section('title', $exchange->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <nav class="breadcrumb">
            <a href="{{ route('dashboard.locale', ['locale' => app()->getLocale()]) }}" class="breadcrumb-link">{{ __('Dashboard') }}</a>
            <span class="text-gray-400 mx-2">/</span>
            <span class="text-gray-600">{{ $exchange->name }}</span>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert-success mb-6">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $exchange->name }}</h1>
                <div class="flex flex-wrap gap-4 mt-2 text-sm text-gray-500">
                    <span>📅 {{ $exchange->event_date->format('M j, Y') }}</span>
                    @if($exchange->formatBudget())
                        <span>💰 {{ $exchange->formatBudget() }}</span>
                    @endif
                    <span>👥 {{ $exchange->participants->count() }} {{ __('participants') }}</span>
                </div>
            </div>
            @if($exchange->isDrawn())
                <span class="badge badge-success">{{ __('Names drawn') }}</span>
            @else
                <span class="badge badge-warning">{{ __('Draft') }}</span>
            @endif
        </div>

        @if($exchange->isDraft())
            <form method="POST" action="{{ route('exchanges.draw', ['locale' => app()->getLocale(), 'exchange' => $exchange->slug]) }}" class="mt-4">
                @csrf
                <button type="submit" class="btn-primary" onclick="return confirm('{{ __('Draw names and send invites? This can\'t be undone.') }}')">
                    🎲 {{ __('Draw names & send invites') }}
                </button>
            </form>
        @endif
    </div>

    {{-- Join link (draft only) --}}
    @if($exchange->isDraft() && $exchange->join_token)
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-6" x-data="{ copied: false }">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Invite link') }}</h2>
        <p class="text-gray-600 text-sm mb-4">{{ __('Share this link so people can add themselves to the group before you draw.') }}</p>
        <div class="flex gap-2">
            <input type="text" readonly value="{{ $exchange->getJoinUrl() }}" class="form-input flex-1 text-sm bg-gray-50" x-ref="joinUrl" @click="$el.select()">
            <button type="button"
                    @click="navigator.clipboard.writeText($refs.joinUrl.value); copied = true; setTimeout(() => copied = false, 2000)"
                    class="btn-secondary shrink-0 text-sm"
                    :class="copied && 'ring-2 ring-teal-300'">
                <span x-show="!copied">{{ __('Copy') }}</span>
                <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
            </button>
        </div>
        @php
            $joinWhatsappMessage = __('Join my gift exchange ":name" on GiveTwice!', ['name' => $exchange->name]) . ' ' . $exchange->getJoinUrl();
        @endphp
        <a href="https://wa.me/?text={{ urlencode($joinWhatsappMessage) }}"
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex items-center gap-2 px-4 py-2 mt-3 bg-[#25D366] text-white rounded-lg font-medium text-sm hover:bg-[#1fb855] transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            {{ __('Share via WhatsApp') }}
        </a>
    </div>
    @endif

    {{-- Impact counter --}}
    @if($exchange->isDrawn() && $claimCount > 0)
    <div class="bg-gradient-to-r from-teal-50 to-teal-100 rounded-2xl p-6 mb-6 border border-teal-200">
        <div class="flex items-center gap-4">
            <div class="text-3xl">✨</div>
            <div>
                <p class="font-semibold text-gray-900">{{ __('Your group is giving twice') }}</p>
                <p class="text-gray-600">{{ $claimCount }} {{ trans_choice('gift claimed|gifts claimed', $claimCount) }} · ~€{{ $claimCount * 3 }} {{ __('estimated donation') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Share section (after draw) --}}
    @if($exchange->isDrawn())
    @php
        $whatsappMessage = __(':name on GiveTwice — check your email to see who you got!', ['name' => $exchange->name]);
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-6" x-data="{ copied: false, message: {!! Js::from($whatsappMessage) !!} }">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Let everyone know') }}</h2>
        <p class="text-gray-600 text-sm mb-4">{{ __('Remind participants to check their email for their draw.') }}</p>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/?text={{ urlencode($whatsappMessage) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#25D366] text-white rounded-lg font-medium text-sm hover:bg-[#1fb855] transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <button type="button"
                    @click="navigator.clipboard.writeText(message); copied = true; setTimeout(() => copied = false, 2000)"
                    class="btn-secondary text-sm"
                    :class="copied && 'ring-2 ring-teal-300'">
                <span x-show="!copied">{{ __('Copy message') }}</span>
                <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
            </button>
        </div>
    </div>
    @endif

    {{-- Participants --}}
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Participants') }}</h2>

        <div class="divide-y divide-cream-100">
            @foreach($exchange->participants as $participant)
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-cream-100 rounded-full flex items-center justify-center text-sm font-semibold text-gray-600">
                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $participant->name }}</p>
                        <p class="text-sm text-gray-500">{{ $participant->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($participant->hasViewed())
                        <span class="badge badge-success text-xs">{{ __('Viewed') }}</span>
                    @elseif($exchange->isDrawn())
                        <span class="badge badge-warning text-xs">{{ __('Invited') }}</span>
                    @else
                        <span class="badge badge-info text-xs">{{ __('Added') }}</span>
                    @endif
                    @if($participant->user_id && $participant->defaultWishlist())
                        <span class="badge badge-success text-xs">{{ __('Has wishlist') }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
