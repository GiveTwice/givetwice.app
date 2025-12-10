@extends('emails.layouts.base')

@section('title', __('Someone claimed a gift from your wishlist!'))

@section('content')
    <h1 style="color: #16a34a; margin-top: 0;">{{ __('Good news!') }}</h1>

    <p>{{ __('Hello') }} {{ $owner->name }},</p>

    <p>{{ __('Someone has claimed a gift from your wishlist:') }}</p>

    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <strong>{{ $gift->title ?: __('Untitled gift') }}</strong>
        @if($gift->price)
            <br><span style="color: #666;">{{ $gift->currency }} {{ number_format($gift->price, 2) }}</span>
        @endif
    </div>

    <p style="color: #666; font-size: 14px;">
        {{ __('To keep the surprise, we won\'t tell you who claimed it!') }}
    </p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $dashboardUrl }}" style="background: #16a34a; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            {{ __('View Your Wishlist') }}
        </a>
    </p>
@endsection
