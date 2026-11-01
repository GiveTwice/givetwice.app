<?php

namespace App\Jobs;

use App\Mail\ReservationAlertMail;
use App\Models\GiftList;
use App\Models\ReservationAlert;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendReservationAlert implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $alertId) {}

    public function handle(): void
    {
        $seed = ReservationAlert::find($this->alertId);
        if (! $seed) {
            return;
        }
        Cache::lock('reservation-bundle:'.hash('sha256', $seed->email).':'.$seed->list_id, 120)->block(5, function () use ($seed): void {
            $seed->getConnection()->transaction(function () use ($seed): void {
                $alerts = ReservationAlert::with('claim.user')->where('email', $seed->email)->where('list_id', $seed->list_id)
                    ->whereNull('sent_at')->whereNull('resolved_at')->orderBy('id')->lockForUpdate()->get();
                if ($alerts->isEmpty()) {
                    return;
                }
                $dueAt = $alerts->max('updated_at')->copy()->addMinutes(10);
                if ($dueAt->isFuture()) {
                    self::dispatch($seed->id)->delay($dueAt)->afterCommit();

                    return;
                }
                $user = User::where('email', $seed->email)->first();
                $list = GiftList::find($seed->list_id);
                if (($user && $list && ($list->creator_id === $user->id || $list->hasUser($user)))
                    || ReservationAlert::where('email', $seed->email)->where('list_id', $seed->list_id)->whereNotNull('muted_at')->exists()
                    || $user?->followedLists()->where('list_id', $seed->list_id)->where('notifications', false)->exists()) {
                    $alerts->each->update(['resolved_at' => now()]);

                    return;
                }
                $valid = $alerts->filter(function (ReservationAlert $alert): bool {
                    $claim = $alert->claim;
                    if (! $claim || $alert->email !== ($claim->claimer_email ?? $claim->user?->email)) {
                        $alert->update(['resolved_at' => now()]);

                        return false;
                    }
                    if ($alert->before === array_diff_key($alert->after, ['deleted' => true]) && ! $alert->after['deleted']) {
                        $alert->update(['resolved_at' => now()]);

                        return false;
                    }

                    return true;
                });
                if ($valid->isEmpty()) {
                    return;
                }
                Mail::to($seed->email)->send((new ReservationAlertMail($valid->first(), $valid))->locale($seed->locale));
                $valid->each->update(['sent_at' => now()]);
            });
        });
    }
}
