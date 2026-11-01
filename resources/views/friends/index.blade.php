@extends('layouts.app')

@section('title', __('Followed lists'))

@section('robots', 'noindex, nofollow')

@section('content')
@php
    $listCount = auth()->user()->lists()->count();
    $followedListCount = $groupedByOwner->flatten(1)->count();
    $shell = \App\Helpers\AppShellHelper::friends(auth()->user(), $followedListCount);
    $currentLocale = $shell['currentLocale'];
@endphp

<x-app-shell
    :title="__('Followed lists')"
    :sidebar-items="$shell['sidebarItems']"
    :stats="$shell['sidebarStats']"
>
    <x-app-content
        :title="__('Followed lists')"
        :description="__('Save individual lists here. Following is one-way. Reserving while logged in follows that list automatically.')"
    >
        <div x-data="friendsPage()">
        {{-- Global notifications toggle --}}
        <div class="flex flex-col items-start gap-3 py-4 mb-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-coral-100 flex items-center justify-center">
                    <x-icons.bell class="w-5 h-5 text-coral-600" />
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ __('Email notifications') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Get one short email after changes to a followed list.') }}</p>
                </div>
            </div>
            <div class="relative">
                <button
                    type="button"
                    x-on:click="toggleGlobalNotifications()"
                    :disabled="togglingGlobal"
                    class="btn-secondary relative min-h-11 cursor-pointer focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50"
                    :class="globalEnabled ? 'bg-coral-500 border-coral-500 text-white' : 'bg-white text-gray-700'"
                    role="switch"
                    :aria-checked="globalEnabled"
                    :style="globalEnabled ? { backgroundColor: 'var(--color-coral-500)', borderColor: 'var(--color-coral-500)', color: 'white' } : {}"
                >
                    <span>{{ __('Email updates') }}</span>
                </button>
                <div
                    x-show="globalSaved"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-sm text-teal-600 font-medium whitespace-nowrap"
                >
                    {{ __('Saved!') }}
                </div>
            </div>
        </div>

        @if($groupedByOwner->isEmpty())
            {{-- Empty state --}}
            <div class="py-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-cream-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <x-icons.users class="w-10 h-10 text-cream-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('No followed lists yet') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('Open a shared list and choose Follow. Reserving while logged in follows that list too.') }}</p>
                </div>
            </div>
        @else
            {{-- Lists grouped by owner --}}
            <div class="space-y-8">
                @foreach($groupedByOwner as $creatorId => $followedLists)
                    @php
                        $owner = $followedLists->first()->list->creator;
                    @endphp
                    <div class="border-b border-gray-200 pb-5">
                        {{-- Owner header --}}
                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-100">
                            <div class="w-10 h-10 rounded-full bg-coral-500 flex items-center justify-center text-white font-bold text-sm">
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
                                    class="flex flex-col items-stretch gap-3 py-3"
                                    x-data="{ notifications: {{ $followedList->notifications ? 'true' : 'false' }}, toggling: false, saved: false }"
                                >
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-lg bg-white border border-cream-200 flex items-center justify-center flex-shrink-0">
                                            <span class="text-base">&#127873;</span>
                                        </div>
                                        <div class="min-w-0">
                                            <a
                                                href="{{ $followedList->list->getPublicUrl() }}"
                                                class="font-medium text-gray-900 hover:text-coral-600 wrap-anywhere block"
                                            >
                                                {{ $followedList->list->name }}
                                            </a>
                                            <p class="text-sm text-gray-500">
                                                {{ trans_choice(':count gift|:count gifts', $followedList->list->gifts->count(), ['count' => $followedList->list->gifts->count()]) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-3">
                                        {{-- Per-list notification toggle --}}
                                        <div class="relative">
                                            <button
                                                type="button"
                                                x-on:click="toggleListNotifications({{ $followedList->id }}, $data)"
                                                :disabled="toggling || !globalEnabled"
                                                class="btn-secondary relative min-h-11 cursor-pointer focus:outline-none focus:ring-2 focus:ring-coral-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                                :class="notifications && globalEnabled ? 'bg-coral-500 border-coral-500 text-white' : 'bg-white text-gray-700'"
                                                role="switch"
                                                :aria-checked="notifications"
                                                :style="notifications && globalEnabled ? { backgroundColor: 'var(--color-coral-500)', borderColor: 'var(--color-coral-500)', color: 'white' } : {}"
                                                :title="globalEnabled ? '' : '{{ __('Enable global notifications first') }}'"
                                            >
                                                <span>{{ __('Email updates') }}</span>
                                            </button>
                                            <div
                                                x-show="saved"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-300"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="absolute top-full left-1/2 -translate-x-1/2 mt-1 text-sm text-teal-600 font-medium whitespace-nowrap"
                                            >
                                                {{ __('Saved!') }}
                                            </div>
                                        </div>

                                        {{-- View list link --}}
                                        <a
                                            href="{{ $followedList->list->getPublicUrl() }}"
                                            class="inline-flex min-h-11 items-center text-sm text-coral-600 hover:text-coral-700 font-medium"
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
</x-app-shell>
@endsection

@push('scripts')
<script>
function friendsPage() {
    return {
        globalEnabled: @js($globalNotificationsEnabled),
        togglingGlobal: false,
        globalSaved: false,
        globalSavedTimeout: null,

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
                    this.showGlobalSaved();
                }
            } catch (error) {
                console.error('Error toggling global notifications:', error);
            } finally {
                this.togglingGlobal = false;
            }
        },

        showGlobalSaved() {
            if (this.globalSavedTimeout) {
                clearTimeout(this.globalSavedTimeout);
            }
            this.globalSaved = true;
            this.globalSavedTimeout = setTimeout(() => {
                this.globalSaved = false;
            }, 5000);
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
                    this.showListSaved(data);
                }
            } catch (error) {
                console.error('Error toggling list notifications:', error);
            } finally {
                data.toggling = false;
            }
        },

        showListSaved(data) {
            if (data.savedTimeout) {
                clearTimeout(data.savedTimeout);
            }
            data.saved = true;
            data.savedTimeout = setTimeout(() => {
                data.saved = false;
            }, 5000);
        }
    }
}
</script>
@endpush
