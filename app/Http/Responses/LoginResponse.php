<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // API / JSON login support
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        // No schools → onboarding
        if ($user->schools()->count() === 0) {
            return to_route('onboarding.school-setup.create');
        }

        // No active school
        if (! $user->active_school_id) {
            if ($user->schools()->count() === 1) {
                $user->setActiveSchool($user->schools()->first());
            } else {
                return to_route('schools.select.index');
            }
        }

        // Role-based landing (future-proof)
        $role = $user->roleForActiveSchool()?->name;

        /*return match ($role) {
            'admin' => to_route('admin.dashboard'),
            'head_teacher', 'teacher' => to_route('teacher.dashboard'),
            'registrar' => to_route('registrar.dashboard'),
            'bursar' => to_route('bursar.dashboard'),
            'accountant' => to_route('accountant.dashboard'),
            'parent' => to_route('parent.dashboard'),
            'student' => to_route('student.dashboard'),
            default => to_route('dashboard'),
        };*/

        $routeMap = [
            'admin'        => 'admin.dashboard',
            'head_teacher' => 'teacher.dashboard',
            'teacher'      => 'teacher.dashboard',
            'registrar'    => 'registrar.dashboard',
            'bursar'       => 'bursar.dashboard',
            'accountant'   => 'accountant.dashboard',
            'parent'       => 'parent.dashboard',
            'student'      => 'student.dashboard',
        ];

        $target = $routeMap[$role] ?? 'admin.dashboard';

        return Route::has($target)
            ? to_route($target)
            : to_route('dashboard');
    }
}
