@extends('layouts.app')
@section('title', __('All my reservations'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-xl flex-col gap-4 p-4 wrap-anywhere">
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
            @foreach($claims as $claim)
                @php($item = $claim->reservation)
                <article class="flex flex-col gap-3 rounded-xl border border-cream-200 bg-white p-4">
                    <div class="flex items-center gap-3">
                        @if($item['image'])<img src="{{ $item['image'] }}" alt="" class="size-14 shrink-0 rounded-lg object-cover">@endif
                        <div class="min-w-0"><h3 class="font-semibold {{ $claim->isRemoved() ? 'line-through text-gray-500' : '' }}">{{ $item['title'] }}</h3><p class="text-sm">{{ $item['price'] }}</p></div>
                    </div>
                    @if($alert = $claim->latestAlert())
                        <a class="rounded-lg bg-coral-50 p-3 font-semibold text-coral-700" href="{{ $alert->choiceUrl('review') }}">{{ __('reservations.changed', ['owner' => $item['owner']]) }} →</a>
                    @elseif($claim->isRemoved())
                        <p class="text-sm text-coral-700">{{ __('reservations.deleted', ['owner' => $item['owner']]) }}</p>
                        @if($claim->resolution === 'keep')<p>{{ __('reservations.keep_done', ['owner' => $item['owner']]) }}</p>
                        @else
                            <p class="text-sm">{{ __('reservations.private', ['owner' => $item['owner']]) }}</p>
                            @foreach(['keep', 'alternative', 'dismiss'] as $choice)
                                <a class="btn-secondary text-center" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('reservations.choose', now()->addDays(30), ['locale' => app()->getLocale(), 'claim' => $claim, 'choice' => $choice, 'email' => hash('sha256', $email)]) }}">{{ __('reservations.'.$choice) }}</a>
                            @endforeach
                        @endif
                    @endif
                    <div class="flex flex-wrap gap-4 text-sm font-semibold text-coral-600">
                        @if($item['url'])<a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('Go to the shop') }} ↗</a>@endif
                        <a href="{{ (new \App\Mail\ReservationMail($claim, app()->getLocale()))->undoUrl }}">{{ __('Undo reservation') }}</a>
                    </div>
                </article>
            @endforeach
        @empty<p>{{ __('reservations.empty') }}</p>@endforelse
    @endif
</div>
@endsection
