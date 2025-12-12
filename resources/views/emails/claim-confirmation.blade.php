@extends('emails.layouts.base')

@section('title', __('Confirm your gift claim'))

@section('content')
    <h1 style="color: #0d9488; margin-top: 0;">{{ __('Confirm your gift claim') }}</h1>

    <p>{{ __('Hello') }}{{ $claim->claimer_name ? ' ' . $claim->claimer_name : '' }},</p>

    <p>{{ __('You requested to claim the following gift:') }}</p>

    <div style="background: #f0fdfa; padding: 15px; border-radius: 12px; margin: 20px 0; border: 1px solid #99f6e4;">
        <strong style="color: #0f766e;">{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->hasPrice())
            <br><span style="color: #14b8a6; font-weight: 600;">{{ $gift->formatPrice(false) }}</span>
        @endif
    </div>

    <p>{{ __('Please click the button below to confirm your claim:') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmUrl }}" style="background: #14b8a6; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
            {{ __('Confirm Claim') }}
        </a>
    </p>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('If you did not request this, you can safely ignore this email.') }}
    </p>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('This link will expire if someone else claims the gift first.') }}
    </p>
@endsection
