<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->last_active_at || $user->last_active_at->lt(now()->subHour())) {
            $updates = ['last_active_at' => now()];

            if ($user->inactive_warning_sent_at) {
                $updates['inactive_warning_sent_at'] = null;
            }

            User::where('id', $user->id)
                ->where(function ($query) {
                    $query->whereNull('last_active_at')
                        ->orWhere('last_active_at', '<', now()->subHour());
                })
                ->toBase()
                ->update($updates);
        }

        return $next($request);
    }
}
