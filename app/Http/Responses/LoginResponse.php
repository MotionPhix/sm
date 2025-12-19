<?php

namespace App\Http\Responses;

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

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'head_teacher', 'teacher' => redirect()->route('teacher.dashboard'),
            'parent' => redirect()->route('parent.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}
