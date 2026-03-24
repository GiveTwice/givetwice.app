<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateClick;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliateAdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_clicks' => AffiliateClick::count(),
            'clicks_today' => AffiliateClick::whereDate('clicked_at', today())->count(),
            'clicks_this_week' => AffiliateClick::whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'clicks_this_month' => AffiliateClick::whereMonth('clicked_at', now()->month)->whereYear('clicked_at', now()->year)->count(),
            'unique_gifts' => AffiliateClick::distinct('gift_id')->count('gift_id'),
            'skimlinks_enabled' => config('services.skimlinks.enabled'),
        ];

        $topRetailers = AffiliateClick::selectRaw('retailer_domain, COUNT(*) as click_count')
            ->groupBy('retailer_domain')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        $chartData = $this->buildClickChartData();

        return view('admin.affiliate.dashboard', compact('stats', 'topRetailers', 'chartData'));
    }

    public function clicks(Request $request): View
    {
        $query = AffiliateClick::with('gift');

        if ($request->filled('retailer')) {
            $query->where('retailer_domain', $request->input('retailer'));
        }

        if ($request->filled('date_from')) {
            $query->where('clicked_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('clicked_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $clicks = $query->latest('clicked_at')
            ->paginate(30)
            ->withQueryString();

        $retailers = AffiliateClick::distinct()->pluck('retailer_domain')->sort()->values();

        return view('admin.affiliate.clicks', compact('clicks', 'retailers'));
    }

    public function commissions(): View
    {
        return view('admin.affiliate.commissions');
    }

    /**
     * @return array<string, list<int|string>>
     */
    private function buildClickChartData(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $period = CarbonPeriod::create($startDate, now()->endOfDay());

        $labels = [];
        $emptyData = [];

        foreach ($period as $date) {
            $labels[] = $date->format('M j');
            $emptyData[$date->format('Y-m-d')] = 0;
        }

        $clickCounts = AffiliateClick::where('clicked_at', '>=', $startDate)
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(clicked_at)')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'labels' => $labels,
            'clicks' => array_values(array_merge($emptyData, $clickCounts)),
        ];
    }
}
