<?php

namespace App\Actions;

use App\Models\Gift;
use App\Models\GiftList;

class MoveGiftAction
{
    public function execute(GiftList $list, Gift $gift, string $direction): void
    {
        $list->getConnection()->transaction(function () use ($direction, $list, $gift): void {
            GiftList::query()->lockForUpdate()->findOrFail($list->id);
            $ids = $list->gifts()->orderBy('gifts.id')->pluck('gifts.id');
            $position = $ids->search($gift->id, true);
            abort_if($position === false, 404);
            $target = $position + ($direction === 'up' ? -1 : 1);
            if (! $ids->has($target)) {
                return;
            }
            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];
            foreach ($ids as $order => $id) {
                $list->gifts()->updateExistingPivot($id, ['sort_order' => $order]);
            }
        });
    }
}
