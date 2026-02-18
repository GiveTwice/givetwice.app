@extends('admin.layout')

@section('title', 'Gifts')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Gifts</h1>
</div>

<div class="card mb-6">
    <form action="{{ route('admin.gifts') }}" method="GET" class="space-y-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by title or URL..."
                       aria-label="Search gifts"
                       class="form-input">
            </div>

            <select name="status" aria-label="Filter by status" class="form-select w-auto">
                <option value="">All statuses</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="fetching" {{ request('status') === 'fetching' ? 'selected' : '' }}>Fetching</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary-sm">Search</button>
            <a href="{{ route('admin.gifts') }}" class="btn-cancel {{ request()->hasAny(['search', 'status']) ? '' : 'invisible' }}">Clear</a>
        </div>
    </form>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-cream-200 table-fixed">
        <thead>
            <tr class="bg-cream-50/50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[250px]">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[250px]">URL</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-cream-100">
            @forelse($gifts as $gift)
                <tr class="hover:bg-cream-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-900 truncate max-w-[230px]">{{ $gift->title ?: 'Untitled' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 truncate max-w-[130px]">{{ $gift->user->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($gift->url)
                            <a href="{{ $gift->url }}" target="_blank" class="text-sm text-coral-600 hover:text-coral-700 truncate block max-w-[230px]">{{ $gift->url }}</a>
                        @else
                            <span class="text-sm text-gray-400">No URL</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <x-fetch-status-badge :status="$gift->fetch_status" />
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $gift->hasPrice() ? $gift->formatPrice() : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $gift->created_at->format('M j, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('admin.gifts.show', $gift) }}" class="text-coral-600 hover:text-coral-700 font-medium">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No gifts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($gifts->hasPages())
    <div class="mt-4">
        {{ $gifts->links() }}
    </div>
@endif
@endsection
