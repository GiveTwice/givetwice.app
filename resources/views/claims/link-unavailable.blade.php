@extends('layouts.app')
@section('title', __('This reservation link no longer works'))
@section('content')
<div class="mx-auto max-w-lg px-4 py-8 flex flex-col gap-4">
    <h1 class="text-xl font-semibold">{{ __('This reservation link no longer works') }}</h1>
    <p>{{ __('It may have expired, or the reservation was already cancelled. Request a fresh link to see your current reservations.') }}</p>
    <a class="btn-primary min-h-11" href="{{ route('reservations.index', ['locale' => app()->getLocale()]) }}">{{ __('All my reservations') }}</a>
</div>
@endsection
