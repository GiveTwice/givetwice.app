<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Gift;
use App\Models\GiftList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentGifts = Gift::with('user')->latest()->take(5)->get();
        $recentClaims = Claim::with(['gift', 'user'])->whereNotNull('confirmed_at')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentGifts', 'recentClaims'));
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

    public function toggleUserStatus(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $user->update(['is_admin' => ! $user->is_admin]);

        return back()->with('success', $user->is_admin ? 'User granted admin access.' : 'Admin access revoked.');
    }
}
