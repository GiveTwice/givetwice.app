<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExportPersonalDataAction
{
    public function execute(User $user): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'account' => $this->accountData($user),
            'social_accounts' => $this->socialAccounts($user),
            'wishlists' => $this->wishlists($user),
            'gifts' => $this->gifts($user),
            'claims_made' => $this->claimsMade($user),
            'claims_on_your_gifts' => $this->claimsOnUserGifts($user),
            'collaborations' => $this->collaborations($user),
            'followed_lists' => $this->followedLists($user),
            'sessions' => $this->sessions($user),
        ];
    }

    protected function accountData(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'locale_preference' => $user->locale_preference,
            'email_verified' => $user->email_verified_at !== null,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'two_factor_enabled' => $user->two_factor_secret !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    protected function socialAccounts(User $user): array
    {
        return [
            'google_connected' => $user->google_id !== null,
            'facebook_connected' => $user->facebook_id !== null,
        ];
    }

    protected function wishlists(User $user): array
    {
        return $user->lists()->get()->map(fn ($list) => [
            'name' => $list->name,
            'description' => $list->description,
            'is_default' => (bool) $list->is_default,
            'public_url' => $list->getPublicUrl(),
            'created_at' => $list->created_at?->toIso8601String(),
        ])->all();
    }

    protected function gifts(User $user): array
    {
        return $user->gifts()->withTrashed()->get()->map(fn ($gift) => [
            'title' => $gift->title,
            'description' => $gift->description,
            'url' => $gift->url,
            'price' => $gift->getPriceAsDecimal(),
            'currency' => $gift->currency,
            'created_at' => $gift->created_at?->toIso8601String(),
        ])->all();
    }

    protected function claimsMade(User $user): array
    {
        return $user->claims()->with('gift')->get()->map(fn ($claim) => [
            'gift_title' => $claim->gift?->title,
            'confirmed_at' => $claim->confirmed_at?->toIso8601String(),
            'notes' => $claim->notes,
            'created_at' => $claim->created_at?->toIso8601String(),
        ])->all();
    }

    protected function claimsOnUserGifts(User $user): array
    {
        $giftIds = $user->gifts()->pluck('id');

        return \App\Models\Claim::whereIn('gift_id', $giftIds)
            ->with('gift')
            ->get()
            ->map(fn ($claim) => [
                'gift_title' => $claim->gift?->title,
                'claimer_name' => $claim->claimer_name,
                'claimer_email' => $claim->claimer_email,
                'confirmed_at' => $claim->confirmed_at?->toIso8601String(),
                'created_at' => $claim->created_at?->toIso8601String(),
            ])->all();
    }

    protected function collaborations(User $user): array
    {
        return $user->lists()
            ->where('creator_id', '!=', $user->id)
            ->get()
            ->map(fn ($list) => [
                'list_name' => $list->name,
                'joined_at' => $list->pivot->joined_at,
            ])->all();
    }

    protected function followedLists(User $user): array
    {
        return $user->followedLists()->with('list')->get()->map(fn ($follow) => [
            'list_name' => $follow->list?->name,
            'notifications' => (bool) $follow->notifications,
            'created_at' => $follow->created_at?->toIso8601String(),
        ])->all();
    }

    protected function sessions(User $user): array
    {
        return DB::table('sessions')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn ($session) => [
                'ip_address' => $session->ip_address,
                'last_activity' => Carbon::createFromTimestamp($session->last_activity)->toIso8601String(),
            ])->all();
    }
}
