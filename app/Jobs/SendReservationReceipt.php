<?php

namespace App\Jobs;

use App\Mail\ReservationMail;
use App\Models\Claim;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendReservationReceipt implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $claimId) {}

    public static function request(Claim $claim, string $locale): void
    {
        Claim::query()->whereKey($claim->id)->update([
            'receipt_requested_at' => now(), 'receipt_sent_at' => null, 'receipt_locale' => $locale,
        ]);
        self::dispatch($claim->id)->delay(now()->addMinutes(10))->afterCommit();
    }

    public function handle(): void
    {
        $seed = Claim::with('user')->find($this->claimId);
        if (! $seed) {
            return;
        }
        $email = $seed->claimer_email ?? $seed->user?->email;
        if (! $email) {
            return;
        }
        $listId = $seed->reservation['list_id'] ?? null;
        $bundle = $listId ?? 'gift:'.$seed->gift_id;
        Cache::lock('reservation-receipt:'.hash('sha256', $email).':'.$bundle, 120)->block(5, function () use ($seed, $email, $listId): void {
            $seed->getConnection()->transaction(function () use ($seed, $email, $listId): void {
                $query = Claim::with('user', 'gift.media', 'gift.lists')->where('claimer_email', $email)
                    ->whereNotNull('confirmed_at')->whereNotNull('receipt_requested_at')->whereNull('receipt_sent_at');
                if ($listId === null) {
                    $query->whereNull('reservation->list_id')->where('gift_id', $seed->gift_id);
                } else {
                    $query->where('reservation->list_id', $listId);
                }
                $claims = $query->orderBy('id')->lockForUpdate()->get();
                if ($claims->isEmpty()) {
                    return;
                }
                $dueAt = $claims->max('receipt_requested_at')->copy()->addMinutes(10);
                if ($dueAt->isFuture()) {
                    self::dispatch($seed->id)->delay($dueAt)->afterCommit();

                    return;
                }
                $valid = $claims->reject->isRemoved()->values();
                if ($valid->isNotEmpty()) {
                    $locale = $valid->last()->receipt_locale ?? 'en';
                    Mail::to($email)->send(new ReservationMail($valid->first(), $locale, $valid));
                }
                $claims->each->update(['receipt_sent_at' => now()]);
            });
        });
    }
}
