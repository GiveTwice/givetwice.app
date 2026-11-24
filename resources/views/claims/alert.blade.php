@extends('layouts.app')
@section('title', $alert->summary())
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card width="max-w-xl" icon="🎁" :title="$alert->summary()" :subtitle="$alert->before['title'].' · '.$claim->created_at->translatedFormat('j M Y')">
    <div class="flex flex-col gap-4">
        <div class="rounded-xl bg-cream-50 p-3">@include('claims.change-values')</div>
        @foreach($choice === 'review' ? ($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release']) : [$choice] as $option)
            <form method="POST" action="{{ $alert->choiceUrl($option) }}">@csrf<button class="{{ $loop->first ? 'btn-primary' : 'btn-secondary' }} w-full min-h-11 justify-center">{{ __('reservations.'.$option, ['owner' => $alert->before['owner']]) }}</button></form>
        @endforeach
        <p class="text-sm text-gray-500">{{ __('reservations.private', ['owner' => $alert->before['owner']]) }}</p>
    </div>
</x-page-card>
@endsection
