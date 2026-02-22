<?php

namespace App\Actions;

use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\SlackAlerts\Facades\SlackAlert;
use Throwable;

class DeleteAccountAction
{
    public function execute(User $user, ?string $auditDetails = null, bool $isSystemAction = false): void
    {
        $email = $user->email;
        $userId = $user->id;

        $gifts = $user->gifts()->withTrashed()->get();

        $properties = [
            'user_email' => $email,
            'user_id' => $userId,
            'performed_by' => $isSystemAction ? 'system' : 'user',
        ];

        if ($auditDetails !== null) {
            $properties['details'] = $auditDetails;
        }

        DB::transaction(function () use ($user, $userId, $isSystemAction, $properties, $gifts) {
            $log = activity()
                ->performedOn($user)
                ->useLog('gdpr')
                ->event('account_deletion')
                ->withProperties($properties);

            if (! $isSystemAction) {
                $log->causedBy($user);
            }

            $log->log('Account deleted');

            foreach ($gifts as $gift) {
                /** @var Gift $gift */
                try {
                    $gift->clearMediaCollection('image');
                } catch (Throwable $e) {
                    report($e);
                }
            }

            $user->delete();
            DB::table('sessions')->where('user_id', $userId)->delete();
        });

        $message = $isSystemAction
            ? "🗑️ {$email} account deleted (inactive 24+ months)"
            : "👋 {$email} deleted their account";

        SlackAlert::message($message);
    }
}
