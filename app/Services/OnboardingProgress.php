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
     * Returns an array with individual checks, an overall complete flag, and the next step.
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

        // Determine the next step
        $nextStep = $this->getNextStep($hasCurrentYear, $hasClasses, $hasStreams, $hasSubjects);

        return [
            'has_current_year' => $hasCurrentYear,
            'has_terms' => $hasTerms,
            'has_classes' => $hasClasses,
            'has_streams' => $hasStreams,
            'has_subjects' => $hasSubjects,
            'complete' => $complete,
            'nextStep' => $nextStep,
        ];
    }

    /**
     * Determine the next onboarding step based on completion status.
     */
    private function getNextStep(bool $hasSchool, bool $hasClasses, bool $hasStreams, bool $hasSubjects): string
    {
        if (!$hasSchool) {
            return 'onboarding.school-setup.create';
        }

        if (!$hasClasses) {
            return 'onboarding.classes.create';
        }

        if (!$hasStreams) {
            return 'onboarding.streams.create';
        }

        if (!$hasSubjects) {
            return 'onboarding.subjects.create';
        }

        return 'admin.dashboard';
    }
}
