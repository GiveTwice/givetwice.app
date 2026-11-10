@extends('layouts.app')
@section('title', __('All my reservations'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-2xl flex-col gap-4 p-4 wrap-anywhere">
    <h1 class="font-display text-2xl font-bold">{{ __('All my reservations') }}</h1>
    @if(!$email)
        <p>{{ __('reservations.intro') }}</p>
        <form method="POST" action="{{ route('reservations.link', ['locale' => app()->getLocale()]) }}" class="flex flex-col gap-3">@csrf
            <label for="email" class="form-label mb-0">{{ __('Email') }}</label><input id="email" name="email" type="email" required autocomplete="email" class="form-input" value="{{ old('email') }}">
            @error('email')<p class="text-red-700">{{ $message }}</p>@enderror
            <button class="btn-primary">{{ __('reservations.send') }}</button>
        </form>
        <a class="btn-link" href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ __('Log in') }}</a>
    @else
        <p class="text-sm text-gray-600">{{ $email }}</p>
        @forelse($groups as $claims)
            @php($context = $claims->first()->reservation)
            <x-section-heading>{{ $context['owner'] }} · {{ $context['list_name'] }} @if($context['date']) · {{ \Illuminate\Support\Carbon::parse($context['date'])->translatedFormat('j M') }} @endif</x-section-heading>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach($claims as $claim)
                @php($item = $claim->reservation)
                @php($image = $claim->gift?->hasImage() ? $claim->gift->getImageUrl('card') : $item['image'])
                <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="relative aspect-square bg-cream-100">
                        @if($image)
                            <img src="{{ $image }}" alt="" class="absolute inset-0 h-full w-full object-cover {{ $claim->isRemoved() ? 'opacity-50' : '' }}" loading="lazy">
                        @else
                            <div class="flex h-full items-center justify-center text-sm text-gray-600">{{ __('No image') }}</div>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col gap-1.5 px-3 pt-2.5 pb-3">
                        {{-- Exactly two lines tall, so the prices and buttons of neighbouring cards line up. --}}
                        <h3 class="h-[2.40625rem] line-clamp-2 text-sm font-semibold leading-snug {{ $claim->isRemoved() ? 'line-through text-gray-500' : 'text-gray-900' }}">{{ $item['title'] }}</h3>
                        <p class="font-bold text-coral-600">{{ $item['price'] }}</p>
                        @if($alert = $claim->latestAlert())
                            <a class="rounded-lg bg-coral-50 p-2.5 text-sm font-semibold text-coral-700" href="{{ $alert->choiceUrl('review') }}">{{ __('reservations.changed', ['owner' => $item['owner']]) }} →</a>
                        @elseif($claim->isRemoved())
                            <p class="text-sm text-coral-700">{{ __('reservations.deleted', ['owner' => $item['owner']]) }}</p>
                            @if($claim->resolution === 'keep')<p class="text-sm">{{ __('reservations.keep_done', ['owner' => $item['owner']]) }}</p>
                            @else
                                <p class="text-sm">{{ __('reservations.private', ['owner' => $item['owner']]) }}</p>
                                @foreach(['keep', 'alternative', 'dismiss'] as $choice)
                                    <a class="btn-secondary w-full text-center text-sm" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('reservations.choose', now()->addDays(30), ['locale' => app()->getLocale(), 'claim' => $claim, 'choice' => $choice, 'email' => hash('sha256', $email)]) }}">{{ __('reservations.'.$choice) }}</a>
                                @endforeach
                            @endif
                        @endif
                        <div class="mt-auto grid gap-1 pt-1">
                            @if($item['url'])<a class="btn-secondary min-h-9 w-full whitespace-nowrap px-2 text-sm" href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Go to the shop') }} ↗</a>@endif
                            <a class="inline-flex min-h-8 items-center justify-center text-sm font-semibold text-coral-700" href="{{ (new \App\Mail\ReservationMail($claim, app()->getLocale()))->undoUrl }}">{{ __('Undo reservation') }}</a>
                        </div>
                    </div>
                </article>
            @endforeach
            </div>
        @empty<p>{{ __('reservations.empty') }}</p>@endforelse
    @endif
</div>
@endsection
