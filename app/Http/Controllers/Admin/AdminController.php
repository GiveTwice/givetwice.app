<?php

namespace App\Http\Controllers\Admin;

use App\Actions\FetchGiftDetailsAction;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Health\ResultStores\ResultStore;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_gifts' => Gift::count(),
            'total_lists' => GiftList::count(),
            'total_claims' => Claim::whereNotNull('confirmed_at')->count(),
            'gifts_today' => Gift::whereDate('created_at', today())->count(),
            'gifts_this_week' => Gift::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'gifts_this_month' => Gift::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'pending_claims' => Claim::whereNull('confirmed_at')->count(),
            'pending_fetches' => Gift::whereIn('fetch_status', ['pending', 'fetching'])->count(),
            'failed_fetches' => Gift::where('fetch_status', 'failed')->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentGifts = Gift::with('user')->latest()->take(5)->get();
        $recentClaims = Claim::with(['gift', 'user'])->whereNotNull('confirmed_at')->latest()->take(5)->get();

        $chartData = $this->buildChartData();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentGifts', 'recentClaims', 'chartData'));
    }

    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter')) {
            match ($request->input('filter')) {
                'admin' => $query->where('is_admin', true),
                'verified' => $query->whereNotNull('email_verified_at'),
                'unverified' => $query->whereNull('email_verified_at'),
                default => null,
            };
        }

        $users = $query->withCount(['gifts', 'lists', 'claims'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function showUser(User $user): View
    {
        $user->load(['gifts' => function ($q) {
            $q->latest()->take(10);
        }, 'lists' => function ($q) {
            $q->latest();
        }]);

        $user->loadCount(['gifts', 'lists', 'claims']);

        return view('admin.user-show', compact('user'));
    }

    public function toggleAdminStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own admin status.');
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('success', $user->is_admin ? 'User granted admin access.' : 'Admin access revoked.');
    }

    public function impersonateAs(User $user, Request $request): RedirectResponse
    {
        if ($user->is_admin || $user->id === auth()->id()) {
            return back()->with('error', 'Cannot impersonate this user.');
        }

        auth()->user()->impersonate($user);

        $locale = $user->locale_preference ?? 'en';
        $redirectTo = $request->input('redirect_to', "/{$locale}/dashboard");

        if (! str_starts_with($redirectTo, '/') || str_starts_with($redirectTo, '//')) {
            $redirectTo = "/{$locale}/dashboard";
        }

        return redirect($redirectTo);
    }

    public function gifts(Request $request): View
    {
        $query = Gift::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('fetch_status', $request->input('status'));
        }

        $gifts = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.gifts', compact('gifts'));
    }

    public function showGift(Gift $gift): View
    {
        $gift->load(['user', 'claims.user', 'lists']);

        return view('admin.gift-show', compact('gift'));
    }

    public function lists(Request $request): View
    {
        $query = GiftList::with('creator')->withCount(['gifts', 'users']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $lists = $query->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.lists', compact('lists'));
    }

    public function refreshGift(Gift $gift): RedirectResponse
    {
        $gift->update([
            'fetch_status' => 'pending',
            'fetch_error' => null,
            'fetch_attempts' => 0,
        ]);

        FetchGiftDetailsAction::dispatch($gift);

        return back()->with('success', 'Gift fetch has been re-queued.');
    }

    public function health(): View
    {
        $checkResults = app(ResultStore::class)->latestResults();

        return view('admin.health', compact('checkResults'));
    }

    /**
     * @return array<string, list<int|string>>
     */
    private function buildChartData(): array
    {
        $startDate = now()->subDays(59)->startOfDay();

        $period = CarbonPeriod::create($startDate, now()->endOfDay());
        $labels = [];
        $emptyData = [];

        foreach ($period as $date) {
            $labels[] = $date->format('M j');
            $emptyData[$date->format('Y-m-d')] = 0;
        }

        return [
            'labels' => $labels,
            'signups' => array_values(array_merge($emptyData, $this->dailyCounts(User::query(), $startDate))),
            'gifts' => array_values(array_merge($emptyData, $this->dailyCounts(Gift::query(), $startDate))),
            'claims' => array_values(array_merge($emptyData, $this->dailyCounts(
                Claim::query()->whereNotNull('confirmed_at'),
                $startDate,
            ))),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function dailyCounts(Builder $query, Carbon $since): array
    {
        return $query
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'date')
            ->toArray();
    }
}
