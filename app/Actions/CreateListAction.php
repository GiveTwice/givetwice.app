<?php

namespace App\Actions;

use App\Models\GiftList;
use App\Models\User;
use Spatie\SlackAlerts\Facades\SlackAlert;

class CreateListAction
{
    public function execute(User $creator, string $name, ?string $description = null, bool $isDefault = false): GiftList
    {
        $list = GiftList::create([
            'creator_id' => $creator->id,
            'name' => $name,
            'description' => $description,
            'is_default' => $isDefault,
        ]);

        $list->users()->attach($creator->id, ['joined_at' => now()]);

        if (! $isDefault) {
            SlackAlert::message("📋 {$creator->email} created a new list: \"{$name}\"");
        }

        return $list;
    }
}
