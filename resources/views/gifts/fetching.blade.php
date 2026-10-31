@extends('layouts.app')

@section('title', __('Fetching Details'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $dashboardUrl = $shell['dashboardUrl'];
@endphp

<x-gift-flow-shell
    :title="__('Lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
<x-app-content
    :title="__('Fetching Details')"
    :breadcrumbs="[
        ['label' => __('Lists'), 'url' => $dashboardUrl],
        ['label' => __('Fetching Details')]
    ]"
>
    {{-- No-JS fallback: redirect to edit after 15s --}}
    <noscript>
        <meta http-equiv="refresh" content="15;url={{ route('gifts.edit', ['locale' => app()->getLocale(), 'gift' => $gift]) }}">
    </noscript>

    <div
        class="flex flex-col items-center justify-center py-16 text-center"
        x-data="giftFetching()"
    >
        <div class="mb-6">
            <x-icons.spinner class="w-12 h-12 text-coral-500 animate-spin" />
        </div>

        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('Looking at :shop...', ['shop' => $gift->siteName()]) }}</h2>
        <p class="text-gray-500 mb-4">{{ __('This usually takes a few seconds.') }}</p>

        @if($gift->siteName())
            <p class="text-sm text-gray-400">
                {{ $gift->siteName() }}
            </p>
        @endif

        <div class="w-full max-w-sm rounded-xl bg-cream-50 p-5 text-left">
            <p class="font-medium break-words">{{ $gift->title ?: $gift->suggestedTitle() }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ __('Taken from the link. Price and photo will follow.') }}</p>
        </div>
        <a x-show="canSkip" x-cloak href="{{ route('gifts.edit', ['locale' => app()->getLocale(), 'gift' => $gift, 'context' => 'fetch_timeout']) }}" class="btn-secondary mt-6 max-w-full text-wrap">{{ __("Don't wait, I'll fill it in myself") }}</a>
        <p class="mt-3 text-sm text-gray-500">{{ __('We never overwrite what you fill in.') }}</p>
    </div>

    <script>
        function giftFetching() {
            return {
                canSkip: @js($gift->created_at->diffInSeconds(now()) >= 8),
                init() {
                    setTimeout(() => this.canSkip = true, @js(max(0, 8 - $gift->created_at->diffInSeconds(now())) * 1000));
                    const editUrl = @js(route('gifts.edit', ['locale' => app()->getLocale(), 'gift' => $gift]));

                    // WebSocket listener
                    if (window.Echo) {
                        window.Echo.private('user.{{ auth()->id() }}')
                            .listen('.gift.fetch.completed', (e) => {
                                if (e.gift.id === {{ $gift->id }}) {
                                    const context = e.gift.fetch_status === 'completed'
                                        ? 'fetch_success'
                                        : 'fetch_failed';
                                    window.location.href = editUrl + '?context=' + context;
                                }
                            });
                    }

                    // Polling fallback: reload every 5s (controller redirects when done)
                    this._pollInterval = setInterval(() => {
                        window.location.reload();
                    }, 5000);
                }
            }
        }
    </script>
</x-app-content>
</x-gift-flow-shell>
@endsection
