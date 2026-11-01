@extends('emails.layouts.base')

@section('title', __('exchange-reminders.wishlist_title'))

@section('content')
    <x-mail.heading>{{ __('exchange-reminders.wishlist_title') }}</x-mail.heading>
    <p>{{ __('Hey') }} {{ $participant->name }},</p>
    <p>{{ __('exchange-reminders.wishlist', ['group' => str_replace("\u{2014}", '·', $exchange->name)]) }}</p>
    <x-mail.action :url="$registerUrl" :button="true">{{ __('Make a wishlist') }}</x-mail.action>
    <x-mail.quiet-link :url="$participant->reminderMuteUrl()">{{ __('exchange-reminders.stop') }}</x-mail.quiet-link>
@endsection
