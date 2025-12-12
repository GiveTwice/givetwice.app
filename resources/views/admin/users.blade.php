@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Users</h1>
</div>

{{-- Search and Filter --}}
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or email..."
                   aria-label="Search users"
                   class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <select name="filter" aria-label="Filter users" class="px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All Users</option>
            <option value="admin" {{ request('filter') === 'admin' ? 'selected' : '' }}>Admins Only</option>
            <option value="verified" {{ request('filter') === 'verified' ? 'selected' : '' }}>Verified</option>
            <option value="unverified" {{ request('filter') === 'unverified' ? 'selected' : '' }}>Unverified</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Search
        </button>

        @if(request()->hasAny(['search', 'filter']))
            <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                Clear
            </a>
        @endif
    </form>
</div>

{{-- Users Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="" class="w-10 h-10 rounded-full mr-3">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <span class="text-gray-500 font-medium">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->gifts_count }} gifts, {{ $user->lists_count }} lists, {{ $user->claims_count }} claims
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->created_at->format('M j, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-green-600 hover:text-green-700">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($users->hasPages())
    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endif
@endsection
