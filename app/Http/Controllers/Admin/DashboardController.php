<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AdmissionCycle;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $school = $request->user()->activeSchool;

        // Get stats
        $staffCount = $school->users()->count();
        $academicYearsCount = $school->academicYears()->count();
        $termsCount = Term::where('school_id', $school->id)->count();
        $admissionCyclesCount = AdmissionCycle::where('school_id', $school->id)->count();

        // Get setup status
        $hasAcademicYears = $academicYearsCount > 0;
        $hasTerms = $termsCount > 0;
        $hasClasses = SchoolClass::where('school_id', $school->id)->exists();
        $hasSubjects = Subject::where('school_id', $school->id)->exists();

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'staffCount' => $staffCount,
                'academicYearsCount' => $academicYearsCount,
                'termsCount' => $termsCount,
                'admissionCyclesCount' => $admissionCyclesCount,
            ],
            'setupStatus' => [
                'schoolProfile' => [
                    'completed' => true, // Completed during onboarding
                    'href' => null,
                ],
                'academicCalendar' => [
                    'completed' => $hasAcademicYears && $hasTerms,
                    'inProgress' => $hasAcademicYears || $hasTerms,
                    'href' => route('admin.settings.academic-year.index'),
                ],
                'classesAndStreams' => [
                    'completed' => $hasClasses,
                    'inProgress' => false,
                    'href' => null, // Will add when UI is created
                ],
                'subjects' => [
                    'completed' => $hasSubjects,
                    'inProgress' => false,
                    'href' => null, // Will add when UI is created
                ],
                'staffAssignments' => [
                    'completed' => false,
                    'inProgress' => false,
                    'href' => null, // Will add when UI is created
                ],
                'gradingScheme' => [
                    'completed' => false,
                    'inProgress' => false,
                    'href' => null, // Will add when UI is created
                ],
            ],
        ]);
    }
}
