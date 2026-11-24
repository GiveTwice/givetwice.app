@extends('layouts.app')
@section('title', __('This reservation link no longer works'))
@section('content')
<x-page-card icon="⌛" :title="__('This reservation link no longer works')">
    <p class="text-gray-600">{{ __('It may have expired, or the reservation was already cancelled. Request a fresh link to see your current reservations.') }}</p>
    <a class="btn-primary mt-6 w-full justify-center" href="{{ route('reservations.index', ['locale' => app()->getLocale()]) }}">{{ __('All my reservations') }}</a>
</x-page-card>
@endsection
