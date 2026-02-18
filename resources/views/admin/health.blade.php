@extends('admin.layout')

@section('title', 'System Health')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">System health</h1>
    <a href="{{ route('admin.health') }}" class="btn-primary-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" />
        </svg>
        Refresh
    </a>
</div>

@if($checkResults && $checkResults->storedCheckResults->count())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($checkResults->storedCheckResults as $result)
            <div class="card">
                <div class="flex items-center gap-3 mb-3">
                    @if($result->status === 'ok')
                        <div class="w-3 h-3 rounded-full bg-teal-500 shrink-0"></div>
                    @elseif($result->status === 'warning')
                        <div class="w-3 h-3 rounded-full bg-sunny-500 shrink-0"></div>
                    @elseif($result->status === 'failed' || $result->status === 'crashed')
                        <div class="w-3 h-3 rounded-full bg-red-500 shrink-0"></div>
                    @else
                        <div class="w-3 h-3 rounded-full bg-gray-300 shrink-0"></div>
                    @endif

                    <h3 class="font-semibold text-gray-900">{{ $result->name }}</h3>
                </div>

                <div class="mb-2">
                    @if($result->status === 'ok')
                        <span class="badge badge-success text-xs">OK</span>
                    @elseif($result->status === 'warning')
                        <span class="badge badge-warning text-xs">Warning</span>
                    @elseif($result->status === 'failed' || $result->status === 'crashed')
                        <span class="badge badge-danger text-xs">{{ ucfirst($result->status) }}</span>
                    @else
                        <span class="badge badge-info text-xs">{{ ucfirst($result->status) }}</span>
                    @endif
                </div>

                @if($result->shortSummary)
                    <p class="text-sm text-gray-600">{{ $result->shortSummary }}</p>
                @endif

                @if($result->notificationMessage)
                    <p class="text-xs text-gray-500 mt-1">{{ $result->notificationMessage }}</p>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="card text-center py-12">
        <p class="text-gray-500">No health check results available. Run <code class="bg-cream-100 px-2 py-0.5 rounded text-sm">php artisan health:check</code> to generate results.</p>
    </div>
@endif
@endsection
