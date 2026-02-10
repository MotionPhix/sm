<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AdmissionCycle;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $school = $request->user()->activeSchool;
        $user = $request->user();

        // Get basic counts
        $staffCount = $school->users()->count();
        $academicYearsCount = $school->academicYears()->count();
        $termsCount = Term::where('school_id', $school->id)->count();
        $admissionCyclesCount = AdmissionCycle::where('school_id', $school->id)->count();
        $classesCount = SchoolClass::where('school_id', $school->id)->count();
        $streamsCount = Stream::where('school_id', $school->id)->count();
        $subjectsCount = Subject::where('school_id', $school->id)->count();

        // Get student counts
        $totalStudents = Student::where('school_id', $school->id)->count();
        $maleStudents = Student::where('school_id', $school->id)->where('gender', 'male')->count();
        $femaleStudents = Student::where('school_id', $school->id)->where('gender', 'female')->count();

        // Get active academic year and term
        $activeAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $activeTerm = $activeAcademicYear
            ? Term::where('academic_year_id', $activeAcademicYear->id)
                ->where('is_current', true)
                ->first()
            : null;

        // Calculate week of term (if active term exists)
        $weekOfTerm = null;
        $totalWeeks = null;
        if ($activeTerm && $activeTerm->start_date) {
            $startDate = $activeTerm->start_date;
            $endDate = $activeTerm->end_date;
            $now = now();

            if ($now->gte($startDate) && $now->lte($endDate)) {
                $weekOfTerm = (int) ceil($now->diffInDays($startDate) / 7) + 1;
                $totalWeeks = (int) ceil($endDate->diffInDays($startDate) / 7);
            }
        }

        // Get fee collection stats (if invoices/payments exist)
        $totalFeeExpected = Invoice::where('school_id', $school->id)->sum('total_amount');
        $totalFeeCollected = Payment::whereHas('invoice', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })->sum('amount');
        $totalFeePending = $totalFeeExpected - $totalFeeCollected;
        $collectionRate = $totalFeeExpected > 0
            ? round(($totalFeeCollected / $totalFeeExpected) * 100, 1)
            : 0;

        // Get student distribution by class (through class stream assignments)
        $studentsByClass = SchoolClass::where('school_id', $school->id)
            ->orderBy('order')
            ->get()
            ->map(function ($class) use ($school) {
                // Count students through class stream assignments
                $count = Student::where('school_id', $school->id)
                    ->whereHas('currentClassroom', function ($q) use ($class) {
                        $q->where('school_class_id', $class->id);
                    })
                    ->count();

                return [
                    'name' => $class->name,
                    'count' => $count,
                ];
            });

        // Get open admission cycles
        $openAdmissionCycles = AdmissionCycle::where('school_id', $school->id)
            ->where('status', 'open')
            ->count();

        // Pending applicants count
        $pendingApplicants = $school->applicants()
            ->where('status', 'pending')
            ->count();

        // Setup status with proper links to new settings pages
        $hasAcademicYears = $academicYearsCount > 0;
        $hasTerms = $termsCount > 0;
        $hasClasses = $classesCount > 0;
        $hasStreams = $streamsCount > 0;
        $hasSubjects = $subjectsCount > 0;

        return Inertia::render('admin/Dashboard', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'staffCount' => $staffCount,
                'academicYearsCount' => $academicYearsCount,
                'termsCount' => $termsCount,
                'admissionCyclesCount' => $admissionCyclesCount,
                'classesCount' => $classesCount,
                'streamsCount' => $streamsCount,
                'subjectsCount' => $subjectsCount,
                'totalStudents' => $totalStudents,
                'maleStudents' => $maleStudents,
                'femaleStudents' => $femaleStudents,
                'openAdmissionCycles' => $openAdmissionCycles,
                'pendingApplicants' => $pendingApplicants,
            ],
            'feeStats' => [
                'totalExpected' => $totalFeeExpected,
                'totalCollected' => $totalFeeCollected,
                'totalPending' => $totalFeePending,
                'collectionRate' => $collectionRate,
            ],
            'activeAcademicYear' => $activeAcademicYear ? [
                'id' => $activeAcademicYear->id,
                'name' => $activeAcademicYear->name,
            ] : null,
            'activeTerm' => $activeTerm ? [
                'id' => $activeTerm->id,
                'name' => $activeTerm->name,
                'weekOfTerm' => $weekOfTerm,
                'totalWeeks' => $totalWeeks,
            ] : null,
            'studentsByClass' => $studentsByClass,
            'setupStatus' => [
                'schoolProfile' => [
                    'completed' => true,
                    'href' => route('admin.settings.school-profile.show'),
                ],
                'academicCalendar' => [
                    'completed' => $hasAcademicYears && $hasTerms,
                    'inProgress' => $hasAcademicYears || $hasTerms,
                    'href' => route('admin.settings.academic-year.index'),
                ],
                'classesAndStreams' => [
                    'completed' => $hasClasses && $hasStreams,
                    'inProgress' => $hasClasses || $hasStreams,
                    'href' => route('admin.settings.classes.index'),
                ],
                'subjects' => [
                    'completed' => $hasSubjects,
                    'inProgress' => false,
                    'href' => route('admin.settings.subjects.index'),
                ],
                'staffAssignments' => [
                    'completed' => false,
                    'inProgress' => false,
                    'href' => route('admin.staff.index'),
                ],
                'gradingScheme' => [
                    'completed' => false,
                    'inProgress' => false,
                    'href' => null,
                ],
            ],
        ]);
    }
}
