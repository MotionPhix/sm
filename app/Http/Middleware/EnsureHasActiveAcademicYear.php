<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasActiveAcademicYear
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $school = $request->user()?->activeSchool;

        if (! $school || ! $school->currentAcademicYear) {
            return to_route('academic-years.create')
                ->with('error', 'Please create and activate an academic year.');
        }

        return $next($request);
    }
}
