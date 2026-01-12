<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SchoolInvitationMail;
use App\Models\Role;
use App\Models\SchoolInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SchoolInvitationController extends Controller
{
    public function invite(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/staff/Invite', [
            'invitations' => SchoolInvitation::where('school_id', $school->id)
                ->latest()
                ->get(),
            'roles' => Role::whereNotIn('name', ['super_admin', 'student', 'parent'])->get(['id', 'name', 'label']),
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $invitation = SchoolInvitation::updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => $data['email'],
            ],
            [
                'name' => $data['name'],
                'role_id' => $data['role_id'],
                'token' => Str::uuid(),
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]
        );

        // Mail sending (stub for now)
        if (Mail::to($invitation->email)->send(new SchoolInvitationMail($invitation))) {
            return back()->with('success', 'Invitation sent.');
        }

        return back()->with('error', 'Invitation created but failed to send.');
    }
}
