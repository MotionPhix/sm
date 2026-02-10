<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSchoolProfileRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolProfileController extends Controller
{
    /**
     * Display the school profile.
     */
    public function show(Request $request)
    {
        $school = $request->user()->activeSchool;

        $schoolTypeLabel = null;
        if ($school->type) {
            $schoolTypeLabel = SchoolType::from($school->type)->label();
        }

        return Inertia::render('admin/settings/school-profile/Show', [
            'school' => $school,
            'schoolTypeLabel' => $schoolTypeLabel,
        ]);
    }

    /**
     * Show the form for editing the school profile.
     */
    public function edit(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/settings/school-profile/Edit', [
            'school' => $school,
            'schoolTypes' => SchoolType::options(),
        ]);
    }

    /**
     * Update the school profile.
     */
    public function update(UpdateSchoolProfileRequest $request)
    {
        $school = $request->user()->activeSchool;

        $school->update([
            'name' => $request->name,
            'code' => $request->code,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'district' => $request->district,
            'country' => $request->country,
        ]);

        return redirect()
            ->route('admin.settings.school-profile.show')
            ->with('success', 'School profile updated successfully.');
    }
}
