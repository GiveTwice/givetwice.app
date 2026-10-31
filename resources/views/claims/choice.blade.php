@extends('layouts.app')
@section('title', __('All my reservations'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-md flex-col gap-4 rounded-xl bg-white p-5 wrap-anywhere">
    <h1 class="text-lg font-bold">{{ __('reservations.'.$choice) }}</h1>
    <p>{{ $claim->reservation['title'] }}</p>
    <p>{{ __('reservations.private', ['owner' => $claim->reservation['owner']]) }}</p>
    <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary">{{ __('reservations.'.$choice) }}</button></form>
</div>
@endsection
