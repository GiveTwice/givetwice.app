<?php

namespace App\Actions;

use App\Models\GiftExchange;
use App\Models\GiftExchangeParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateGiftExchangeAction
{
    public function execute(
        User $organizer,
        string $name,
        string $eventDate,
        string $locale,
        array $participants,
        ?int $budgetAmount = null,
        string $budgetCurrency = 'EUR',
        bool $organizerParticipates = false,
    ): GiftExchange {
        $allParticipants = collect($participants)->map(fn ($p) => [
            'name' => trim($p['name']),
            'email' => strtolower(trim($p['email'])),
        ]);

        if ($organizerParticipates) {
            $allParticipants->push([
                'name' => $organizer->name,
                'email' => strtolower($organizer->email),
            ]);
        }

        $allParticipants = $allParticipants->unique('email')->values();

        if ($allParticipants->count() < 3) {
            throw new InvalidArgumentException(__('At least 3 participants are required.'));
        }

        return DB::transaction(function () use ($organizer, $name, $eventDate, $locale, $allParticipants, $budgetAmount, $budgetCurrency) {
            $exchange = GiftExchange::create([
                'organizer_id' => $organizer->id,
                'name' => $name,
                'event_date' => $eventDate,
                'locale' => $locale,
                'budget_amount' => $budgetAmount,
                'budget_currency' => $budgetCurrency,
                'status' => 'draft',
            ]);

            foreach ($allParticipants as $participantData) {
                $existingUser = User::where('email', $participantData['email'])->first();

                GiftExchangeParticipant::create([
                    'exchange_id' => $exchange->id,
                    'user_id' => $existingUser?->id,
                    'name' => $participantData['name'],
                    'email' => $participantData['email'],
                ]);
            }

            return $exchange->fresh('participants');
        });
    }
}
