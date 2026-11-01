@extends('emails.layouts.base')

@section('title', __("You're invited to collaborate!", locale: $locale))

@section('content')
    <x-mail.heading>{{ __("You're invited!", locale: $locale) }}</x-mail.heading>

    <p>{{ __('Hello', locale: $locale) }},</p>

    <p>{{ __(':name has invited you to collaborate on their wishlist:', ['name' => $inviter->name], $locale) }}</p>

    <x-mail.panel>
        <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 4px;">{{ str_replace("\u{2014}", '·', $list->name) }}</h2>
        @if($list->description)
            <span style="color: #6b7280; font-size: 14px;">{{ $list->description }}</span>
        @endif
    </x-mail.panel>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __("You'll be able to add and edit gifts on this list together.", locale: $locale) }}
    </p>

    @if($isExistingUser)
        <x-mail.action :url="$acceptUrl" :button="true">{{ __('Accept invitation', locale: $locale) }}</x-mail.action>
        <p style="color: #6b7280; font-size: 14px; text-align: center;">
            {{ __('You can also accept or decline this invitation from your dashboard.', locale: $locale) }}
        </p>
    @else
        <x-mail.action :url="$registerUrl" :button="true">{{ __('Create account & accept', locale: $locale) }}</x-mail.action>
        <p style="color: #6b7280; font-size: 14px; text-align: center;">
            {{ __('Create a free account and start adding gifts together.', locale: $locale) }}
        </p>
    @endif

    <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
        {{ __('This invitation expires in 30 days.', locale: $locale) }}
    </p>
@endsection
