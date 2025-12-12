@extends('emails.layouts.base')

@section('title', __('Someone claimed a gift from your wishlist!'))

@section('content')
    <h1 style="color: #E8614D; margin-top: 0; font-size: 24px;">{{ __('Good news!') }} &#127873;</h1>

    <p>{{ __('Hello') }} {{ $owner->name }},</p>

    <p>{{ __('Someone has claimed a gift from your wishlist:') }}</p>

    <div style="background: #FEF7F5; padding: 16px; border-radius: 12px; margin: 20px 0; border: 1px solid #FECDC7;">
        <strong style="color: #C4493A;">{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->hasPrice())
            <br><span style="color: #E8614D; font-weight: 600;">{{ $gift->formatPrice(false) }}</span>
        @endif
    </div>

    <p style="color: #6b7280; font-size: 14px;">
        {{ __('To keep the surprise, we won\'t tell you who claimed it!') }}
    </p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $dashboardUrl }}" style="background: #E8614D; color: white; padding: 14px 28px; text-decoration: none; border-radius: 12px; display: inline-block; font-weight: 600;">
            {{ __('View Your Wishlist') }}
        </a>
    </p>
@endsection
