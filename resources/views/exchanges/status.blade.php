@extends('layouts.app')
@section('title', $exchange->name)
@section('content')
@php($locale = app()->getLocale())
<div class="max-w-2xl mx-auto space-y-6 py-6">
    <p class="text-sm text-gray-500">{{ $exchange->isDraft() ? __('Step 2 of 3') : __('Step 3 of 3') }}</p>
    <h1 class="text-2xl font-bold break-words">{{ $exchange->name }}</h1>
    <p class="text-gray-600">{{ $exchange->event_date?->format('d/m/Y') }} {{ $exchange->formatBudget() }}</p>
    @if($errors->any()) <div class="alert-error" role="alert">{{ $errors->first() }}</div> @endif

    @if($exchange->isDraft())
    <section class="bg-white rounded-2xl border border-cream-200 p-5 space-y-4" x-data="{ selected: null }">
        <h2 class="text-xl font-bold">{{ __('Who should not draw each other?') }}</h2>
        <p class="text-sm text-gray-600">{{ __('Tap two names to make a couple. Couples never draw each other.') }}</p>
        <form method="POST" action="{{ route('exchanges.rules', [$locale, $exchange]) }}" x-ref="couple">
            @csrf
            <input type="hidden" name="giver_id" :value="selected">
            <input type="hidden" name="receiver_id" x-ref="receiver">
            <input type="hidden" name="mutual" value="1">
            <div class="flex flex-wrap gap-2">
                @foreach($exchange->participants as $participant)
                <button type="button" class="btn-secondary break-all" :class="selected === {{ $participant->id }} && 'ring-2 ring-teal-500'" :aria-pressed="selected === {{ $participant->id }}" @click="if (selected === null) { selected = {{ $participant->id }} } else if (selected === {{ $participant->id }}) { selected = null } else { $refs.receiver.value = {{ $participant->id }}; $refs.couple.requestSubmit() }">{{ $participant->name }}</button>
                @endforeach
            </div>
        </form>
        <div class="space-y-2">
            @foreach($exchange->exclusions as $rule)
                @php($mutual = $exchange->exclusions->contains(fn ($other) => $other->giver_id === $rule->receiver_id && $other->receiver_id === $rule->giver_id))
                @if(!$mutual || $rule->giver_id < $rule->receiver_id)
                <form method="POST" action="{{ route('exchanges.rules', [$locale, $exchange]) }}" class="flex flex-wrap items-center gap-2 text-sm">
                    @csrf
                    <input type="hidden" name="giver_id" value="{{ $rule->giver_id }}">
                    <input type="hidden" name="receiver_id" value="{{ $rule->receiver_id }}">
                    <input type="hidden" name="mutual" value="{{ (int) $mutual }}">
                    <input type="hidden" name="remove" value="1">
                    <span class="break-all">{{ $rule->giver->name }} {{ $mutual ? '+' : '→' }} {{ $rule->receiver->name }}</span>
                    <button class="btn-link">{{ __('Remove') }}</button>
                </form>
                @endif
            @endforeach
        </div>
        <details>
            <summary class="text-coral-600 cursor-pointer font-semibold">{{ __('More rules') }}</summary>
            <form method="POST" action="{{ route('exchanges.rules', [$locale, $exchange]) }}" class="grid gap-3 pt-3">
                @csrf
                <input type="hidden" name="mutual" value="0">
                <label class="form-label">{{ __('This person') }}
                    <select name="giver_id" class="form-select">@foreach($exchange->participants as $participant)<option value="{{ $participant->id }}">{{ $participant->name }}</option>@endforeach</select>
                </label>
                <label class="form-label">{{ __('Cannot draw') }}
                    <select name="receiver_id" class="form-select">@foreach($exchange->participants as $participant)<option value="{{ $participant->id }}">{{ $participant->name }}</option>@endforeach</select>
                </label>
                <button class="btn-secondary">{{ __('Add rule') }}</button>
            </form>
        </details>
        <form method="POST" action="{{ route('exchanges.draw', [$locale, $exchange]) }}">
            @csrf
            <button class="btn-primary w-full">{{ __('Draw names') }}</button>
        </form>
        <p class="text-xs text-gray-500">{{ __('There is no overview of who drew whom. Not even for you.') }}</p>
    </section>
    <details class="bg-white rounded-2xl border border-cream-200 p-5">
        <summary class="font-semibold cursor-pointer">{{ __('Participants') }} ({{ $exchange->participants->count() }})</summary>
        <div class="grid gap-3 py-4">
            @foreach($exchange->participants as $participant)
            <form method="POST" action="{{ route('exchanges.participants.destroy', [$locale, $exchange, $participant]) }}" class="flex items-center justify-between gap-2">
                @csrf @method('DELETE')
                <span class="break-all">{{ $participant->name }}</span>
                @if($exchange->participants->count() > 3)<button class="btn-link shrink-0">{{ __('Remove') }}</button>@endif
            </form>
            @endforeach
            <form method="POST" action="{{ route('exchanges.participants.store', [$locale, $exchange]) }}" class="grid gap-2">
                @csrf
                <input name="name" class="form-input" aria-label="{{ __('Name') }}" placeholder="{{ __('Name') }}" required>
                <input type="email" name="email" class="form-input" aria-label="{{ __('Email (optional)') }}" placeholder="{{ __('Email (optional)') }}">
                <button class="btn-secondary">{{ __('Add participant') }}</button>
            </form>
            <label class="form-label">{{ __('Invite link') }}<input readonly class="form-input min-w-0" value="{{ $exchange->getJoinUrl() }}" @click="$el.select()"></label>
        </div>
    </details>
    @else
    <section class="bg-white rounded-2xl border border-cream-200 p-5 space-y-4">
        <h2 class="text-xl font-bold">{{ __('Drawn! Send everyone their private link') }}</h2>
        <p class="text-sm text-gray-600">{{ __('Each link is personal. Only send it to that person.') }}</p>
        <p class="text-sm text-gray-600">{{ __('Pick the right person in WhatsApp. Mark it sent yourself. Seen only counts after they tap Show my draw.') }}</p>
        @foreach($exchange->participants as $participant)
        <div class="border-t border-cream-200 pt-4 space-y-2" x-data="{ whatsappOpened: false }">
            <p class="font-semibold break-all">{{ $participant->name }}</p>
            @if(!$participant->hasViewed() && !$participant->sent_at)<p x-show="whatsappOpened" x-cloak class="text-sm text-gray-500">{{ __('WhatsApp opened') }}</p>@endif
            <p x-show="!whatsappOpened || @js($participant->hasViewed() || (bool) $participant->sent_at)" class="text-sm text-gray-500">{{ $participant->hasViewed() ? __('Seen') : ($participant->sent_at ? __('Sent') : ($participant->whatsapp_opened_at ? __('WhatsApp opened') : __('Not sent'))) }}</p>
            <div class="flex flex-wrap gap-2">
                @if($participant->user_id === auth()->id())<a class="btn-secondary text-sm" href="{{ $participant->revealUrl() }}">{{ __('My draw') }}</a>@endif
                <form method="POST" action="{{ route('exchanges.delivery', [$locale, $exchange, $participant]) }}" target="_blank" @submit="whatsappOpened = true">
                    @csrf <button name="action" value="whatsapp" class="btn-secondary text-sm">WhatsApp</button>
                </form>
                <form method="POST" action="{{ route('exchanges.delivery', [$locale, $exchange, $participant]) }}">
                    @csrf <button name="action" value="sent" class="btn-secondary text-sm">{{ __('Mark sent') }}</button>
                </form>
                @if($participant->email)
                <form method="POST" action="{{ route('exchanges.resend-invite', [$locale, $exchange, $participant]) }}">
                    @csrf <button class="btn-secondary text-sm">{{ __('Resend email') }}</button>
                </form>
                @endif
                <form method="POST" action="{{ route('exchanges.delivery', [$locale, $exchange, $participant]) }}" onsubmit="return confirm(@js(__('Replace this link? The old one will stop working.')))">
                    @csrf <button name="action" value="regenerate" class="btn-link text-sm">{{ __('New link') }}</button>
                </form>
            </div>
        </div>
        @endforeach
    </section>
    @endif
</div>
@endsection
