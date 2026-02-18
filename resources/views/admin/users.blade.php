@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Users</h1>
</div>

<div class="card mb-6">
    <form action="{{ route('admin.users') }}" method="GET" class="space-y-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name or email..."
                       aria-label="Search users"
                       class="form-input">
            </div>

            <select name="filter" aria-label="Filter users" class="form-select w-auto">
                <option value="">All Users</option>
                <option value="admin" {{ request('filter') === 'admin' ? 'selected' : '' }}>Admins Only</option>
                <option value="verified" {{ request('filter') === 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="unverified" {{ request('filter') === 'unverified' ? 'selected' : '' }}>Unverified</option>
            </select>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary-sm">Search</button>
            <a href="{{ route('admin.users') }}" class="btn-cancel {{ request()->hasAny(['search', 'filter']) ? '' : 'invisible' }}">Clear</a>
        </div>
    </form>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-cream-200 table-fixed">
        <thead>
            <tr class="bg-cream-50/50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[280px]">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[200px]">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[200px]">Activity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[120px]">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-cream-100">
            @forelse($users as $user)
                <tr class="hover:bg-cream-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full mr-3 shrink-0">
                            @else
                                <div class="w-10 h-10 rounded-full bg-coral-100 flex items-center justify-center mr-3 shrink-0">
                                    <span class="text-coral-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 truncate max-w-[180px]">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500 truncate max-w-[180px]">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @if($user->is_admin)
                                <span class="badge badge-info text-xs">Admin</span>
                            @endif
                            @if($user->email_verified_at)
                                <span class="badge badge-success text-xs">Verified</span>
                            @else
                                <span class="badge badge-warning text-xs">Unverified</span>
                            @endif
                            @if($user->google_id)
                                <span class="badge badge-info text-xs">Google</span>
                            @endif
                            @if($user->facebook_id)
                                <span class="badge badge-info text-xs">Facebook</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $user->gifts_count }} gifts, {{ $user->lists_count }} lists, {{ $user->claims_count }} claims
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $user->created_at->format('M j, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-coral-600 hover:text-coral-700 font-medium">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endif
@endsection
