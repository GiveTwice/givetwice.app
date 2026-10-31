@extends('layouts.app')
@section('title', __('reservations.changed', ['owner' => $alert->before['owner']]))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-xl flex-col gap-4 bg-white p-4 wrap-anywhere">
    <h1 class="text-xl font-bold">{{ __('reservations.'.($alert->after['deleted'] ? 'deleted' : 'changed'), ['owner' => $alert->before['owner']]) }}</h1>
    <p>{{ $alert->before['title'] }} · {{ $claim->created_at->translatedFormat('j M Y') }}</p>
    @include('claims.change-values')
    @if($alert->after['deleted'])<p>{{ __('reservations.keep_done', ['owner' => $alert->before['owner']]) }}</p>@endif
    @foreach($choice === 'review' ? ($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release']) : [$choice] as $option)
        <form method="POST" action="{{ $alert->choiceUrl($option) }}">@csrf<button class="btn-secondary w-full">{{ __('reservations.'.$option, ['owner' => $alert->before['owner']]) }}</button></form>
    @endforeach
    <p class="text-sm text-gray-500">{{ __('reservations.private', ['owner' => $alert->before['owner']]) }}</p>
</div>
@endsection
