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
        $validated = $request->validated();
        $school = auth()->user()->activeSchool;

        // Only process new subjects (those without an id)
        $newSubjects = array_filter($validated['subjects'], fn ($s) => !isset($s['id']));

        $errors = [];

        foreach ($newSubjects as $index => $subjectData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                Subject::class,
                $subjectData['name'],
                $school->id,
                'Subject'
            );

            if ($duplicateError) {
                $errors["subjects.{$index}.name"] = $duplicateError;
                continue;
            }

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
