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
        $user = $request->user();
        $school = app('school.context')->model;
        abort_unless($user && $school, 403);
        $has = \DB::table('school_user')
            ->where('school_id', $school->id)
            ->where('user_id', $user->id)
            ->whereIn('role', $roles)
            ->exists();
        abort_unless($has, 403);
        return $next($request);
    }
}
