@extends('emails.layouts.base')

@section('title', __('You just gave twice'))

@section('content')
<div style="background: linear-gradient(135deg, #e8f5f3, #d4ede8); padding: 24px; border-radius: 12px 12px 0 0; margin: -32px -32px 24px -32px; text-align: center;">
    <div style="font-size: 32px; margin-bottom: 8px;">✨</div>
    <h1 style="color: #1a1a1a; font-size: 22px; font-weight: 700; margin: 0;">{{ __('You just gave twice.') }}</h1>
    <p style="color: #555; font-size: 15px; margin-top: 4px;">{{ __('One gift. Two smiles.') }}</p>
</div>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ $giftTitle }} — {{ __('someone special is getting exactly what they wanted.') }}
</p>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('And charity just got a little boost too.') }}
</p>

<div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: center;">
    <p style="font-size: 14px; color: #374151; margin: 0;">
        {{ __('You didn\'t pay a cent extra. The store\'s commission? We donated all of it.') }}
    </p>
    <p style="font-size: 13px; color: #9ca3af; margin: 8px 0 0 0;">
        {{ __('You\'re basically a hero. Cape not included.') }}
    </p>
</div>

<div style="text-align: center;">
    <a href="{{ config('app.url') }}?utm_source=givetwice&utm_medium=email&utm_campaign=gave-twice" style="display: inline-block; background-color: #14b8a6; color: white; text-decoration: none; padding: 12px 28px; border-radius: 12px; font-weight: 600; font-size: 15px;">
        {{ __('Make your own wishlist') }}
    </a>
</div>
@endsection
