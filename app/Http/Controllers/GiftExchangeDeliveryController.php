<?php

namespace App\Http\Controllers;

use App\Http\Requests\GiftExchangeDeliveryRequest;
use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class GiftExchangeDeliveryController extends Controller
{
    public function __invoke(GiftExchangeDeliveryRequest $request, string $locale, GiftExchange $exchange, GiftExchangeParticipant $participant): RedirectResponse
    {
        abort_unless($participant->exchange_id === $exchange->id && $exchange->isDrawn(), 404);
        $action = $request->validated('action');
        if ($action === 'regenerate') {
            $participant->update(['token' => Str::random(64), 'token_generated_at' => now(), 'viewed_at' => null, 'sent_at' => null, 'whatsapp_opened_at' => null]);
        } elseif ($action === 'sent') {
            $participant->update(['sent_at' => now()]);
        } else {
            $participant->update(['whatsapp_opened_at' => now()]);
            $message = __('Hi :name! Your private draw for :group:', ['name' => $participant->name, 'group' => $exchange->name]);

            return redirect()->away('https://wa.me/?text='.rawurlencode($message.' '.$participant->revealUrl()));
        }

        return back();
    }
}
