<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->last_active_at || $user->last_active_at->diffInHours(now()) >= 1)) {
            $user->updateQuietly(['last_active_at' => now()]);
        }

        return $next($request);
    }
}
