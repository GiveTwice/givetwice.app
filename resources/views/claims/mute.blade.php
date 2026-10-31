@extends('layouts.app')
@section('title', __('reservations.mute', ['owner' => $alert->before['owner']]))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto max-w-md rounded-xl bg-white p-5 wrap-anywhere">
    <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary">{{ __('reservations.mute', ['owner' => $alert->before['owner']]) }}</button></form>
</div>
@endsection
