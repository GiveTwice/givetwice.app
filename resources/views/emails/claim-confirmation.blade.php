@extends('emails.layouts.base')

@section('title', __('Confirm your gift claim'))

@section('content')
    <x-mail.heading>{{ __('Confirm your gift claim') }}</x-mail.heading>

    <p>{{ __('Hello') }}{{ $claim->claimer_name ? ' ' . $claim->claimer_name : '' }},</p>

    <p>{{ __('You want to claim this gift:') }}</p>

    <x-mail.panel>
        <strong style="color: #0f766e;">{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->hasPrice())
            <br><span style="color: #0f766e; font-weight: 600;">{{ $gift->formatPrice(false) }}</span>
        @endif
    </x-mail.panel>

    <p>{{ __('One click to confirm:') }}</p>

    <x-mail.action :url="$confirmUrl" :button="true">{{ __('Confirm Claim') }}</x-mail.action>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('If you did not request this, you can safely ignore this email.') }}
    </p>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('Heads up: if someone else claims it first, this link stops working.') }}
    </p>
@endsection
