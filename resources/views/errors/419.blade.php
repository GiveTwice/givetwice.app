@extends('layouts.app')
@section('title', __('Page Expired'))
@section('robots', 'noindex, nofollow')
@section('content')
<x-page-card icon="⌛" :title="__('Page Expired')" subtitle="419">
    <p class="text-gray-600">{{ __('Your session expired. Open the page again and retry.') }}</p>
    <div class="mt-6 flex flex-col gap-3">
        <a href="{{ url()->current() }}" class="btn-primary justify-center">{{ __('Try again') }}</a>
        <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="btn-link justify-center">{{ __('Back to Home') }}</a>
    </div>
</x-page-card>
@endsection
