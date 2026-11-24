<?php

namespace App\Helpers;

use App\Models\GiftExchange;
use App\Models\GiftList;
use App\Models\User;
use App\Support\ListColour;
use Illuminate\Support\Collection;

class AppShellHelper
{
    public static function lists(User $user, ?GiftList $activeList = null): array
    {
        $base = self::base($user, $activeList);

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
                ['label' => __('Following'), 'value' => $followedListCount],
                ['label' => __('Groups'), 'value' => $base['exchangeCount']],
            ],
        ];
    }

    private static function base(User $user, ?GiftList $activeList = null): array
    {
        $currentLocale = app()->getLocale();
        $dashboardUrl = route('dashboard.locale', ['locale' => $currentLocale]);
        $secretSantaUrl = route('dashboard.secret-santa', ['locale' => $currentLocale]);
        $exchangeSlug = ExchangeHelper::exchangeTypeForLocale($currentLocale);
        $lists = $user->lists()->withCount('gifts')->orderBy('lists.created_at')->orderBy('lists.id')->get();
        $exchangeQuery = GiftExchange::query()->where('organizer_id', $user->id);
        $exchangeCount = (clone $exchangeQuery)->count();
        $drawnCount = (clone $exchangeQuery)->where('status', 'drawn')->count();

        return [
            'currentLocale' => $currentLocale,
            'dashboardUrl' => $dashboardUrl,
            'secretSantaUrl' => $secretSantaUrl,
            'exchangeSlug' => $exchangeSlug,
            'listCount' => $lists->count(),
            'listPositions' => $lists->pluck('id')->flip()->all(),
            'listLinks' => self::listLinks($lists, $activeList, $currentLocale),
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
                'children' => $base['listLinks'],
            ],
            [
                'label' => __('Secret Santa'),
                'meta' => __('Secret Santa groups, draws, and reveals'),
                'href' => $base['secretSantaUrl'],
                'emoji' => '🎲',
                'active' => $activeSection === 'secret-santa',
            ],
            [
                'label' => __('Followed lists'),
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

    /**
     * Builds the list links shown under "Lists" in the sidebar, at most five of them.
     * The open list always gets a place, so it can be marked active even when it is the sixth or later.
     *
     * @param  Collection<int, GiftList>  $lists
     * @return array{links: array<int, array{label: string, href: string, active: bool, dot: string}>, moreHref: string|null, createHref: string}
     */
    private static function listLinks(Collection $lists, ?GiftList $activeList, string $locale): array
    {
        $shown = $lists->take(5);
        $openList = $activeList ? $lists->firstWhere('id', $activeList->id) : null;
        if ($openList) {
            $shown = $shown->contains('id', $openList->id) ? $shown : $shown->take(4)->push($openList);
        }

        return [
            'links' => $shown->map(fn (GiftList $list) => [
                'label' => $list->name,
                'href' => route('list.show', ['locale' => $locale, 'list' => $list]),
                'active' => $activeList?->id === $list->id,
                'dot' => ListColour::forPosition($lists->search(fn (GiftList $candidate) => $candidate->id === $list->id))['dot'],
            ])->values()->all(),
            'moreHref' => $lists->count() > 5 ? route('dashboard.locale', ['locale' => $locale]) : null,
            'createHref' => route('lists.create', ['locale' => $locale]),
        ];
    }
}
