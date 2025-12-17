<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $schoolId = session('school_id');

        $role = auth()->user()
            ->schools()
            ->where('schools.id', $schoolId)
            ->first()
            ->pivot
            ->role_id;

        if (!in_array(Role::find($role)->name, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
