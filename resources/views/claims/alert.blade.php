@extends('layouts.app')
@section('title', $alert->summary())
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-xl flex-col gap-4 bg-white p-4 wrap-anywhere">
    <h1 class="text-xl font-bold">{{ $alert->summary() }}</h1>
    <p>{{ $alert->before['title'] }} · {{ $claim->created_at->translatedFormat('j M Y') }}</p>
    @include('claims.change-values')
    @foreach($choice === 'review' ? ($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release']) : [$choice] as $option)
        <form method="POST" action="{{ $alert->choiceUrl($option) }}">@csrf<button class="btn-secondary w-full min-h-11">{{ __('reservations.'.$option, ['owner' => $alert->before['owner']]) }}</button></form>
    @endforeach
    <p class="text-sm text-gray-500">{{ __('reservations.private', ['owner' => $alert->before['owner']]) }}</p>
</div>
@endsection
