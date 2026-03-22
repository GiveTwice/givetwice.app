@extends('emails.layouts.base')

@section('title', __('Quick hint for whoever drew you'))

@section('content')
<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Hey') }} {{ $participant->name }},
</p>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Someone in your group is right now staring at their phone, wondering what to get you. You could help them. Or you could enjoy watching them panic.') }}
</p>

<p style="font-size: 15px; margin-bottom: 24px;">
    {{ __('Drop a few ideas on a wishlist — takes about a minute. And when they buy from it, we\'ll donate to charity. Everyone wins. Especially charity.') }}
</p>

<div style="text-align: center; margin-bottom: 24px;">
    <a href="{{ $registerUrl }}" style="display: inline-block; background-color: #f07060; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 600; font-size: 16px;">
        {{ __('Make a wishlist') }} →
    </a>
</div>
@endsection
