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
<div class="text-center py-8 sm:py-12 lg:py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-coral-100 text-coral-500 rounded-2xl text-3xl mb-6 transform -rotate-3">
        🎲
    </div>
    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">{{ $title }}</h1>
    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">{{ __('Draw names. Buy gifts. Donate to charity. All without anyone knowing who got whom.') }}</p>
</div>

<div class="max-w-4xl mx-auto">
    {{-- How it works --}}
    <div class="grid md:grid-cols-3 gap-6 mb-12">
        <div class="text-center p-6">
            <div class="w-12 h-12 bg-coral-100 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">1</div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Create a group') }}</h3>
            <p class="text-gray-600 text-sm">{{ __('Add everyone\'s name and email. Set a budget if you want.') }}</p>
        </div>
        <div class="text-center p-6">
            <div class="w-12 h-12 bg-sunny-100 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">2</div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Draw names') }}</h3>
            <p class="text-gray-600 text-sm">{{ __('We shake the hat and send everyone an email with their person. No peeking.') }}</p>
        </div>
        <div class="text-center p-6">
            <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center text-2xl mx-auto mb-4">3</div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ __('Give twice') }}</h3>
            <p class="text-gray-600 text-sm">{{ __('Buy from their wishlist. We donate our commission to charity. Everyone wins.') }}</p>
        </div>
    </div>

    {{-- Create form --}}
    @auth
    <div class="bg-white rounded-2xl shadow-sm border border-cream-200 p-6 sm:p-8 mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Start a group') }}</h2>

        <form method="POST" action="{{ route('exchanges.store', ['locale' => $locale, 'exchangeType' => $exchangeType]) }}" x-data="exchangeForm()">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="name" class="form-label">{{ __('Group name') }}</label>
                    <input type="text" name="name" id="name" class="form-input" placeholder="{{ __('e.g. Family Van der Berg') }}" value="{{ old('name') }}" required>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="event_date" class="form-label">{{ __('Event date') }}</label>
                        <input type="date" name="event_date" id="event_date" class="form-input" value="{{ old('event_date') }}" min="{{ now()->addDay()->format('Y-m-d') }}" required>
                        @error('event_date') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="budget_amount" class="form-label">{{ __('Budget per person') }} <span class="text-gray-400">({{ __('optional') }})</span></label>
                        <div class="flex gap-2">
                            <select name="budget_currency" class="form-select shrink-0 !w-16">
                                <option value="EUR">€</option>
                                <option value="USD">$</option>
                            </select>
                            <input type="number" name="budget_amount" id="budget_amount" class="form-input min-w-0 flex-1" placeholder="25" value="{{ old('budget_amount') }}" min="0" step="0.01">
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
                    <input type="checkbox" name="organizer_participates" id="organizer_participates" value="1" class="rounded border-gray-300 text-coral-500 focus:ring-coral-500" checked>
                    <label for="organizer_participates" class="text-gray-700 text-sm">{{ __('I\'m participating too') }}</label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">{{ __('Create group') }}</button>
                </div>
            </div>
        </form>
    </div>
    @else
    <div class="text-center py-8">
        <p class="text-gray-600 mb-4">{{ __('Sign up to create your group. It takes about a minute.') }}</p>
        <a href="{{ route('register', ['locale' => $locale]) }}" class="btn-primary">{{ __('Create an account') }}</a>
        <p class="mt-3 text-sm text-gray-500">{{ __('Already have one?') }} <a href="{{ route('login', ['locale' => $locale]) }}" class="text-coral-500 hover:text-coral-600">{{ __('Log in') }}</a></p>
    </div>
    @endauth

    {{-- Charity angle --}}
    <div class="bg-gradient-to-r from-coral-50 to-sunny-50 rounded-2xl p-6 lg:p-8 max-w-3xl mx-auto mb-12 border border-coral-100">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="text-4xl">&#10084;&#65039;</div>
                <div>
                    <p class="font-semibold text-gray-900">{{ __('Every gift gives twice') }}</p>
                    <p class="text-gray-600">{{ __('When someone buys from a wishlist, we donate our commission. You pay nothing extra.') }}</p>
                </div>
            </div>
            <a href="{{ route('about', ['locale' => $locale]) }}" class="inline-flex items-center px-5 py-2 bg-white text-gray-700 rounded-full hover:bg-gray-50 font-medium transition-colors border border-gray-200 whitespace-nowrap">
                {{ __('How it works') }} &rarr;
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exchangeForm() {
    return {
        participants: [
            { name: '', email: '' },
            { name: '', email: '' },
        ],
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
