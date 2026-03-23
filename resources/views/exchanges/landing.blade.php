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

    {{-- Compact header — not a wall of marketing --}}
    <div class="text-center pt-6 pb-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">🎲 {{ $title }}</h1>
        <p class="text-gray-600">{{ __('Draw names. Buy gifts. Donate to charity. All without anyone knowing who got whom.') }}</p>
    </div>

    {{-- The form is always visible — guest or authenticated --}}
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-8">

        @auth
        <form method="POST" action="{{ route('exchanges.store', ['locale' => $locale, 'exchangeType' => $exchangeType]) }}" x-data="exchangeForm()" @submit="clearSaved()">
            @csrf
        @else
        <form x-data="exchangeForm()" @submit.prevent="showAuthPrompt = true">
        @endauth

            <div class="space-y-5">
                <div>
                    <label for="name" class="form-label">{{ __('Group name') }}</label>
                    <input type="text" name="name" id="name" class="form-input" placeholder="{{ __('e.g. Family Van der Berg') }}" x-model="groupName" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="event_date" class="form-label">{{ __('Event date') }}</label>
                        <input type="date" name="event_date" id="event_date" class="form-input" x-model="eventDate" min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        @error('event_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="budget_amount" class="form-label">{{ __('Budget per person') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
                        <div class="flex gap-2">
                            <select name="budget_currency" class="form-select shrink-0 !w-16" x-model="budgetCurrency">
                                <option value="EUR">€</option>
                                <option value="USD">$</option>
                            </select>
                            <input type="number" name="budget_amount" id="budget_amount" class="form-input min-w-0 flex-1" placeholder="25" x-model="budgetAmount" min="0" step="0.01">
                        </div>
                        @error('budget_amount') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">{{ __('Participants') }}</label>
                    <p class="form-help mb-3">{{ __('Add at least 2 other people (3 total with you, if you\'re playing).') }}</p>

                    <template x-for="(participant, index) in participants" :key="index">
                        <div class="flex gap-2 mb-2">
                            <input type="text" :name="'participants[' + index + '][name]'" class="form-input flex-1" :placeholder="'{{ __('Name') }}'" x-model="participant.name" required>
                            <input type="email" :name="'participants[' + index + '][email]'" class="form-input flex-1" :placeholder="'{{ __('Email') }}'" x-model="participant.email" required>
                            <button type="button" @click="removeParticipant(index)" x-show="participants.length > 2" class="text-gray-400 hover:text-red-500 px-2">
                                &times;
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="addParticipant()" class="btn-link text-sm mt-1">
                        + {{ __('Add another person') }}
                    </button>

                    @error('participants') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="organizer_participates" id="organizer_participates" value="1" class="rounded border-gray-300 text-coral-500 focus:ring-coral-500" x-model="organizerParticipates">
                    <label for="organizer_participates" class="text-gray-700 text-sm">{{ __('I\'m participating too') }}</label>
                </div>

                @auth
                <div class="form-actions">
                    <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('Create group') }}</button>
                </div>
                @else
                {{-- Auth prompt appears when guest tries to submit --}}
                <div>
                    <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('Create group') }}</button>
                </div>

                <div x-show="showAuthPrompt" x-transition x-cloak class="mt-4 bg-coral-50 border border-coral-200 rounded-xl p-5 text-center">
                    <p class="font-semibold text-gray-900 mb-1">{{ __('Almost there!') }}</p>
                    <p class="text-gray-600 text-sm mb-4">{{ __('Create a free account to save your group and draw names.') }}</p>
                    <div class="flex flex-col sm:flex-row gap-2 justify-center">
                        <a href="{{ route('register', ['locale' => $locale]) }}?intended={{ urlencode(route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType])) }}" class="btn-primary">{{ __('Sign up — it\'s free') }}</a>
                        <a href="{{ route('login', ['locale' => $locale]) }}?intended={{ urlencode(route('exchanges.landing', ['locale' => $locale, 'exchangeType' => $exchangeType])) }}" class="btn-secondary">{{ __('Log in') }}</a>
                    </div>
                </div>
                @endauth
            </div>
        </form>
    </div>

    {{-- How it works — below the form, not above it --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-coral-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-coral-600">1</div>
            <p class="text-gray-600 text-xs">{{ __('Add everyone\'s name and email. Set a budget if you want.') }}</p>
        </div>
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-sunny-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-sunny-600">2</div>
            <p class="text-gray-600 text-xs">{{ __('We shake the hat and send everyone an email with their person. No peeking.') }}</p>
        </div>
        <div class="text-center p-4">
            <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center text-lg mx-auto mb-2 font-bold text-teal-600">3</div>
            <p class="text-gray-600 text-xs">{{ __('Buy from their wishlist. We donate our commission to charity. Everyone wins.') }}</p>
        </div>
    </div>

    {{-- Charity angle --}}
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-5 border border-coral-100 text-center mb-8">
        <p class="text-gray-600 text-sm">
            ❤️ {{ __('When someone buys from a wishlist, we donate our commission. You pay nothing extra.') }}
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
        groupName: saved?.groupName || '{{ old('name', '') }}',
        eventDate: saved?.eventDate || '{{ old('event_date', '') }}',
        budgetAmount: saved?.budgetAmount || '{{ old('budget_amount', '') }}',
        budgetCurrency: saved?.budgetCurrency || '{{ old('budget_currency', 'EUR') }}',
        organizerParticipates: saved?.organizerParticipates ?? true,
        participants: saved?.participants || [
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
