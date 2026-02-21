<?php

namespace App\Console\Commands;

use App\Actions\DeleteAccountAction;
use App\Models\User;
use Illuminate\Console\Command;

class DeleteInactiveAccountsCommand extends Command
{
    protected $signature = 'app:delete-inactive-accounts {--dry-run : List accounts without deleting}';

    protected $description = 'Delete accounts inactive for 24+ months that were warned 2+ months ago';

    public function handle(DeleteAccountAction $action): int
    {
        $users = User::query()
            ->with('gifts')
            ->where('last_active_at', '<', now()->subMonths(24))
            ->whereNotNull('inactive_warning_sent_at')
            ->where('inactive_warning_sent_at', '<', now()->subMonths(2))
            ->where('is_admin', false)
            ->get();

        if ($this->option('dry-run')) {
            $this->info("Dry run: found {$users->count()} account(s) eligible for deletion.");

            foreach ($users as $user) {
                $this->line("  - {$user->email} (last active: {$user->last_active_at->format('Y-m-d')}, warned: {$user->inactive_warning_sent_at->format('Y-m-d')})");
            }

            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            $action->execute($user, 'Inactive 24+ months', 'system');
        }

        $this->info("Deleted {$users->count()} inactive account(s).");

        return Command::SUCCESS;
    }
}
