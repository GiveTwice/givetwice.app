@extends('admin.layout')

@section('title', $user->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users') }}" class="text-gray-500 hover:text-gray-700">&larr; Back to Users</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full mr-4">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                        <span class="text-gray-500 text-2xl font-medium">{{ substr($user->name, 0, 1) }}</span>
                    </div>
                @endif
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-500">{{ $user->email }}</p>
                </div>
            </div>

            <div class="space-y-2 mb-6">
                <div class="flex flex-wrap gap-1">
                    @if($user->is_admin)
                        <span class="px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-800">Admin</span>
                    @endif
                    @if($user->email_verified_at)
                        <span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Verified</span>
                    @else
                        <span class="px-2 py-0.5 text-xs rounded bg-yellow-100 text-yellow-800">Unverified</span>
                    @endif
                    @if($user->google_id)
                        <span class="px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800">Google</span>
                    @endif
                    @if($user->facebook_id)
                        <span class="px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-800">Facebook</span>
                    @endif
                </div>
            </div>

            <dl class="space-y-2 text-sm">
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
                <div class="mt-6 pt-6 border-t">
                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST">
                        @csrf
                        @if($user->is_admin)
                            <button type="submit" class="w-full px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                Revoke Admin Access
                            </button>
                        @else
                            <button type="submit" class="w-full px-4 py-2 bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                Grant Admin Access
                            </button>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h2 class="font-semibold text-gray-900">Lists ({{ $user->lists->count() }})</h2>
            </div>
            <div class="p-4">
                @forelse($user->lists as $list)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <div>
                            <p class="font-medium text-gray-900">{{ $list->name }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $list->slug }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">{{ $list->created_at->format('M j, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No lists.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="p-4 border-b">
                <h2 class="font-semibold text-gray-900">Recent Gifts</h2>
            </div>
            <div class="p-4">
                @forelse($user->gifts as $gift)
                    <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b' : '' }}">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ $gift->title ?: 'Untitled' }}</p>
                            <p class="text-sm text-gray-500 truncate">{{ $gift->url }}</p>
                        </div>
                        <div class="text-right ml-4">
                            <span class="text-xs px-2 py-0.5 rounded
                                {{ $gift->fetch_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $gift->fetch_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $gift->fetch_status === 'fetching' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $gift->fetch_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                            ">{{ ucfirst($gift->fetch_status) }}</span>
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
