@extends('layouts.app')

@section('title', __('Fetching Details'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $shell = \App\Helpers\AppShellHelper::lists(auth()->user());
    $dashboardUrl = $shell['dashboardUrl'];
@endphp

<x-app-shell
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

        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('Fetching details...') }}</h2>
        <p class="text-gray-500 mb-4">{{ __('This usually takes a few seconds.') }}</p>

        @if($gift->siteName())
            <p class="text-sm text-gray-400">
                {{ $gift->siteName() }}
            </p>
        @endif

        @if($gift->url)
            <p class="text-sm text-gray-400 mt-2 truncate max-w-md">
                {{ Str::limit($gift->url, 80) }}
            </p>
        @endif
    </div>

    <script>
        function giftFetching() {
            return {
                init() {
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

                    // 30s timeout fallback
                    setTimeout(() => {
                        clearInterval(this._pollInterval);
                        window.location.href = editUrl + '?context=fetch_timeout';
                    }, 30000);
                }
            }
        }
    </script>
</x-app-content>
</x-app-shell>
@endsection
