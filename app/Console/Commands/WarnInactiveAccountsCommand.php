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

        foreach ($users as $user) {
            Mail::to($user->email)->send(new InactiveAccountWarningMail($user));

            $user->updateQuietly(['inactive_warning_sent_at' => now()]);
        }

        $this->info("Sent {$users->count()} inactive account warning(s).");

        return Command::SUCCESS;
    }
}
