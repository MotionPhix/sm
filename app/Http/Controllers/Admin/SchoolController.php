<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SchoolController extends Controller
{
    public function create()
    {
        return Inertia::render('admin/schools/CreateSchool');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:schools,code',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $school = School::create($data);

            SchoolSetting::create([
                'school_id' => $school->id,
            ]);

            Subscription::create([
                'school_id' => $school->id,
                'plan' => 'trial',
                'starts_at' => now(),
                'ends_at' => now()->addDays(30),
            ]);

            $adminRole = Role::where('name', 'admin')->first();

            auth()->user()->schools()->attach($school->id, [
                'role_id' => $adminRole->id,
            ]);
        });

        return redirect()->route('admin.dashboard');
    }
}
