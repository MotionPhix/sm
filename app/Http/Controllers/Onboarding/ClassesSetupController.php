<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreClassesRequest;
use App\Models\SchoolClass;
use App\Traits\PreventsNearDuplicates;
use Inertia\Inertia;

class ClassesSetupController extends Controller
{
    use PreventsNearDuplicates;

    public function create()
    {
        $school = auth()->user()->activeSchool;

        return Inertia::render('onboarding/CreateClasses', [
            'existingClasses' => SchoolClass::where('school_id', $school->id)
                ->orderBy('order')
                ->get(['id', 'name', 'order'])
                ->toArray(),
        ]);
    }

    public function store(StoreClassesRequest $request)
    {
        $validated = $request->validated();
        $school = auth()->user()->activeSchool;

        // Only process new classes (those without an id)
        $newClasses = array_filter($validated['classes'], fn ($c) => !isset($c['id']));

        $errors = [];

        foreach ($newClasses as $index => $classData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                SchoolClass::class,
                $classData['name'],
                $school->id,
                'Class'
            );

            if ($duplicateError) {
                $errors["classes.{$index}.name"] = $duplicateError;
                continue;
            }

            SchoolClass::create([
                'school_id' => $school->id,
                'name' => $classData['name'],
                'order' => $classData['order'],
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        return to_route('onboarding.streams.create');
    }
}
