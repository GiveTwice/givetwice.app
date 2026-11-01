@extends('emails.layouts.base')
@section('title', __('reservations.updates'))
@section('content')
    <x-mail.heading>{{ __('reservations.updates') }}</x-mail.heading>
    <p style="color: #6b7280; font-size: 14px;">{{ str_replace("\u{2014}", '·', $alert->before['list_name'] ?? $alert->before['owner']) }} · {{ $alert->before['owner'] }}</p>
    @foreach($alerts as $alert)
        <x-mail.panel>
            <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 16px;">{{ $alert->before['title'] }}</h2>
            @include('emails.partials.reservation-changes')
            @foreach($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release'] as $choice)
                <x-mail.action :url="$alert->choiceUrl($choice)">{{ __('reservations.'.$choice) }}</x-mail.action>
            @endforeach
        </x-mail.panel>
    @endforeach
    <x-mail.action :url="$alert->claim->historyUrl($alert->locale)" :button="true">{{ __('All my reservations') }}</x-mail.action>
    <x-mail.quiet-link :url="$alert->choiceUrl('mute')">{{ __('reservations.mute', ['owner' => $alert->before['list_name'] ?? $alert->before['owner']]) }}</x-mail.quiet-link>
@endsection
