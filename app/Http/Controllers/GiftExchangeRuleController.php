<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftExchangeRuleRequest;
use App\Models\GiftExchange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GiftExchangeRuleController extends Controller
{
    public function __invoke(GiftExchangeRuleRequest $request, string $locale, GiftExchange $exchange): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($exchange, $data, $request) {
            $pairs = [[$data['giver_id'], $data['receiver_id']]];
            if ($request->boolean('mutual')) {
                $pairs[] = [$data['receiver_id'], $data['giver_id']];
            }
            foreach ($pairs as [$giver, $receiver]) {
                $attributes = ['giver_id' => $giver, 'receiver_id' => $receiver];
                if ($request->boolean('remove')) {
                    $exchange->exclusions()->where($attributes)->delete();
                } else {
                    $exchange->exclusions()->firstOrCreate($attributes);
                }
            }
        });

        return back();
    }
}
