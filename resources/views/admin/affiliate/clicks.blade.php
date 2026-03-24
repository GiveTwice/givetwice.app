@extends('admin.layout')

@section('title', 'Affiliate Clicks')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Affiliate Clicks</h1>
    <a href="{{ route('admin.affiliate.dashboard') }}" class="text-coral-600 hover:text-coral-700 text-sm font-medium">&larr; Back to dashboard</a>
</div>

<div class="card mb-6">
    <form action="{{ route('admin.affiliate.clicks') }}" method="GET" class="space-y-4">
        <div class="flex flex-wrap gap-4">
            <select name="retailer" aria-label="Filter by retailer" class="form-select w-auto">
                <option value="">All retailers</option>
                @foreach($retailers as $retailer)
                    <option value="{{ $retailer }}" {{ request('retailer') === $retailer ? 'selected' : '' }}>{{ $retailer }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-auto" placeholder="From">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-auto" placeholder="To">
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary-sm">Filter</button>
            <a href="{{ route('admin.affiliate.clicks') }}" class="btn-cancel {{ request()->hasAny(['retailer', 'date_from', 'date_to']) ? '' : 'invisible' }}">Clear</a>
        </div>
    </form>
</div>

<div class="card !p-0 overflow-x-auto">
    <table class="min-w-full divide-y divide-cream-200 table-fixed">
        <thead>
            <tr class="bg-cream-50/50">
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[180px]">Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[200px]">Gift</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">Retailer</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-cream-100">
            @forelse($clicks as $click)
                <tr class="hover:bg-cream-50/30 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $click->clicked_at->format('M j, H:i') }}
                    </td>
                    <td class="px-6 py-4">
                        @if($click->gift)
                            <a href="{{ route('admin.gifts.show', $click->gift) }}" class="text-sm font-medium text-coral-600 hover:text-coral-700 truncate block max-w-[180px]">
                                {{ $click->gift->title ?: 'Untitled' }}
                            </a>
                        @else
                            <span class="text-sm text-gray-400">Deleted gift</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $click->retailer_domain }}
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ $click->url }}" target="_blank" class="text-sm text-coral-600 hover:text-coral-700 truncate block max-w-[300px]">{{ $click->url }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No clicks yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($clicks->hasPages())
    <div class="mt-4">
        {{ $clicks->links() }}
    </div>
@endif
@endsection
