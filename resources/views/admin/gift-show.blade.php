@extends('admin.layout')

@section('title', $gift->title ?: 'Gift #' . $gift->id)

@php
    $hasDebug = $gift->fetch_status === 'failed' || $gift->fetch_error;
@endphp

@section('content')
<div class="breadcrumb">
    <a href="{{ route('admin.gifts') }}" class="breadcrumb-link">Gifts</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700 truncate max-w-[200px]">{{ $gift->title ?: 'Gift #' . $gift->id }}</span>
</div>

<div x-data="{ tab: 'main' }">
    @if($hasDebug)
        <div class="flex gap-1 p-1.5 mb-6 bg-cream-100 rounded-xl">
            <button
                @click="tab = 'main'"
                :class="tab === 'main' ? 'bg-white text-coral-600 shadow-sm' : 'text-gray-500 hover:text-coral-500'"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all"
            >
                Details
            </button>
            <button
                @click="tab = 'debug'"
                :class="tab === 'debug' ? 'bg-white text-coral-600 shadow-sm' : 'text-gray-500 hover:text-coral-500'"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-1.5"
            >
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                Debug
            </button>
        </div>
    @endif

    <div x-show="tab === 'main'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1 space-y-6">
                <div class="card">
                    <div class="flex gap-4 items-start">
                        @if($gift->hasImage())
                            <img src="{{ $gift->getImageUrl('thumb') }}" alt="{{ $gift->title }}" class="w-16 h-16 rounded-lg object-cover bg-cream-100 shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-lg bg-cream-100 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-cream-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold text-gray-900 break-words">{{ $gift->title ?: 'Untitled' }}</h1>
                            @if($gift->description)
                                <p class="text-gray-600 mt-1 break-words text-sm line-clamp-2">{{ $gift->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Owner</dt>
                            <dd><a href="{{ route('admin.users.show', $gift->user) }}" class="text-coral-600 hover:text-coral-700">{{ $gift->user->name }}</a></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Price</dt>
                            <dd class="text-gray-900">{{ $gift->hasPrice() ? $gift->formatPrice() : 'Not set' }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-500">Fetch status</dt>
                            <dd>
                                <x-fetch-status-badge :status="$gift->fetch_status" />
                            </dd>
                        </div>
                        @if($gift->url)
                            <div>
                                <dt class="text-gray-500 mb-1">URL</dt>
                                <dd><a href="{{ $gift->url }}" target="_blank" class="text-coral-600 hover:text-coral-700 break-all text-xs">{{ $gift->url }}</a></dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Created</dt>
                            <dd class="text-gray-900">{{ $gift->created_at->format('M j, Y H:i') }}</dd>
                        </div>
                        @if($gift->fetched_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Fetched</dt>
                                <dd class="text-gray-900">{{ $gift->fetched_at->format('M j, Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if($gift->fetch_status === 'failed')
                        <div class="mt-4 pt-4 border-t border-cream-200">
                            <form action="{{ route('admin.gifts.refresh', $gift) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary-sm w-full justify-center">Retry fetch</button>
                            </form>
                        </div>
                    @endif

                    @if($gift->user->id !== auth()->id() && !$gift->user->is_admin)
                        <div class="mt-4 pt-4 border-t border-cream-200">
                            <form action="{{ route('admin.impersonate', $gift->user) }}" method="POST">
                                @csrf
                                <input type="hidden" name="redirect_to" value="/{{ $gift->user->locale_preference ?? 'en' }}/gifts/{{ $gift->id }}/edit">
                                <button type="submit" class="btn-secondary w-full justify-center text-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                    </svg>
                                    Impersonate & view gift
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">

                <div class="card !p-0">
                    <div class="p-5 border-b border-cream-200">
                        <h2 class="font-semibold text-gray-900">Claims ({{ $gift->claims->count() }})</h2>
                    </div>
                    <div class="p-5">
                        @forelse($gift->claims as $claim)
                            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">
                                        @if($claim->user)
                                            <a href="{{ route('admin.users.show', $claim->user) }}" class="hover:text-coral-600 transition-colors">{{ $claim->user->name }}</a>
                                        @else
                                            {{ $claim->claimer_name ?? $claim->claimer_email }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-500 truncate max-w-[250px]">{{ $claim->claimer_email }}</p>
                                </div>
                                <div class="text-right ml-4 shrink-0">
                                    @if($claim->confirmed_at)
                                        <span class="badge badge-success text-xs">Confirmed</span>
                                        <p class="text-xs text-gray-500 mt-1">{{ $claim->confirmed_at->format('M j, Y') }}</p>
                                    @else
                                        <span class="badge badge-warning text-xs">Pending</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No claims for this gift.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card !p-0">
                    <div class="p-5 border-b border-cream-200">
                        <h2 class="font-semibold text-gray-900">Lists ({{ $gift->lists->count() }})</h2>
                    </div>
                    <div class="p-5">
                        @forelse($gift->lists as $list)
                            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $list->name }}</p>
                                    <p class="text-sm text-gray-500 truncate max-w-[300px]">{{ $list->slug }}</p>
                                </div>
                                <div class="text-right ml-4 shrink-0">
                                    @if($list->is_default)
                                        <span class="badge badge-info text-xs">Default</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">Not added to any list.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($hasDebug)
        <div x-show="tab === 'debug'" x-cloak>
            <div class="rounded-xl border border-red-200 bg-red-50/50 overflow-hidden">
                <div class="px-5 py-3 bg-red-100/60 border-b border-red-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <h3 class="font-semibold text-red-800 text-sm">Fetch debug info</h3>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-red-700">
                        <span>Attempts: <strong>{{ $gift->fetch_attempts }}</strong></span>
                        @if($gift->fetched_at)
                            <span>Last attempt: <strong>{{ $gift->fetched_at->format('M j, Y H:i:s') }}</strong></span>
                        @endif
                    </div>
                </div>
                @if($gift->fetch_error)
                    <div class="p-4">
                        @if($gift->fetch_error['summary'] ?? null)
                            <p class="text-sm font-medium text-red-800 mb-3 font-mono">{{ $gift->fetch_error['summary'] }}</p>
                        @endif
                        <pre class="text-xs bg-gray-900 text-green-400 rounded-lg p-4 leading-relaxed whitespace-pre-wrap break-words max-h-[600px] overflow-y-auto"><code>{{ json_encode($gift->fetch_error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                @else
                    <div class="p-4">
                        <p class="text-sm text-red-700">No error details recorded.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
