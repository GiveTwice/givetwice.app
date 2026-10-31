<?php

namespace App\Jobs;

use App\Mail\ReservationAlertMail;
use App\Models\ReservationAlert;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendReservationAlert implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $alertId) {}

    public function handle(): void
    {
        (new ReservationAlert)->getConnection()->transaction(function () {
            $alert = ReservationAlert::with('claim')->lockForUpdate()->find($this->alertId);
            if (! $alert || $alert->sent_at || $alert->resolved_at || ! $alert->claim || $alert->email !== ($alert->claim->claimer_email ?? $alert->claim->user?->email)) {
                return;
            }
            $user = User::where('email', $alert->email)->whereNotNull('email_verified_at')->first();
            if (ReservationAlert::where('email', $alert->email)->where('list_id', $alert->list_id)->whereNotNull('muted_at')->exists()
                || $user?->followedLists()->where('list_id', $alert->list_id)->where('notifications', false)->exists()) {
                return;
            }
            if ($alert->before === array_diff_key($alert->after, ['deleted' => true]) && ! $alert->after['deleted']) {
                $alert->update(['resolved_at' => now()]);

                return;
            }
            Mail::to($alert->email)->send((new ReservationAlertMail($alert))->locale($alert->locale));
            $alert->update(['sent_at' => now()]);
        });
    }
}
