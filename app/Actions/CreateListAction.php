<?php

namespace App\Actions;

use App\Models\GiftList;
use App\Models\User;

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

        return $list;
    }
}
