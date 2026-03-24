@extends('admin.layout')

@section('title', 'Affiliate Commissions')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Commissions</h1>
    <a href="{{ route('admin.affiliate.dashboard') }}" class="text-coral-600 hover:text-coral-700 text-sm font-medium">&larr; Back to dashboard</a>
</div>

<div class="card">
    <div class="text-center py-12">
        <div class="icon-circle-lg bg-cream-100 text-cream-400 mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Waiting for Skimlinks approval</h3>
        <p class="text-gray-500 max-w-md mx-auto">
            Commission tracking will activate once the Skimlinks publisher account is approved.
            Click data is already being collected.
        </p>
    </div>
</div>
@endsection
