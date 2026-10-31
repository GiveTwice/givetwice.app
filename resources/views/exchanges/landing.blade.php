@extends('layouts.app')

@php
    $titles = [
        'lootjes-trekken' => __('Lootjes trekken'),
        'secret-santa' => __('Secret Santa'),
        'tirage-au-sort' => __('Tirage au sort'),
    ];
    $title = $titles[$exchangeType] ?? __('Gift Exchange');
@endphp

@section('title', $title)
@section('description', __('meta.exchange'))

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="pt-2 pb-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
    </div>

    <ol class="mb-4 grid grid-cols-3 gap-2 text-xs sm:text-sm text-gray-600" aria-label="{{ __('Steps') }}">
        <li aria-current="step" class="border-b-2 border-coral-600 pb-2 font-semibold text-coral-700">1. {{ __('Participants') }}</li>
        <li class="border-b-2 border-cream-300 pb-2">2. {{ __('Draw names') }}</li>
        <li class="border-b-2 border-cream-300 pb-2">3. {{ __('Share links') }}</li>
    </ol>

    <div class="bg-white rounded-xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-8">

        @auth
        <form method="POST" action="{{ route('exchanges.store', ['locale' => $locale, 'exchangeType' => $exchangeType]) }}" x-data="exchangeForm()" x-on:invalid.capture="$event.target.closest('details')?.setAttribute('open', '')" @submit="clearSaved()">
            @csrf
        @else
        <form x-data="exchangeForm()" x-on:invalid.capture="$event.target.closest('details')?.setAttribute('open', '')" @submit.prevent="showAuthPrompt = true; $nextTick(() => $refs.authPrompt.scrollIntoView({ behavior: 'smooth', block: 'nearest' }))">
        @endauth

            <div class="space-y-5">
                <fieldset class="min-w-0">
                    <legend class="text-lg font-semibold text-gray-900">{{ __('Who is joining?') }}</legend>
                    <p class="mt-1 text-sm text-gray-600">{{ __('At least 3 people, including you.') }}</p>
                    <div class="my-4 flex items-center gap-3">
                        <input type="checkbox" name="organizer_participates" id="organizer_participates" value="1" class="h-5 w-5 shrink-0 rounded border-gray-300 text-coral-500 focus:ring-coral-500" x-model="organizerParticipates">
                        <label for="organizer_participates" class="text-sm text-gray-700">
                            <span class="font-semibold">{{ __('You are joining') }}</span>@auth <span>({{ auth()->user()->name }})</span>@endauth
                        </label>
                    </div>
                    <div class="grid gap-3">
                        <template x-for="(participant, index) in participants" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text" :name="'participants[' + index + '][name]'" :aria-label="@js(__('Participant :number')).replace(':number', index + 1)" class="form-input min-w-0 flex-1" placeholder="{{ __('Name') }}" x-model="participant.name" required>
                                <button type="button" @click="removeParticipant(index)" :aria-label="@js(__('Remove participant :number')).replace(':number', index + 1)" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-2xl text-gray-600 hover:bg-cream-100">×</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addParticipant()" :disabled="participants.length >= (organizerParticipates ? 49 : 50)" class="btn-link py-3 text-sm disabled:opacity-50">+ {{ __('Add participant') }}</button>
                    @if($errors->any())<p class="form-error">{{ $errors->first() }}</p>@endif
                </fieldset>

                <div>
                    <label for="name" class="form-label">{{ __('Group name') }}</label>
                    <input type="text" name="name" id="name" class="form-input" placeholder="{{ __('e.g. Family Van der Berg') }}" x-model="groupName" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <details @if($errors->has('event_date') || $errors->has('budget_amount')) open @endif>
                    <summary class="cursor-pointer py-2 text-sm font-medium text-gray-700">{{ __('Date and budget (optional)') }}</summary>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="event_date" class="form-label">{{ __('Event date') }} <span class="text-gray-600">({{ __('optional') }})</span></label>
                            <input type="date" name="event_date" id="event_date" class="form-input" x-model="eventDate" min="{{ now()->addDay()->format('Y-m-d') }}">
                            @error('event_date') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="budget_amount" class="form-label">{{ __('Budget per person') }} <span class="text-gray-600">({{ __('optional') }})</span></label>
                            <div class="flex gap-2">
                                <select aria-label="{{ __('Currency') }}" name="budget_currency" class="form-select shrink-0 !w-16" x-model="budgetCurrency">
                                    <option value="EUR">€</option>
                                    <option value="USD">$</option>
                                </select>
                                <input type="number" name="budget_amount" id="budget_amount" class="form-input min-w-0 flex-1" placeholder="25" x-model="budgetAmount" min="0" step="0.01">
                            </div>
                            @error('budget_amount') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </details>
                <details x-bind:open="participants.some(person => person.email)" @if($errors->has('participants.*.email')) open @endif>
                    <summary class="cursor-pointer py-2 text-sm font-medium text-gray-700">{{ __('Email addresses (optional)') }}</summary>
                    <p class="mb-3 text-sm text-gray-600">{{ __('No email needed. You can share private links via WhatsApp.') }}</p>
                    <div class="grid gap-3">
                        <template x-for="(participant, index) in participants" :key="index">
                            <div>
                                <label :for="'participant-email-' + index" class="form-label text-sm" x-text="participant.name || @js(__('Participant :number')).replace(':number', index + 1)"></label>
                                <input type="email" :id="'participant-email-' + index" :name="'participants[' + index + '][email]'" class="form-input" placeholder="{{ __('Email (optional)') }}" x-model="participant.email">
                            </div>
                        </template>
                    </div>
                </details>

                <div class="form-actions">
                    <button type="submit" class="btn-primary w-full justify-center">{{ __('Next') }}</button>
                </div>

                @guest
                <div id="save-group" x-ref="authPrompt" x-show="showAuthPrompt" x-transition x-cloak class="mt-4 bg-coral-50 border border-coral-200 rounded-xl p-5 text-center">
                    <p class="font-semibold text-gray-900 mb-1">{{ __('Almost there!') }}</p>
                    <p class="text-gray-600 text-sm mb-4">{{ __('Create a free account to save your group and draw names.') }}</p>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <a href="{{ route('register', ['locale' => $locale]) }}?intended={{ urlencode(route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType])) }}" class="btn-primary">{{ __('Sign up - it\'s free') }}</a>
                        <a href="{{ route('login', ['locale' => $locale]) }}?intended={{ urlencode(route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType])) }}" class="btn-secondary">{{ __('Log in') }}</a>
                    </div>
                </div>
                @endguest
            </div>
        </form>
    </div>

    {{-- How it works - below the form, not above it --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-coral-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-coral-600">1</div>
            <p class="text-gray-600 text-xs">{{ __('Names first. Email is optional.') }}</p>
        </div>
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-sunny-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-sunny-600">2</div>
            <p class="text-gray-600 text-xs">{{ __('Add couples, draw names, then send each private link.') }}</p>
        </div>
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-teal-600">3</div>
            <p class="text-gray-600 text-xs">{{ __('If you later buy through our shop links, all our commission will go to charity. We are setting that up now.') }}</p>
        </div>
    </div>

    {{-- Charity angle --}}
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-5 border border-coral-100 text-center mb-8">
        <p class="text-gray-600 text-sm">
            ❤️ {{ __('If you later buy through our shop links, all our commission will go to charity. We are setting that up now.') }}
            <a href="{{ route('about', ['locale' => $locale]) }}" class="text-coral-500 hover:text-coral-600 font-medium">{{ __('How it works') }} →</a>
        </p>
    </div>
</div>

@push('scripts')
<script>
function exchangeForm() {
    const storageKey = 'exchange_form_draft';
    const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');

    return {
        showAuthPrompt: false,
        groupName: saved?.groupName || {!! Js::from(old('name', '')) !!},
        eventDate: saved?.eventDate || {!! Js::from(old('event_date', '')) !!},
        budgetAmount: saved?.budgetAmount || {!! Js::from(old('budget_amount', '')) !!},
        budgetCurrency: saved?.budgetCurrency || {!! Js::from(old('budget_currency', 'EUR')) !!},
        organizerParticipates: {!! session()->hasOldInput() ? Js::from((bool) old('organizer_participates', false)) : 'saved?.organizerParticipates ?? true' !!},
        participants: {!! Js::from(old('participants')) !!} || saved?.participants || [
            { name: '', email: '' },
            { name: '', email: '' },
        ],
        init() {
            this.$watch('groupName', () => this.save());
            this.$watch('eventDate', () => this.save());
            this.$watch('budgetAmount', () => this.save());
            this.$watch('budgetCurrency', () => this.save());
            this.$watch('organizerParticipates', () => this.save());
            this.$watch('participants', () => this.save(), { deep: true });
        },
        save() {
            localStorage.setItem(storageKey, JSON.stringify({
                groupName: this.groupName,
                eventDate: this.eventDate,
                budgetAmount: this.budgetAmount,
                budgetCurrency: this.budgetCurrency,
                organizerParticipates: this.organizerParticipates,
                participants: this.participants,
            }));
        },
        clearSaved() {
            localStorage.removeItem(storageKey);
        },
        addParticipant() {
            if (this.participants.length >= (this.organizerParticipates ? 49 : 50)) { return; }
            this.participants.push({ name: '', email: '' });
        },
        removeParticipant(index) {
            this.participants.splice(index, 1);
        }
    }
}
</script>
@endpush
@endsection
