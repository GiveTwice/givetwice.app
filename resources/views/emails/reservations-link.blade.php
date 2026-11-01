@extends('emails.layouts.base')
@section('title', __('All my reservations'))
@section('content')
    <x-mail.heading>{{ __('All my reservations') }}</x-mail.heading>
    <p>{{ __('Your reservations are one click away. This link works for 30 minutes.') }}</p>
    <x-mail.action :url="$historyUrl" :button="true">{{ __('All my reservations') }}</x-mail.action>
@endsection
