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
        $validated = $request->validated();
        $school = auth()->user()->activeSchool;

        // Only process new streams (those without an id)
        $newStreams = array_filter($validated['streams'], fn ($s) => !isset($s['id']));

        $errors = [];

        foreach ($newStreams as $index => $streamData) {
            // Check for duplicates
            $duplicateError = $this->checkForDuplicates(
                Stream::class,
                $streamData['name'],
                $school->id,
                'Stream'
            );

            if ($duplicateError) {
                $errors["streams.{$index}.name"] = $duplicateError;
                continue;
            }

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
