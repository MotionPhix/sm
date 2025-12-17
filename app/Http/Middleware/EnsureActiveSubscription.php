<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = app('currentSchool');

        if (!$school->subscription?->is_active ||
            $school->subscription->ends_at->isPast()) {
            abort(402, 'Subscription expired.');
        }

        return $next($request);
    }
}
