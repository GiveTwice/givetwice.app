@extends('emails.layouts.base')

@section('title', __('You\'ve been drawn!'))

@section('content')
<div style="background: linear-gradient(135deg, #f07060, #e85d4a); padding: 24px; border-radius: 12px 12px 0 0; margin: -32px -32px 24px -32px; text-align: center;">
    <div style="font-size: 32px; margin-bottom: 8px;">🎲</div>
    <h1 style="color: white; font-size: 22px; font-weight: 700; margin: 0;">{{ __('You\'ve been drawn!') }}</h1>
</div>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Hey') }} {{ $participant->name }},
</p>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ $exchange->organizer->name }} {{ __('started a gift exchange for') }} <strong>"{{ $exchange->name }}"</strong> {{ __('and you got picked.') }}
</p>

<p style="font-size: 15px; margin-bottom: 24px;">
    {{ __('Tap below to see who you\'re buying for.') }}
</p>

<div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
    <div style="display: flex; gap: 16px; font-size: 14px; color: #374151;">
        @if($exchange->formatBudget())
            <span>💰 {{ __('Budget') }}: {{ $exchange->formatBudget() }}</span>
        @endif
        <span>📅 {{ $exchange->event_date->format('M j, Y') }}</span>
    </div>
</div>

<div style="text-align: center; margin-bottom: 24px;">
    <a href="{{ $revealUrl }}" style="display: inline-block; background-color: #14b8a6; color: white; text-decoration: none; padding: 14px 32px; border-radius: 12px; font-weight: 600; font-size: 16px;">
        {{ __('See my draw') }} →
    </a>
</div>

<p style="text-align: center; font-size: 13px; color: #9ca3af;">
    {{ __('No account needed. Just click and see.') }}
</p>
@endsection
