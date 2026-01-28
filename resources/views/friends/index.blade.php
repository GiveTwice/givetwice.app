@extends('layouts.app')

@section('title', __('Friends\' wishlists'))

@section('robots', 'noindex, nofollow')

@section('content')
<x-app-content
    :title="__('Friends\' wishlists')"
    :description="__('Wishlists from people you\'ve claimed gifts from.')"
    :breadcrumbs="[
        ['label' => __('Dashboard'), 'url' => url('/' . app()->getLocale() . '/dashboard')],
        ['label' => __('Friends\' wishlists')]
    ]"
>
    <div x-data="friendsPage()">
        {{-- Global notifications toggle --}}
        <div class="flex items-center justify-between p-4 bg-cream-50 rounded-xl mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-coral-100 flex items-center justify-center">
                    <x-icons.bell class="w-5 h-5 text-coral-600" />
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ __('Email notifications') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Receive daily updates when friends update their wishlists') }}</p>
                </div>
            </div>
            <button
                type="button"
                x-on:click="toggleGlobalNotifications()"
                :disabled="togglingGlobal"
                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50"
                :class="globalEnabled ? 'bg-coral-500' : 'bg-gray-200'"
                role="switch"
                :aria-checked="globalEnabled"
            >
                <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                    :class="globalEnabled ? 'translate-x-5' : 'translate-x-0'"
                ></span>
            </button>
        </div>

        @if($groupedByOwner->isEmpty())
            {{-- Empty state --}}
            <div class="py-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icons.users class="w-10 h-10 text-cream-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No friends\' wishlists yet') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('When you claim gifts from someone\'s wishlist, their list will appear here so you can stay updated on their wishes.') }}</p>
                </div>
            </div>
        @else
            {{-- Lists grouped by owner --}}
            <div class="space-y-8">
                @foreach($groupedByOwner as $creatorId => $followedLists)
                    @php
                        $owner = $followedLists->first()->list->creator;
                    @endphp
                    <div class="card">
                        {{-- Owner header --}}
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-coral-400 to-coral-500 flex items-center justify-center text-white font-bold text-sm">
                                {{ substr($owner->name, 0, 2) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $owner->name }}</p>
                                <p class="text-sm text-gray-500">{{ trans_choice(':count wishlist|:count wishlists', $followedLists->count(), ['count' => $followedLists->count()]) }}</p>
                            </div>
                        </div>

                        {{-- Lists --}}
                        <div class="space-y-3">
                            @foreach($followedLists as $followedList)
                                <div
                                    class="flex items-center justify-between p-3 bg-cream-50/50 rounded-lg"
                                    x-data="{ notifications: {{ $followedList->notifications ? 'true' : 'false' }}, toggling: false }"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-white border border-cream-200 flex items-center justify-center flex-shrink-0">
                                            <span class="text-base">&#127873;</span>
                                        </div>
                                        <div class="min-w-0">
                                            <a
                                                href="{{ $followedList->list->getPublicUrl() }}"
                                                class="font-medium text-gray-900 hover:text-coral-600 truncate block"
                                            >
                                                {{ $followedList->list->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">
                                                {{ trans_choice(':count gift|:count gifts', $followedList->list->gifts->count(), ['count' => $followedList->list->gifts->count()]) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        {{-- Per-list notification toggle --}}
                                        <button
                                            type="button"
                                            x-on:click="toggleListNotifications({{ $followedList->id }}, $data)"
                                            :disabled="toggling || !globalEnabled"
                                            class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                            :class="notifications && globalEnabled ? 'bg-coral-500' : 'bg-gray-200'"
                                            role="switch"
                                            :aria-checked="notifications"
                                            :title="globalEnabled ? '' : '{{ __('Enable global notifications first') }}'"
                                        >
                                            <span
                                                class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="notifications && globalEnabled ? 'translate-x-4' : 'translate-x-0'"
                                            ></span>
                                        </button>

                                        {{-- View list link --}}
                                        <a
                                            href="{{ $followedList->list->getPublicUrl() }}"
                                            class="text-sm text-coral-600 hover:text-coral-700 font-medium"
                                        >
                                            {{ __('View') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-content>
@endsection

@push('scripts')
<script>
function friendsPage() {
    return {
        globalEnabled: @js($globalNotificationsEnabled),
        togglingGlobal: false,

        async toggleGlobalNotifications() {
            this.togglingGlobal = true;

            try {
                const response = await fetch('{{ route('friends.notifications.global', ['locale' => app()->getLocale()]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.globalEnabled = data.enabled;
                }
            } catch (error) {
                console.error('Error toggling global notifications:', error);
            } finally {
                this.togglingGlobal = false;
            }
        },

        async toggleListNotifications(followedListId, data) {
            data.toggling = true;

            try {
                const response = await fetch(`{{ url('/' . app()->getLocale() . '/friends') }}/${followedListId}/notifications`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    data.notifications = result.notifications;
                }
            } catch (error) {
                console.error('Error toggling list notifications:', error);
            } finally {
                data.toggling = false;
            }
        }
    }
}
</script>
@endpush
