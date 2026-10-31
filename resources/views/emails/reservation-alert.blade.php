@extends('emails.layouts.base')
@section('title', __('reservations.changed', ['owner' => $alert->before['owner']]))
@section('content')
<h1 style="font-size:22px">{{ __('reservations.'.($alert->after['deleted'] ? 'deleted' : 'changed'), ['owner' => $alert->before['owner']]) }}</h1>
<p>{{ $alert->before['title'] }}</p>
@include('claims.change-values')
@if($alert->after['deleted'])<p>{{ __('reservations.keep_done', ['owner' => $alert->before['owner']]) }}</p>@endif
@foreach($alert->after['deleted'] ? ['keep', 'alternative', 'dismiss'] : ['new', 'old', 'release'] as $choice)
    <p><a style="display:block;padding:12px;background:#FFF0ED;border-radius:8px;color:#b83a2b;text-align:center;text-decoration:none" href="{{ $alert->choiceUrl($choice) }}">{{ __('reservations.'.$choice) }}</a></p>
@endforeach
<p><a href="{{ $alert->claim->historyUrl($alert->locale) }}">{{ __('All my reservations') }}</a></p>
<p style="font-size:12px"><a href="{{ $alert->choiceUrl('mute') }}">{{ __('reservations.mute', ['owner' => $alert->before['owner']]) }}</a></p>
@endsection
