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
    :full-width="true"
>
    {{-- No-JS fallback: redirect to edit after 15s --}}
    <noscript>
        <meta http-equiv="refresh" content="15;url={{ route('gifts.edit', ['locale' => app()->getLocale(), 'gift' => $gift]) }}">
    </noscript>

    <div x-data="giftFetching()">
        <div class="space-y-5 p-4 sm:px-6 sm:py-6">
            <div class="flex items-start gap-3">
                <x-icons.spinner class="mt-0.5 h-5 w-5 shrink-0 animate-spin text-coral-500" />
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900">{{ __('Looking at :shop...', ['shop' => $gift->siteName()]) }}</p>
                    <p class="mt-0.5 text-sm text-gray-500">{{ __('This usually takes a few seconds.') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border border-cream-200 bg-cream-50 p-4">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg border border-cream-200 bg-white text-gray-400">
                    <x-icons.image-placeholder class="h-8 w-8" />
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-gray-900 break-words">{{ $gift->title ?: $gift->suggestedTitle() }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Taken from the link. Price and photo will follow.') }}</p>
                </div>
            </div>
        </div>

        <div class="flex min-h-[4.25rem] flex-col gap-3 border-t border-gray-100 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-sm text-gray-500">{{ __('We never overwrite what you fill in.') }}</p>
            <a x-show="canSkip" x-cloak href="{{ route('gifts.edit', ['locale' => app()->getLocale(), 'gift' => $gift, 'context' => 'fetch_timeout']) }}" class="btn-secondary shrink-0 text-wrap">{{ __("Don't wait, I'll fill it in myself") }}</a>
        </div>
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
