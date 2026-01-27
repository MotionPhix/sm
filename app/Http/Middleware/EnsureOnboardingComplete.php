<?php

namespace App\Http\Middleware;

use App\Services\OnboardingProgress;
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
        // Always allow guests to proceed to registration/onboarding
        if (!auth()->check()) {
            return $next($request);
        }

        // Allow onboarding and admin registration routes without gating
        // Also allow API onboarding data endpoints (for dropdowns, etc)
        if ($request->routeIs('onboarding.*', 'api.onboarding.*') || $request->routeIs('admin.register*')) {
            return $next($request);
        }

        $user = $request->user();

        // If user has no schools, redirect to onboarding
        if ($user->schools()->count() === 0) {
            return redirect()->route('onboarding.school-setup.create');
        }

        // If active school is missing, let the school selection or onboarding handle it
        $school = $user->activeSchool;
        if (!$school) {
            return redirect()->route('schools.select.index');
        }

        // Check onboarding readiness for the active school
        $progress = app(OnboardingProgress::class)->status($school);
        if (!$progress['complete']) {
            return redirect()->route('onboarding.school-setup.create')
                ->with('onboarding_progress', $progress);
        }

        return $next($request);
    }
}
