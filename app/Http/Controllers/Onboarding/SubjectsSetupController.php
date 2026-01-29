<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreSubjectsRequest;
use App\Models\Subject;
use App\Traits\PreventsNearDuplicates;
use Inertia\Inertia;

class SubjectsSetupController extends Controller
{
    use PreventsNearDuplicates;

    public function create()
    {
        $school = auth()->user()->activeSchool;

        return Inertia::render('onboarding/CreateSubjects', [
            'existingSubjects' => Subject::where('school_id', $school->id)
                ->get(['id', 'name', 'code'])
                ->toArray(),
        ]);
    }

    public function store(StoreSubjectsRequest $request)
    {
        // Filter to only validate new subjects (those without an id)
        $allSubjects = $request->input('subjects', []);
        $newSubjectsOnly = array_values(array_filter($allSubjects, fn ($s) => !isset($s['id'])));

        // Revalidate only new subjects with strict rules
        $validated = validator()
            ->make(
                ['subjects' => $newSubjectsOnly],
                [
                    'subjects' => ['required', 'array', 'min:1'],
                    'subjects.*.name' => ['required', 'string', 'max:100', 'distinct'],
                    'subjects.*.code' => ['required', 'string', 'max:20', 'distinct'],
                ]
            )
            ->validate();

        $school = auth()->user()->activeSchool;
        $errors = [];

        // Process only new subjects
        foreach ($validated['subjects'] as $index => $subjectData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                Subject::class,
                $subjectData['name'],
                $school->id,
                'Subject'
            );

            if ($duplicateError) {
                // If duplicate found and user didn't provide password, show error
                if (!$request->input('bypass_password')) {
                    $errors["subjects.{$index}.name"] = $duplicateError;
                    continue;
                }

                // If bypass password provided, verify it
                if (!$this->verifyBypassPassword($request->input('bypass_password'))) {
                    $errors["bypass_password"] = "Invalid password.";
                    continue;
                }
            }

            // Create new subject
            Subject::create([
                'school_id' => $school->id,
                'name' => $subjectData['name'],
                'code' => $subjectData['code'],
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        return to_route('admin.dashboard');
    }
}
