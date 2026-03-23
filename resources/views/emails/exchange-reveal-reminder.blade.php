@extends('emails.layouts.base')

@section('title', __('Your draw is getting lonely'))

@section('content')
<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Hey') }} {{ $participant->name }},
</p>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Someone in') }} <strong>"{{ $exchange->name }}"</strong> {{ __('is waiting to find out who\'s buying for them. That someone drew you. Don\'t leave them hanging.') }}
</p>

<div style="text-align: center; margin: 24px 0;">
    <a href="{{ $revealUrl }}" style="display: inline-block; background-color: #14b8a6; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 600; font-size: 16px;">
        {{ __('See my draw') }} →
    </a>
</div>

<p style="font-size: 13px; color: #9ca3af; text-align: center;">
    @if($exchange->event_date)
        {{ __('We\'re not saying there\'s a deadline. But there\'s definitely a deadline.') }} ({{ $exchange->event_date->format('M j') }})
    @endif
</p>
@endsection
