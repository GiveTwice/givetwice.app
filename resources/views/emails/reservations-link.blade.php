@extends('emails.layouts.base')
@section('title', __('All my reservations'))
@section('content')
<h1>{{ __('All my reservations') }}</h1>
<p>{{ __('reservations.intro') }}</p>
<p><a href="{{ $historyUrl }}">{{ __('All my reservations') }}</a></p>
@endsection
