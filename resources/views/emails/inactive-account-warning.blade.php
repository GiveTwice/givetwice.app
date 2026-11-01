@extends('emails.layouts.base')

@section('title', __("We haven't seen you in a while"))

@section('content')
    <x-mail.heading>{{ __("We haven't seen you in a while") }}</x-mail.heading>

    <p style="font-size: 15px; color: #374151;">{{ __('Hi :name,', ['name' => $user->name]) }}</p>

    <p style="font-size: 15px; color: #374151;">{{ __('Your GiveTwice account has been quiet for a while. We clean up inactive accounts after 24 months to protect your privacy.') }}</p>

    <x-mail.panel>
        <p style="margin: 0; color: #374151; font-size: 14px; font-weight: 600;">{{ __('What happens next?') }}</p>
        <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 15px;">{{ __('If you don\'t log in within 2 months, your account and data will be permanently deleted.') }}</p>
    </x-mail.panel>

    <p style="font-size: 15px; color: #374151;">{{ __('Want to keep it? Just log in and your account stays active.') }}</p>

    <x-mail.action :url="$loginUrl" :button="true">{{ __('Log in to keep your account') }}</x-mail.action>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('You can also export your data before deletion from your') }} <a href="{{ $settingsUrl }}" style="color: #0f766e; text-decoration: underline; font-weight: 600;">{{ __('settings page') }}</a>.
    </p>
@endsection
