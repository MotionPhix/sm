<?php

namespace App\Http\Middleware;

use App\Models\School;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! $user->active_school_id) {
            abort(403, 'No active school selected.');
        }

        // Ensure user belongs to the active school
        if (! $user->schools()
            ->where('schools.id', $user->active_school_id)
            ->exists()) {
            abort(403, 'Unauthorized school access.');
        }

        // Bind current school globally
        app()->instance(
            'currentSchool',
            School::findOrFail($user->active_school_id)
        );

        return $next($request);
    }
}
