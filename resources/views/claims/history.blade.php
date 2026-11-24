@extends('layouts.app')
@section('title', __('All my reservations'))
@section('robots', 'noindex, nofollow')
@section('content')
{{-- The page width follows the layout rule in CLAUDE.md. A logged-in user gets the app shell with the sidebar.
     A guest who opened the email link sees a grid of gifts, so it gets the wide card. A guest without the link only sees a form, so it gets the single card. --}}
@auth
    @php($shell = \App\Helpers\AppShellHelper::reservations(auth()->user()))
    <x-app-shell :title="__('Your account')" :sidebar-items="$shell['sidebarItems']" :stats="$shell['sidebarStats']">
        <x-app-content :title="__('All my reservations')" :description="$email ?? __('reservations.intro')">
            @if($email)
                @include('claims.history-body')
            @else
                <div class="max-w-md">@include('claims.history-form')</div>
            @endif
        </x-app-content>
    </x-app-shell>
@elseif($email)
    <x-app-content :title="__('All my reservations')" :description="$email">
        @include('claims.history-body')
    </x-app-content>
@else
    <x-page-card icon="🎁" :title="__('All my reservations')" :subtitle="__('reservations.intro')">
        @include('claims.history-form')
        <a class="btn-link mt-4 flex justify-center" href="{{ route('login', ['locale' => app()->getLocale()]) }}">{{ __('Log in') }}</a>
    </x-page-card>
@endauth
@endsection
