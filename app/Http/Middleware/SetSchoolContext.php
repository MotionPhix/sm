<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSchoolContext
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
        if ($schoolId) {
            $school = \App\Models\School::findOrFail($schoolId);
            app()->instance('school.context', (object)['id' => $school->id, 'model' => $school]);
        } else {
            app()->instance('school.context', (object)['id' => null, 'model' => null]);
        }
        return $next($request);
    }
}
