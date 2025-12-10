@extends('emails.layouts.base')

@section('title', __('Confirm your gift claim'))

@section('content')
    <h1 style="color: #16a34a; margin-top: 0;">{{ __('Confirm your gift claim') }}</h1>

    <p>{{ __('Hello') }}{{ $claim->claimer_name ? ' ' . $claim->claimer_name : '' }},</p>

    <p>{{ __('You requested to claim the following gift:') }}</p>

    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <strong>{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->price)
            <br><span style="color: #666;">{{ $gift->currency }} {{ number_format($gift->price, 2) }}</span>
        @endif
    </div>

    <p>{{ __('Please click the button below to confirm your claim:') }}</p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $confirmUrl }}" style="background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            {{ __('Confirm Claim') }}
        </a>
    </p>

    <p style="color: #666; font-size: 14px;">
        {{ __('If you did not request this, you can safely ignore this email.') }}
    </p>

    <p style="color: #666; font-size: 14px;">
        {{ __('This link will expire if someone else claims the gift first.') }}
    </p>
@endsection
