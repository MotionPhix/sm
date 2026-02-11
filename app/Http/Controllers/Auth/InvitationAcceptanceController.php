<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InvitationAcceptanceController extends Controller
{
    public function show(string $token)
    {
        $invitation = SchoolInvitation::where('token', $token)
            ->with(['school', 'role'])
            ->whereNull('accepted_at')
            ->firstOrFail();

        abort_if($invitation->isExpired(), 410);

        return inertia('auth/AcceptInvitation', [
            'name' => $invitation->name ?? '',
            'email' => $invitation->email,
            'school' => $invitation->school->name,
            'role' => $invitation->role->label,
            'token' => $token,
            'userExists' => User::where('email', $invitation->email)->exists(),
        ]);
    }

    public function store(Request $request, string $token)
    {
        $invitation = SchoolInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->firstOrFail();

        abort_if($invitation->isExpired(), 410);

        $user = User::where('email', $invitation->email)->first();

        if (! $user) {
            $data = $request->validate([
                'name' => ['required', 'string'],
                'password' => ['required', Password::default()],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);
        }

        // Accepting an invitation proves email ownership — mark as verified
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Attach user to school
        $user->schools()->syncWithoutDetaching([
            $invitation->school_id => [
                'role_id' => $invitation->role_id,
                'is_active' => true,
            ],
        ]);

        $user->setActiveSchool($invitation->school);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return to_route('dashboard.redirect');
    }
}
