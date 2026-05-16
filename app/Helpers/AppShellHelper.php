<?php

namespace App\Helpers;

use App\Models\GiftExchange;
use App\Models\User;

class AppShellHelper
{
    public static function lists(User $user): array
    {
        $base = self::base($user);

        return [
            ...$base,
            'sidebarItems' => self::sidebarItems($base, 'lists'),
            'sidebarStats' => [
                ['label' => __('Lists'), 'value' => $base['listCount']],
                ['label' => __('Gifts'), 'value' => $base['giftCount']],
                ['label' => __('Groups'), 'value' => $base['exchangeCount']],
            ],
        ];
    }

    public static function secretSanta(User $user): array
    {
        $base = self::base($user);

        return [
            ...$base,
            'sidebarItems' => self::sidebarItems($base, 'secret-santa'),
            'sidebarStats' => [
                ['label' => __('Lists'), 'value' => $base['listCount']],
                ['label' => __('Draft'), 'value' => $base['draftCount']],
                ['label' => __('Drawn'), 'value' => $base['drawnCount']],
            ],
        ];
    }

    public static function friends(User $user, ?int $followedListCount = null): array
    {
        $base = self::base($user);
        $followedListCount ??= $user->followedLists()->count();

        return [
            ...$base,
            'sidebarItems' => self::sidebarItems($base, 'friends', $followedListCount),
            'sidebarStats' => [
                ['label' => __('Lists'), 'value' => $base['listCount']],
                ['label' => __('Following'), 'value' => $followedListCount],
                ['label' => __('Groups'), 'value' => $base['exchangeCount']],
            ],
        ];
    }

    public static function settings(User $user): array
    {
        $base = self::base($user);
        $followedListCount = $user->followedLists()->count();

        return [
            ...$base,
            'sidebarItems' => self::sidebarItems($base, 'settings'),
            'sidebarStats' => [
                ['label' => __('Lists'), 'value' => $base['listCount']],
                ['label' => __('Friends'), 'value' => $followedListCount],
                ['label' => __('Groups'), 'value' => $base['exchangeCount']],
            ],
        ];
    }

    private static function base(User $user): array
    {
        $currentLocale = app()->getLocale();
        $dashboardUrl = route('dashboard.locale', ['locale' => $currentLocale]);
        $secretSantaUrl = route('dashboard.secret-santa', ['locale' => $currentLocale]);
        $exchangeSlug = GiftExchange::exchangeTypeSlugs()[$currentLocale] ?? 'secret-santa';
        $lists = $user->lists()->withCount('gifts')->get();
        $exchangeQuery = GiftExchange::query()->where('organizer_id', $user->id);
        $exchangeCount = (clone $exchangeQuery)->count();
        $drawnCount = (clone $exchangeQuery)->where('status', 'drawn')->count();

        return [
            'currentLocale' => $currentLocale,
            'dashboardUrl' => $dashboardUrl,
            'secretSantaUrl' => $secretSantaUrl,
            'exchangeSlug' => $exchangeSlug,
            'listCount' => $lists->count(),
            'giftCount' => $lists->sum('gifts_count'),
            'exchangeCount' => $exchangeCount,
            'drawnCount' => $drawnCount,
            'draftCount' => $exchangeCount - $drawnCount,
        ];
    }

    private static function sidebarItems(array $base, string $activeSection, int $followedListCount = 0): array
    {
        return [
            [
                'label' => __('Lists'),
                'meta' => trans_choice(':count wishlist|:count wishlists', $base['listCount'], ['count' => $base['listCount']]),
                'href' => $base['dashboardUrl'],
                'emoji' => '🎁',
                'active' => $activeSection === 'lists',
            ],
            [
                'label' => __('Secret Santa'),
                'meta' => __('Secret Santa groups, draws, and reveals'),
                'href' => $base['secretSantaUrl'],
                'emoji' => '🎲',
                'active' => $activeSection === 'secret-santa',
            ],
            [
                'label' => __('Friends'),
                'meta' => $activeSection === 'friends'
                    ? trans_choice(':count followed wishlist|:count followed wishlists', $followedListCount, ['count' => $followedListCount])
                    : __('Wishlists you follow'),
                'href' => route('friends.index', ['locale' => $base['currentLocale']]),
                'icon' => 'users',
                'active' => $activeSection === 'friends',
            ],
            [
                'label' => __('Settings'),
                'meta' => $activeSection === 'settings'
                    ? __('Profile, security, and exports')
                    : __('Profile, sessions, and account'),
                'href' => route('settings', ['locale' => $base['currentLocale']]),
                'icon' => 'settings',
                'active' => $activeSection === 'settings',
            ],
        ];
    }
}
