@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Total Gifts</h3>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_gifts']) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Total Lists</h3>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_lists']) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Confirmed Claims</h3>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_claims']) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Gifts Today</h3>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['gifts_today']) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Gifts This Week</h3>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['gifts_this_week']) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-500 text-sm font-medium">Gifts This Month</h3>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['gifts_this_month']) }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="font-semibold text-gray-900">Recent Users</h2>
        </div>
        <div class="p-4">
            @forelse($recentUsers as $user)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b' : '' }}">
                    <div>
                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        @if($user->is_admin)
                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded">Admin</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No users yet.</p>
            @endforelse
        </div>
        <div class="p-4 border-t">
            <a href="{{ route('admin.users') }}" class="text-green-600 hover:text-green-700 text-sm">View all users &rarr;</a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="font-semibold text-gray-900">Recent Gifts</h2>
        </div>
        <div class="p-4">
            @forelse($recentGifts as $gift)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b' : '' }}">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $gift->title ?: 'Untitled' }}</p>
                        <p class="text-sm text-gray-500">by {{ $gift->user->name }}</p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-sm text-gray-500">{{ $gift->created_at->diffForHumans() }}</p>
                        <span class="text-xs px-2 py-0.5 rounded
                            {{ $gift->fetch_status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $gift->fetch_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $gift->fetch_status === 'fetching' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $gift->fetch_status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                        ">{{ ucfirst($gift->fetch_status) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No gifts yet.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="mt-6 bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="font-semibold text-gray-900">Recent Claims</h2>
    </div>
    <div class="p-4">
        @forelse($recentClaims as $claim)
            <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b' : '' }}">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 truncate">{{ $claim->gift->title ?: 'Untitled gift' }}</p>
                    <p class="text-sm text-gray-500">
                        @if($claim->user)
                            by {{ $claim->user->name }}
                        @else
                            by {{ $claim->claimer_email }}
                        @endif
                    </p>
                </div>
                <div class="text-right ml-4">
                    <p class="text-sm text-gray-500">{{ $claim->confirmed_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No claims yet.</p>
        @endforelse
    </div>
</div>
@endsection
