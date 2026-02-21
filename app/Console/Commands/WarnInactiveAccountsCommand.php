<?php

namespace App\Console\Commands;

use App\Mail\InactiveAccountWarningMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class WarnInactiveAccountsCommand extends Command
{
    protected $signature = 'app:warn-inactive-accounts';

    protected $description = 'Send warning emails to users inactive for 22+ months';

    public function handle(): int
    {
        $users = User::query()
            ->where('last_active_at', '<', now()->subMonths(22))
            ->whereNull('inactive_warning_sent_at')
            ->where('is_admin', false)
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            $this->info("Sending inactive warning to {$user->email}...");

            try {
                Mail::to($user->email)
                    ->locale($user->locale_preference ?? 'en')
                    ->send(new InactiveAccountWarningMail($user));
                $user->updateQuietly(['inactive_warning_sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send warning to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->comment("Sent {$sent} inactive account warning(s).");

        return Command::SUCCESS;
    }
}
