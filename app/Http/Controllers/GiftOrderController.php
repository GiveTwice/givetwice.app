<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveGiftRequest;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;

class GiftOrderController extends Controller
{
    public function __invoke(MoveGiftRequest $request, string $locale, GiftList $list, Gift $gift): RedirectResponse
    {
        $this->authorize('update', $list);
        $list->getConnection()->transaction(function () use ($request, $list, $gift): void {
            GiftList::query()->lockForUpdate()->findOrFail($list->id);
            $ids = $list->gifts()->orderBy('gifts.id')->pluck('gifts.id')->all();
            $position = array_search($gift->id, $ids, true);
            abort_if($position === false, 404);
            $target = $position + ($request->validated('direction') === 'up' ? -1 : 1);
            if (! array_key_exists($target, $ids)) {
                return;
            }
            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];
            foreach ($ids as $order => $id) {
                $list->gifts()->updateExistingPivot($id, ['sort_order' => $order]);
            }
        });

        return back()->with('success', __('Gift order saved.'));
    }
}
