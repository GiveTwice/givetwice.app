@extends('layouts.app')
@section('title', __('All my reservations'))
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card icon="🎁" :title="__('reservations.'.$choice)" :subtitle="$claim->reservation['title']">
    <p class="text-gray-600">{{ __('reservations.private', ['owner' => $claim->reservation['owner']]) }}</p>
    <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6">@csrf<button class="btn-primary w-full justify-center">{{ __('reservations.'.$choice) }}</button></form>
</x-page-card>
@endsection
