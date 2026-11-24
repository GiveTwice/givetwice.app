<?php

namespace App\Actions;

use App\Models\GiftList;
use Illuminate\Validation\ValidationException;

class ReorderGiftsAction
{
    /**
     * Puts the given gifts in the given order, inside the places they already take in the list.
     *
     * The owner can only drag the gifts on one page of the list. Gifts that are not in
     * $giftIds keep their place, so reordering one page never moves gifts on another page.
     *
     * @param  array<int, int>  $giftIds
     */
    public function execute(GiftList $list, array $giftIds): void
    {
        $list->getConnection()->transaction(function () use ($list, $giftIds): void {
            GiftList::query()->lockForUpdate()->findOrFail($list->id);
            $listOrder = $list->gifts()->orderBy('gifts.id')->pluck('gifts.id');
            $places = $listOrder->intersect($giftIds)->keys();

            if ($places->count() !== count($giftIds)) {
                throw ValidationException::withMessages(['gifts' => __('This list changed. Refresh and try again.')]);
            }

            $places->each(fn (int $place, int $index) => $listOrder->put($place, $giftIds[$index]));
            $listOrder->each(fn (int $giftId, int $order) => $list->gifts()->updateExistingPivot($giftId, ['sort_order' => $order]));
        });
    }
}
