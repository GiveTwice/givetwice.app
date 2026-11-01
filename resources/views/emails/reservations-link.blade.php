@extends('emails.layouts.base')
@section('title', __('All my reservations'))
@section('content')
<h1>{{ __('All my reservations') }}</h1>
<p>{{ __('Your reservations are one click away. This link works for 30 minutes.') }}</p>
<p><a href="{{ $historyUrl }}">{{ __('All my reservations') }}</a></p>
@endsection
