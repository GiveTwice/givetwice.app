@extends('layouts.app')
@section('title', __('reservations.mute', ['owner' => $alert->before['owner']]))
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card icon="🔕" :title="__('reservations.mute', ['owner' => $alert->before['owner']])">
    <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary w-full justify-center">{{ __('reservations.mute', ['owner' => $alert->before['owner']]) }}</button></form>
</x-page-card>
@endsection
