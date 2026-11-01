@extends('emails.layouts.base')

@section('title', __('Your draw is getting lonely'))

@section('content')
    <x-mail.heading>{{ __('exchange-reminders.reveal_title') }}</x-mail.heading>
    <p>{{ __('Hey') }} {{ $participant->name }},</p>
    <p>{{ __('exchange-reminders.reveal', ['group' => str_replace("\u{2014}", '·', $exchange->name)]) }}</p>
    <x-mail.action :url="$revealUrl" :button="true">{{ __('See my draw') }}</x-mail.action>
    @if($exchange->event_date)
        <p style="color: #6b7280; font-size: 14px;">{{ __('exchange-reminders.date', ['date' => $exchange->event_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY')]) }}</p>
    @endif
    <x-mail.quiet-link :url="$participant->reminderMuteUrl()">{{ __('exchange-reminders.stop') }}</x-mail.quiet-link>
@endsection
