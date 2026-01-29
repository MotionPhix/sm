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
        // Filter to only validate new classes (those without an id)
        $allClasses = $request->input('classes', []);
        $newClassesOnly = array_values(array_filter($allClasses, fn ($c) => !isset($c['id'])));

        $school = auth()->user()->activeSchool;

        // Get the last class order for this school
        $lastClassOrder = SchoolClass::where('school_id', $school->id)
            ->max('order') ?? 0;

        // Revalidate only new classes with strict rules
        $validated = validator()
            ->make(
                ['classes' => $newClassesOnly],
                [
                    'classes' => ['required', 'array', 'min:1'],
                    'classes.*.name' => [
                        'required',
                        'string',
                        'max:100',
                        'distinct',
                        'regex:/^[A-Z][a-z]+ \d+$/',
                    ],
                    'classes.*.order' => [
                        'required',
                        'integer',
                        'min:' . ($lastClassOrder + 1),
                        'distinct',
                    ],
                ],
                [
                    'classes.*.name.distinct' => 'Class names must be unique.',
                    'classes.*.name.regex' => 'Class names must be in the format "Class Name 1".',
                    'classes.*.order.distinct' => 'Class orders must be unique.',
                    'classes.*.order.min' => "Class order must start from " . ($lastClassOrder + 1) . ".",
                    'classes.*.order.required' => 'Class order is required.',
                    'classes.required' => 'You haven\'t added any new classes.',
                ]
            )
            ->validate();

        $errors = [];

        // Process only new classes
        foreach ($validated['classes'] as $index => $classData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                SchoolClass::class,
                $classData['name'],
                $school->id,
                'Class'
            );

            if ($duplicateError) {
                // If duplicate found and user didn't provide password, show error
                if (!$request->input('bypass_password')) {
                    $errors["classes.{$index}.name"] = $duplicateError;
                    continue;
                }

                // If bypass password provided, verify it
                if (!$this->verifyBypassPassword($request->input('bypass_password'))) {
                    $errors["bypass_password"] = "Invalid password.";
                    continue;
                }

                // Even with bypass, strictly validate that the name doesn't exist (safety check)
                if ($this->hasExactDuplicate(SchoolClass::class, $classData['name'], $school->id)) {
                    $errors["classes.{$index}.name"] = "Class '{$classData['name']}' already exists in the system.";
                    continue;
                }
            }

            // Create new class
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
