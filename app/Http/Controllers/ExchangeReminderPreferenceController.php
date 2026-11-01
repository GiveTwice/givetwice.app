<?php

namespace App\Http\Controllers;

use App\Models\GiftExchangeParticipant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExchangeReminderPreferenceController extends Controller
{
    public function __invoke(Request $request, string $locale, GiftExchangeParticipant $participant): View
    {
        if ($request->isMethod('post')) {
            $participant->update(['reminders_muted_at' => now()]);
        }

        return view('exchanges.reminder-preferences', compact('participant'));
    }
}
