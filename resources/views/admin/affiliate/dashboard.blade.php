@extends('admin.layout')

@section('title', 'Affiliate Dashboard')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Affiliate Dashboard</h1>
    <div class="flex items-center gap-2">
        @if($stats['skimlinks_enabled'])
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-teal-100 text-teal-700 text-sm font-medium rounded-full">
                <span class="w-2 h-2 bg-teal-500 rounded-full"></span>
                Skimlinks active
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-500 text-sm font-medium rounded-full">
                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                Passthrough mode
            </span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <a href="{{ route('admin.affiliate.clicks') }}" class="card card-link flex items-center gap-4">
        <div class="icon-circle-lg bg-coral-100 text-coral-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total clicks</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_clicks']) }}</p>
        </div>
    </a>

    <div class="card flex items-center gap-4">
        <div class="icon-circle-lg bg-teal-100 text-teal-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Clicks today</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['clicks_today']) }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4">
        <div class="icon-circle-lg bg-sunny-100 text-sunny-700">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">This week</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['clicks_this_week']) }}</p>
        </div>
    </div>

    <div class="card flex items-center gap-4">
        <div class="icon-circle-lg bg-coral-100 text-coral-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Unique gifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['unique_gifts']) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 card" x-data="affiliateChart()" x-init="initChart()">
        <h3 class="text-sm font-medium text-gray-500 mb-3">Clicks (30 days)</h3>
        <div class="relative h-48"><canvas x-ref="canvas"></canvas></div>
    </div>

    <div class="card !p-0">
        <div class="p-5 border-b border-cream-200">
            <h3 class="font-semibold text-gray-900">Top retailers</h3>
        </div>
        <div class="p-5">
            @forelse($topRetailers as $retailer)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-cream-100' : '' }}">
                    <a href="{{ route('admin.affiliate.clicks', ['retailer' => $retailer->retailer_domain]) }}" class="text-sm font-medium text-gray-900 hover:text-coral-600 transition-colors">
                        {{ $retailer->retailer_domain }}
                    </a>
                    <span class="text-sm text-gray-500">{{ number_format($retailer->click_count) }}</span>
                </div>
            @empty
                <p class="text-gray-500 text-sm">No clicks yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('head-scripts')
    @vite('resources/js/admin-charts.js')
@endpush

@push('scripts')
    <script id="affiliate-chart-data" type="application/json">@json($chartData)</script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('affiliateChart', () => ({
                initChart() {
                    const data = JSON.parse(document.getElementById('affiliate-chart-data').textContent);
                    const ctx = this.$refs.canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Clicks',
                                data: data.clicks,
                                borderColor: '#f97066',
                                backgroundColor: 'rgba(249, 112, 102, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                pointRadius: 0,
                                pointHitRadius: 10,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { display: false },
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                }
            }));
        });
    </script>
@endpush
