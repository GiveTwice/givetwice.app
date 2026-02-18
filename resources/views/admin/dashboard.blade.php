@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <a href="{{ route('admin.users') }}" class="card card-link flex items-center gap-4">
        <div class="icon-circle-lg bg-coral-100 text-coral-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total users</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
        </div>
    </a>

    <a href="{{ route('admin.gifts') }}" class="card card-link flex items-center gap-4">
        <div class="icon-circle-lg bg-teal-100 text-teal-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total gifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_gifts']) }}</p>
        </div>
    </a>

    <a href="{{ route('admin.lists') }}" class="card card-link flex items-center gap-4">
        <div class="icon-circle-lg bg-sunny-100 text-sunny-700">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total lists</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_lists']) }}</p>
        </div>
    </a>

    <div class="card flex items-center gap-4">
        <div class="icon-circle-lg bg-teal-100 text-teal-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Confirmed claims</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_claims']) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
    <a href="{{ route('admin.gifts') }}" class="card card-link">
        <p class="text-sm text-gray-500 mb-1">Gifts today</p>
        <p class="text-xl font-bold text-gray-900">{{ number_format($stats['gifts_today']) }}</p>
    </a>
    <a href="{{ route('admin.gifts', ['status' => 'pending']) }}" class="card card-link">
        <p class="text-sm text-gray-500 mb-1">Pending fetches</p>
        <p class="text-xl font-bold text-gray-900">{{ number_format($stats['pending_fetches']) }}</p>
    </a>
    <a href="{{ route('admin.gifts', ['status' => 'failed']) }}" class="card card-link">
        <p class="text-sm text-gray-500 mb-1">Failed fetches</p>
        <p class="text-xl font-bold {{ $stats['failed_fetches'] > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($stats['failed_fetches']) }}</p>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="card" x-data="adminChart('signups')" x-init="initChart()">
        <h3 class="text-sm font-medium text-gray-500 mb-3">Signups (60 days)</h3>
        <div class="relative h-40"><canvas x-ref="canvas"></canvas></div>
    </div>
    <div class="card" x-data="adminChart('gifts')" x-init="initChart()">
        <h3 class="text-sm font-medium text-gray-500 mb-3">Gifts (60 days)</h3>
        <div class="relative h-40"><canvas x-ref="canvas"></canvas></div>
    </div>
    <div class="card" x-data="adminChart('claims')" x-init="initChart()">
        <h3 class="text-sm font-medium text-gray-500 mb-3">Claims (60 days)</h3>
        <div class="relative h-40"><canvas x-ref="canvas"></canvas></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card !p-0">
        <div class="p-5 border-b border-cream-200">
            <h2 class="font-semibold text-gray-900">Recent users</h2>
        </div>
        <div class="p-5">
            @forelse($recentUsers as $user)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500 truncate max-w-[200px]">{{ $user->email }}</p>
                    </div>
                    <div class="text-right ml-4 shrink-0">
                        <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                        @if($user->is_admin)
                            <span class="badge badge-info text-xs">Admin</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No users yet.</p>
            @endforelse
        </div>
        <div class="px-5 py-3 border-t border-cream-200">
            <a href="{{ route('admin.users') }}" class="text-coral-600 hover:text-coral-700 text-sm font-medium">View all users &rarr;</a>
        </div>
    </div>

    <div class="card !p-0">
        <div class="p-5 border-b border-cream-200">
            <h2 class="font-semibold text-gray-900">Recent gifts</h2>
        </div>
        <div class="p-5">
            @forelse($recentGifts as $gift)
                <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $gift->title ?: 'Untitled' }}</p>
                        <p class="text-sm text-gray-500">by {{ $gift->user->name }}</p>
                    </div>
                    <div class="text-right ml-4 shrink-0">
                        <p class="text-sm text-gray-500">{{ $gift->created_at->diffForHumans() }}</p>
                        <x-fetch-status-badge :status="$gift->fetch_status" />
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No gifts yet.</p>
            @endforelse
        </div>
        <div class="px-5 py-3 border-t border-cream-200">
            <a href="{{ route('admin.gifts') }}" class="text-coral-600 hover:text-coral-700 text-sm font-medium">View all gifts &rarr;</a>
        </div>
    </div>
</div>

<div class="mt-6 card !p-0">
    <div class="p-5 border-b border-cream-200">
        <h2 class="font-semibold text-gray-900">Recent claims</h2>
    </div>
    <div class="p-5">
        @forelse($recentClaims as $claim)
            <div class="flex items-center justify-between py-2.5 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
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
                <div class="text-right ml-4 shrink-0">
                    <p class="text-sm text-gray-500">{{ $claim->confirmed_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No claims yet.</p>
        @endforelse
    </div>
</div>
@endsection

@push('head-scripts')
    @vite('resources/js/admin-charts.js')
@endpush

@push('scripts')
    <script id="admin-chart-data" type="application/json">@json($chartData)</script>
@endpush
