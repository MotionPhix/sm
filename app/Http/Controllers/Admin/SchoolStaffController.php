<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SchoolStaffController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/staff/Index', [
            'staff' => fn() => $school->users()
                ->withPivot('role_id', 'is_active')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => Role::find($user->pivot->role_id)?->label,
                        'is_active' => (bool) $user->pivot->is_active,
                    ];
                }),

            'pendingInvitations' => SchoolInvitation::where('school_id', $school->id)
                ->whereNull('accepted_at')
                ->latest()
                ->get(),
        ]);
    }
}
