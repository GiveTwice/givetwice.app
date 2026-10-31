@extends('layouts.app')
@section('title', __('Reserve this gift'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto max-w-md rounded-2xl bg-white p-5" x-data="reservationSheet" x-on:reservation-submit="send($event.detail.form)">
    <p x-show="error" x-text="error" role="alert" class="text-sm text-red-700"></p>
    <div x-ref="content">@include('claims.sheet-content')</div>
</div>
@endsection
