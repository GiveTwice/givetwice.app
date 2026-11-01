@extends('layouts.app')
@section('title', __('Page Expired'))
@section('robots', 'noindex, nofollow')
@section('content')
<div class="mx-auto flex max-w-lg flex-col gap-4 py-8">
    <p class="text-sm font-medium text-gray-600">419</p>
    <h1 class="text-2xl font-bold text-gray-900">{{ __('Page Expired') }}</h1>
    <p class="text-gray-600">{{ __('Your session expired. Open the page again and retry.') }}</p>
    <a href="{{ url()->current() }}" class="btn-primary">{{ __('Try again') }}</a>
    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn-link">{{ __('Back to Home') }}</a>
</div>
@endsection
