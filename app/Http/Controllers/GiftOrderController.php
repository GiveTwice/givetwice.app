<?php

namespace App\Http\Controllers;

use App\Actions\MoveGiftAction;
use App\Http\Requests\MoveGiftRequest;
use App\Models\Gift;
use App\Models\GiftList;
use Illuminate\Http\RedirectResponse;

class GiftOrderController extends Controller
{
    public function __invoke(MoveGiftRequest $request, string $locale, GiftList $list, Gift $gift): RedirectResponse
    {
        $this->authorize('update', $list);
        app(MoveGiftAction::class)->execute($list, $gift, $request->validated('direction'));

        return back()->with('success', __('Gift order saved.'));
    }
}
