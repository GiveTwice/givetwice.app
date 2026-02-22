<?php

namespace App\Console\Commands;

use App\Mail\InactiveAccountWarningMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class WarnInactiveAccountsCommand extends Command
{
    protected $signature = 'app:warn-inactive-accounts';

    protected $description = 'Send warning emails to users inactive for 22+ months';

    public function handle(): int
    {
        $users = User::query()
            ->inactiveSince(22)
            ->whereNull('inactive_warning_sent_at')
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            $this->info("Sending inactive warning to {$user->email}...");

            try {
                Mail::to($user->email)
                    ->locale($user->locale_preference ?? config('app.locale'))
                    ->send(new InactiveAccountWarningMail($user));
                $user->updateQuietly(['inactive_warning_sent_at' => now()]);
                $sent++;
            } catch (Throwable $e) {
                report($e);
                $this->error("Failed to send warning to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->comment("Sent {$sent} inactive account warning(s).");

        return Command::SUCCESS;
    }
}
