<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check() &&
            auth()->user()->schools()->count() === 0 &&
            !$request->routeIs('onboarding.*') &&
            !$request->routeIs('admin.register*')
        ) {
            return redirect()->route('onboarding.start');
        }

        return $next($request);
    }
}
