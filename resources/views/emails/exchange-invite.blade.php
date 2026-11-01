@extends('emails.layouts.base')

@section('title', __('You\'ve been drawn!'))

@section('content')
<x-mail.heading>{{ __('You\'ve been drawn!') }}</x-mail.heading>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ __('Hey') }} {{ $participant->name }},
</p>

<p style="font-size: 15px; margin-bottom: 16px;">
    {{ $exchange->organizer->name }} {{ __('started a gift exchange for') }} <strong>"{{ str_replace("\u{2014}", '·', $exchange->name) }}"</strong> {{ __('and you got picked.') }}
</p>

<p style="font-size: 15px; margin-bottom: 24px;">
    {{ __('Tap below to see who you\'re buying for.') }}
</p>

<x-mail.panel>
    <h2 style="color: #0f766e; font-size: 16px; line-height: 1.4; font-weight: 600; margin: 0 0 16px;">{{ str_replace("\u{2014}", '·', $exchange->name) }}</h2>
    @if($exchange->formatBudget())
        <p style="margin: 0;">{{ __('Budget') }}: {{ $exchange->formatBudget() }}</p>
    @endif
    @if($exchange->event_date)
        <p style="margin: 0;">{{ $exchange->event_date->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</p>
    @endif
</x-mail.panel>
<x-mail.action :url="$revealUrl" :button="true">{{ __('See my draw') }}</x-mail.action>

<p style="text-align: center; font-size: 14px; color: #6b7280;">
    {{ __('No account needed. Just click and see.') }}
</p>
@endsection
