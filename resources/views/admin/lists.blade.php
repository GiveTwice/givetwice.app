@extends('admin.layout')

@section('title', 'Lists')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Lists</h1>
</div>

<div class="card mb-6">
    <form action="{{ route('admin.lists') }}" method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by list name..."
                   aria-label="Search lists"
                   class="form-input">
        </div>

        <button type="submit" class="btn-primary-sm">Search</button>

        <a href="{{ route('admin.lists') }}" class="btn-cancel {{ request('search') ? '' : 'invisible' }}">Clear</a>
    </form>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-cream-200 table-fixed">
        <thead>
            <tr class="bg-cream-50/50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[250px]">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[180px]">Creator</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Gifts</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[120px]">Collaborators</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Default</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[120px]">Created</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-[80px]">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-cream-100">
            @forelse($lists as $list)
                <tr class="hover:bg-cream-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-900 truncate max-w-[230px]">{{ $list->name }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($list->creator)
                            <a href="{{ route('admin.users.show', $list->creator) }}" class="text-sm text-coral-600 hover:text-coral-700 truncate block max-w-[160px]">{{ $list->creator->name }}</a>
                        @else
                            <span class="text-sm text-gray-400">Unknown</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $list->gifts_count }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $list->users_count }}
                    </td>
                    <td class="px-6 py-4">
                        @if($list->is_default)
                            <span class="badge badge-info text-xs">Default</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $list->created_at->format('M j, Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($list->creator && $list->creator->id !== auth()->id() && !$list->creator->is_admin)
                            <form action="{{ route('admin.impersonate', $list->creator) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="redirect_to" value="/{{ $list->creator->locale_preference ?? 'en' }}/v/{{ $list->id }}/{{ $list->slug }}">
                                <button type="submit" class="text-coral-600 hover:text-coral-700 font-medium text-sm">Impersonate</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No lists found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($lists->hasPages())
    <div class="mt-4">
        {{ $lists->links() }}
    </div>
@endif
@endsection
