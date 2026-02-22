<?php

namespace App\Console\Commands;

use App\Models\ListInvitation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PruneExpiredDataCommand extends Command
{
    private const INVITATION_RETENTION_DAYS = 60;

    private const PASSWORD_TOKEN_RETENTION_HOURS = 1;

    private const GUEST_SESSION_RETENTION_DAYS = 7;

    private const AUTH_SESSION_RETENTION_DAYS = 30;

    protected $signature = 'app:prune-expired-data';

    protected $description = 'Prune expired invitations, password tokens, and stale sessions';

    public function handle(): int
    {
        $this->pruneExpiredInvitations();
        $this->prunePasswordResetTokens();
        $this->pruneGuestSessions();
        $this->pruneAuthenticatedSessions();

        return Command::SUCCESS;
    }

    protected function pruneExpiredInvitations(): void
    {
        $count = ListInvitation::query()
            ->where('created_at', '<', now()->subDays(self::INVITATION_RETENTION_DAYS))
            ->where(function (Builder $query) {
                $query->whereNotNull('accepted_at')
                    ->orWhereNotNull('declined_at')
                    ->orWhere('expires_at', '<', now());
            })
            ->delete();

        $this->info("Pruned {$count} expired list invitations.");
    }

    protected function prunePasswordResetTokens(): void
    {
        $count = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(self::PASSWORD_TOKEN_RETENTION_HOURS))
            ->delete();

        $this->info("Pruned {$count} expired password reset tokens.");
    }

    protected function pruneGuestSessions(): void
    {
        $count = DB::table('sessions')
            ->whereNull('user_id')
            ->where('last_activity', '<', now()->subDays(self::GUEST_SESSION_RETENTION_DAYS)->timestamp)
            ->delete();

        $this->info("Pruned {$count} stale guest sessions.");
    }

    protected function pruneAuthenticatedSessions(): void
    {
        $count = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '<', now()->subDays(self::AUTH_SESSION_RETENTION_DAYS)->timestamp)
            ->delete();

        $this->info("Pruned {$count} old authenticated sessions.");
    }
}
