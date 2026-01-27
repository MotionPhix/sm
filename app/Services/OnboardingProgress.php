<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Term;

class OnboardingProgress
{
    /**
     * Compute onboarding readiness for a given school.
     * Returns an array with individual checks and an overall complete flag.
     */
    public function status(School $school): array
    {
        $currentYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $hasCurrentYear = (bool) $currentYear;
        $hasTerms = $hasCurrentYear
            ? Term::where('academic_year_id', $currentYear->id)->count() >= 3
            : false;

        $hasClasses = SchoolClass::where('school_id', $school->id)->exists();
        $hasStreams = Stream::where('school_id', $school->id)->exists();
        $hasSubjects = Subject::where('school_id', $school->id)->exists();

        $complete = $hasCurrentYear && $hasTerms && $hasClasses && $hasStreams && $hasSubjects;

        return [
            'has_current_year' => $hasCurrentYear,
            'has_terms' => $hasTerms,
            'has_classes' => $hasClasses,
            'has_streams' => $hasStreams,
            'has_subjects' => $hasSubjects,
            'complete' => $complete,
        ];
    }
}
