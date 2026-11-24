@extends('layouts.app')
@section('title', __('Undo reservation'))
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card icon="↩️" :title="__('Undo reservation')" :subtitle="$claim->reservation['title'] ?? $claim->gift?->title">
    <form method="POST" action="{{ request()->fullUrl() }}">@csrf<button class="btn-primary w-full justify-center">{{ __('Undo reservation') }}</button></form>
</x-page-card>
@endsection
