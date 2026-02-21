<?php

namespace App\Actions;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\SlackAlerts\Facades\SlackAlert;

class DeleteAccountAction
{
    public function execute(User $user, ?string $auditDetails = null, ?string $auditPerformedBy = null): void
    {
        $email = $user->email;
        $userId = $user->id;

        $gifts = $user->gifts()->withTrashed()->get();

        DB::transaction(function () use ($user, $userId, $email, $auditDetails, $auditPerformedBy) {
            $logger = activity()
                ->performedOn($user)
                ->useLog('gdpr')
                ->event('account_deletion')
                ->withProperties(array_filter([
                    'user_email' => $email,
                    'details' => $auditDetails,
                    'performed_by' => $auditPerformedBy,
                ], fn ($value) => $value !== null));

            if ($auditPerformedBy !== 'system') {
                $logger->causedBy($user);
            }

            $logger->log('Account deleted');

            $user->delete();
            DB::table('sessions')->where('user_id', $userId)->delete();
        });

        foreach ($gifts as $gift) {
            /** @var Gift $gift */
            $gift->clearMediaCollection('image');
        }

        $message = $auditPerformedBy === 'system'
            ? "🗑️ {$email} account deleted (inactive 24+ months)"
            : "👋 {$email} deleted their account";

        SlackAlert::message($message);
    }
}
