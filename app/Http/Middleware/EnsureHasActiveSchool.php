<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActiveSchool
{
    /**
     * Routes that should bypass school checks.
     */
    protected array $exceptRoutes = [
        'login',
        'logout',
        'admin.register.*',
        'password.request',
        'password.reset',
        'password.email',
        'password.update',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'onboarding.school-setup.create',
        'onboarding.school-setup.store',
        'schools.select.*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (! $request->user()) {
            return $next($request);
        }

        if ($request->route() &&
            in_array($request->route()->getName(), $this->exceptRoutes, true)) {
            return $next($request);
        }

        $user = $request->user();

        // User must already have at least one school
        if ($user->schools()->count() === 0) {
            abort(403, 'No school associated with account.');
        }

        // Must have active school
        if (! $user->active_school_id) {
            abort(403, 'No active school selected.');
        }

        return $next($request);
    }
}
