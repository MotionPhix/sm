<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreStreamsRequest;
use App\Models\Stream;
use App\Traits\PreventsNearDuplicates;
use Inertia\Inertia;

class StreamsSetupController extends Controller
{
    use PreventsNearDuplicates;

    public function create()
    {
        $school = auth()->user()->activeSchool;

        return Inertia::render('onboarding/CreateStreams', [
            'existingStreams' => Stream::where('school_id', $school->id)
                ->get(['id', 'name'])
                ->toArray(),
        ]);
    }

    public function store(StoreStreamsRequest $request)
    {
        // Filter to only validate new streams (those without an id)
        $allStreams = $request->input('streams', []);
        $newStreamsOnly = array_values(array_filter($allStreams, fn ($s) => !isset($s['id'])));

        // Revalidate only new streams with strict rules
        $validated = validator()
            ->make(
                ['streams' => $newStreamsOnly],
                [
                    'streams' => ['required', 'array', 'min:1'],
                    'streams.*.name' => ['required', 'string', 'max:100', 'distinct'],
                ]
            )
            ->validate();

        $school = auth()->user()->activeSchool;
        $errors = [];

        // Process only new streams
        foreach ($validated['streams'] as $index => $streamData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                Stream::class,
                $streamData['name'],
                $school->id,
                'Stream'
            );

            if ($duplicateError) {
                // If duplicate found and user didn't provide password, show error
                if (!$request->input('bypass_password')) {
                    $errors["streams.{$index}.name"] = $duplicateError;
                    continue;
                }

                // If bypass password provided, verify it
                if (!$this->verifyBypassPassword($request->input('bypass_password'))) {
                    $errors["bypass_password"] = "Invalid password.";
                    continue;
                }
            }

            // Create new stream
            Stream::create([
                'school_id' => $school->id,
                'name' => $streamData['name'],
            ]);
        }

        if (!empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        return to_route('onboarding.subjects.create');
    }
}
