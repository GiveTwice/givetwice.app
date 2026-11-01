@extends('emails.layouts.base')

@section('title', __('Someone claimed a gift from your wishlist!'))

@section('content')
    <x-mail.heading>{{ __('Good news!') }}</x-mail.heading>

    <p>{{ __('Hello') }} {{ $owner->name }},</p>

    <p>{{ __('Someone has claimed a gift from your wishlist:') }}</p>

    <x-mail.panel>
        <strong style="color: #0f766e;">{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->hasPrice())
            <br><span style="color: #0f766e; font-weight: 600;">{{ $gift->formatPrice(false) }}</span>
        @endif
    </x-mail.panel>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('To keep the surprise, we won\'t tell you who claimed it!') }}
    </p>

    <x-mail.action :url="$dashboardUrl" :button="true">{{ __('View Your Wishlist') }}</x-mail.action>
@endsection
