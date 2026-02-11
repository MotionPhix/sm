<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSchoolProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            $schoolTypeLabel = SchoolType::tryFrom($school->type)?->label();
        }

        return Inertia::render('admin/settings/school-profile/Show', [
            'school' => [
                ...$school->toArray(),
                'logo_url' => $school->logo_path ? Storage::url($school->logo_path) : null,
            ],
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
            'school' => [
                ...$school->toArray(),
                'logo_url' => $school->logo_path ? Storage::url($school->logo_path) : null,
            ],
            'schoolTypes' => SchoolType::options(),
        ]);
    }

    /**
     * Update the school profile.
     */
    public function update(UpdateSchoolProfileRequest $request)
    {
        $school = $request->user()->activeSchool;

        $data = [
            'name' => $request->name,
            'code' => $request->code,
            'email' => $request->email,
            'phone' => $request->phone,
            'type' => $request->type,
            'district' => $request->district,
            'country' => $request->country,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store(
                "schools/{$school->id}/logos",
                'public'
            );
        }

        $school->update($data);

        return redirect()
            ->route('admin.settings.school-profile.show')
            ->with('success', 'School profile updated successfully.');
    }
}
