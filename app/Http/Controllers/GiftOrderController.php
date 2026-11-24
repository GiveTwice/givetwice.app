<?php

namespace App\Http\Controllers;

use App\Actions\ReorderGiftsAction;
use App\Http\Requests\ReorderGiftsRequest;
use App\Models\GiftList;
use Illuminate\Http\Response;

class GiftOrderController extends Controller
{
    public function __invoke(ReorderGiftsRequest $request, string $locale, GiftList $list): Response
    {
        $this->authorize('update', $list);
        app(ReorderGiftsAction::class)->execute($list, array_map(intval(...), array_values($request->validated('gifts'))));

        return response()->noContent();
    }
}
