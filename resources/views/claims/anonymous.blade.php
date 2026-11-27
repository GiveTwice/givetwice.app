@extends('layouts.app')
@section('title', __('Reserve this gift'))
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card class="relative" x-data="reservationSheet" x-on:reservation-submit="send($event.detail.form)">
    <p x-show="error" x-text="error" role="alert" class="text-sm text-red-700"></p>
    <div x-ref="content">@include('claims.sheet-content')</div>
    @include('claims.busy-overlay')
</x-page-card>
@endsection
