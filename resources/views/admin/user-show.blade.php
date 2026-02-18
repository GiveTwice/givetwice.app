@extends('admin.layout')

@section('title', $user->name)

@section('content')
<div class="breadcrumb">
    <a href="{{ route('admin.users') }}" class="breadcrumb-link">Users</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-700">{{ $user->name }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1">
        <div class="card">
            <div class="flex items-center mb-4">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full mr-4 shrink-0">
                @else
                    <div class="w-16 h-16 rounded-full bg-coral-100 flex items-center justify-center mr-4 shrink-0">
                        <span class="text-coral-600 text-2xl font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 break-words">{{ $user->name }}</h1>
                    <p class="text-gray-500 break-words">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-1 mb-6">
                @if($user->is_admin)
                    <span class="badge badge-info">Admin</span>
                @endif
                @if($user->email_verified_at)
                    <span class="badge badge-success">Verified</span>
                @else
                    <span class="badge badge-warning">Unverified</span>
                @endif
                @if($user->google_id)
                    <span class="text-xs px-2.5 py-1 rounded-full bg-blue-100 text-blue-700">Google</span>
                @endif
                @if($user->facebook_id)
                    <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700">Facebook</span>
                @endif
            </div>

            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Joined</dt>
                    <dd class="text-gray-900">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Locale</dt>
                    <dd class="text-gray-900">{{ $user->locale_preference ?? 'Not set' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Gifts</dt>
                    <dd class="text-gray-900">{{ $user->gifts_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Lists</dt>
                    <dd class="text-gray-900">{{ $user->lists_count }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Claims</dt>
                    <dd class="text-gray-900">{{ $user->claims_count }}</dd>
                </div>
            </dl>

            @if($user->id !== auth()->id())
                <div class="mt-6 pt-6 border-t border-cream-200 space-y-3">
                    @if(!$user->is_admin)
                        <form action="{{ route('admin.impersonate', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary w-full justify-center text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                Impersonate
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                        @csrf
                        @if($user->is_admin)
                            <button type="submit" class="w-full px-4 py-2.5 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors text-sm font-medium">
                                Revoke admin access
                            </button>
                        @else
                            <button type="submit" class="w-full px-4 py-2.5 bg-cream-100 text-gray-700 rounded-xl hover:bg-cream-200 transition-colors text-sm font-medium">
                                Grant admin access
                            </button>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <div class="card !p-0">
            <div class="p-5 border-b border-cream-200">
                <h2 class="font-semibold text-gray-900">Lists ({{ $user->lists->count() }})</h2>
            </div>
            <div class="p-5">
                @forelse($user->lists as $list)
                    <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $list->name }}</p>
                            <p class="text-sm text-gray-500 truncate max-w-[300px]">{{ $list->slug }}</p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <p class="text-sm text-gray-500">{{ $list->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No lists.</p>
                @endforelse
            </div>
        </div>

        <div class="card !p-0">
            <div class="p-5 border-b border-cream-200">
                <h2 class="font-semibold text-gray-900">Recent gifts</h2>
            </div>
            <div class="p-5">
                @forelse($user->gifts as $gift)
                    <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.gifts.show', $gift) }}" class="font-medium text-gray-900 truncate block hover:text-coral-600 transition-colors">{{ $gift->title ?: 'Untitled' }}</a>
                            <p class="text-sm text-gray-500 truncate max-w-[300px]">{{ $gift->url }}</p>
                        </div>
                        <div class="text-right ml-4 shrink-0">
                            <x-fetch-status-badge :status="$gift->fetch_status" />
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No gifts.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
