<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Resolve school by route param school or by session/current selection
        $schoolId = $request->route('school') ?? session('school_id');

        if (!$schoolId) {
            abort(403, 'No school context selected.');
        }

        if (!auth()->user()
            ->schools()
            ->where('schools.id', $schoolId)
            ->exists()) {
            abort(403, 'Unauthorized school access.');
        }

        app()->instance('currentSchool', School::findOrFail($schoolId));

        return $next($request);
    }
}
