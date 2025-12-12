@extends('emails.layouts.base')

@section('title', __('Confirm your gift claim'))

@section('content')
    <h1 style="color: #E8614D; margin-top: 0; font-size: 24px;">{{ __('Confirm your gift claim') }}</h1>

    <p>{{ __('Hello') }}{{ $claim->claimer_name ? ' ' . $claim->claimer_name : '' }},</p>

    <p>{{ __('You requested to claim the following gift:') }}</p>

    <div style="background: #FEF7F5; padding: 16px; border-radius: 12px; margin: 20px 0; border: 1px solid #FECDC7;">
        <strong style="color: #C4493A;">{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->hasPrice())
            <br><span style="color: #E8614D; font-weight: 600;">{{ $gift->formatPrice(false) }}</span>
        @endif
    </div>

    <p>{{ __('Please click the button below to confirm your claim:') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmUrl }}" style="background: #E8614D; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
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
