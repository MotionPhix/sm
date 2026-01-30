<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registrar\EnrollStudentRequest;
use App\Http\Requests\Registrar\StoreStudentRequest;
use App\Http\Requests\Registrar\TransferStudentRequest;
use App\Http\Requests\Registrar\UpdateStudentRequest;
use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\ClassStreamAssignment;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    /**
     * List all students with filters.
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $query = Student::where('school_id', $school->id)
            ->with(['activeEnrollment.classroom.class', 'activeEnrollment.classroom.stream']);

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('is_active', true)
                    ->whereHas('classroom', function ($cq) use ($request) {
                        $cq->where('school_class_id', $request->class_id);
                    });
            });
        }

        // Filter by stream
        if ($request->filled('stream_id')) {
            $query->whereHas('enrollments', function ($q) use ($request) {
                $q->where('is_active', true)
                    ->whereHas('classroom', function ($cq) use ($request) {
                        $cq->where('stream_id', $request->stream_id);
                    });
            });
        }

        // Filter by status (enrolled, withdrawn, etc.)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereHas('enrollments', fn ($q) => $q->where('is_active', true));
            } elseif ($request->status === 'inactive') {
                $query->whereDoesntHave('enrollments', fn ($q) => $q->where('is_active', true));
            }
        }

        // Search by name or admission number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()
            ->paginate($request->input('per_page', 20))
            ->withQueryString();

        $classes = SchoolClass::where('school_id', $school->id)->get();
        $streams = Stream::where('school_id', $school->id)->get();

        return Inertia::render('registrar/students/Index', [
            'students' => $students,
            'classes' => $classes,
            'streams' => $streams,
            'filters' => $request->only(['search', 'class_id', 'stream_id', 'status']),
        ]);
    }

    /**
     * Show student details with enrollment history.
     */
    public function show(Request $request, Student $student)
    {
        $student->load([
            'enrollments.classroom.class',
            'enrollments.classroom.stream',
            'guardians',
        ]);

        $school = $request->user()->activeSchool;
        $currentAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $availableClasses = SchoolClass::where('school_id', $school->id)->get();
        $availableStreams = Stream::where('school_id', $school->id)->get();
        $classStreamAssignments = ClassStreamAssignment::where('school_id', $school->id)
            ->where('academic_year_id', $currentAcademicYear?->id)
            ->with(['class', 'stream'])
            ->get();

        return Inertia::render('registrar/students/Show', [
            'student' => $student,
            'currentAcademicYear' => $currentAcademicYear,
            'availableClasses' => $availableClasses,
            'availableStreams' => $availableStreams,
            'classStreamAssignments' => $classStreamAssignments,
        ]);
    }

    /**
     * Show form to create a new student.
     */
    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;
        $currentAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        // Get admitted applicants that haven't been enrolled yet
        $admittedApplicants = Applicant::where('school_id', $school->id)
            ->where('status', 'admitted')
            ->whereDoesntHave('student')
            ->with('admissionCycle')
            ->get();

        return Inertia::render('registrar/students/Create', [
            'admittedApplicants' => $admittedApplicants,
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Create a new student from applicant or direct enrollment.
     */
    public function store(StoreStudentRequest $request)
    {
        $school = $request->user()->activeSchool;
        $validated = $request->validated();

        // If enrolling from applicant
        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('school_id', $school->id)
                ->where('id', $validated['applicant_id'])
                ->firstOrFail();

            $student = $this->studentService->enrollFromApplicant($applicant, $validated);
        } else {
            // Direct enrollment
            $student = $this->studentService->createStudent($school->id, $validated);
        }

        return redirect()->route('registrar.students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    /**
     * Show form to edit student.
     */
    public function edit(Request $request, Student $student)
    {
        $school = $request->user()->activeSchool;

        $student->load(['guardians', 'enrollments']);

        return Inertia::render('registrar/students/Edit', [
            'student' => $student,
        ]);
    }

    /**
     * Update student information.
     */
    public function update(UpdateStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        $student->update($validated);

        return back()->with('success', 'Student updated successfully.');
    }

    /**
     * Soft delete a student.
     */
    public function destroy(Request $request, Student $student)
    {
        $student->delete();

        return redirect()->route('registrar.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Show enrollment form.
     */
    public function enrollForm(Request $request, Student $student)
    {
        $school = $request->user()->activeSchool;
        $currentAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        // Get class-stream assignments for current academic year
        $classStreamAssignments = ClassStreamAssignment::where('school_id', $school->id)
            ->where('academic_year_id', $currentAcademicYear?->id)
            ->with(['class', 'stream'])
            ->get()
            ->map(function ($assignment) {
                $currentCount = $assignment->studentEnrollments()
                    ->where('is_active', true)
                    ->count();

                return [
                    'id' => $assignment->id,
                    'name' => $assignment->display_name,
                    'class_id' => $assignment->school_class_id,
                    'stream_id' => $assignment->stream_id,
                    'capacity' => null, // Can be added if needed
                    'current_enrollment' => $currentCount,
                    'available_spots' => null, // Can be calculated if capacity is set
                ];
            });

        return Inertia::render('registrar/students/Enroll', [
            'student' => $student,
            'classStreamAssignments' => $classStreamAssignments,
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Enroll student in a class/stream for current academic year.
     */
    public function enroll(EnrollStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        $this->studentService->enrollStudent($student, $validated);

        return redirect()->route('registrar.students.show', $student)
            ->with('success', 'Student enrolled successfully.');
    }

    /**
     * Show transfer form.
     */
    public function transferForm(Request $request, Student $student)
    {
        $school = $request->user()->activeSchool;
        $currentAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        // Get class-stream assignments for current academic year excluding current enrollment
        $currentEnrollmentId = $student->enrollments()
            ->where('is_active', true)
            ->value('class_stream_assignment_id');

        $classStreamAssignments = ClassStreamAssignment::where('school_id', $school->id)
            ->where('academic_year_id', $currentAcademicYear?->id)
            ->when($currentEnrollmentId, function ($query) use ($currentEnrollmentId) {
                $query->where('id', '!=', $currentEnrollmentId);
            })
            ->with(['class', 'stream'])
            ->get()
            ->map(function ($assignment) {
                $currentCount = $assignment->studentEnrollments()
                    ->where('is_active', true)
                    ->count();

                return [
                    'id' => $assignment->id,
                    'name' => $assignment->display_name,
                    'class_id' => $assignment->school_class_id,
                    'stream_id' => $assignment->stream_id,
                    'current_enrollment' => $currentCount,
                ];
            });

        return Inertia::render('registrar/students/Transfer', [
            'student' => $student,
            'classStreamAssignments' => $classStreamAssignments,
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Transfer student between classes/streams.
     */
    public function transfer(TransferStudentRequest $request, Student $student)
    {
        $validated = $request->validated();

        $this->studentService->transferStudent($student, $validated);

        return redirect()->route('registrar.students.show', $student)
            ->with('success', 'Student transferred successfully.');
    }

    /**
     * Withdraw student with reason.
     */
    public function withdraw(Request $request, Student $student)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
            'withdrawal_date' => ['required', 'date'],
        ]);

        $this->studentService->withdrawStudent($student, $validated);

        return redirect()->route('registrar.students.show', $student)
            ->with('success', 'Student withdrawn successfully.');
    }

    /**
     * Get available classes/streams for enrollment (API endpoint).
     */
    public function getAvailableClassStreams(Request $request): JsonResponse
    {
        $school = $request->user()->activeSchool;
        $academicYearId = $request->input('academic_year_id');

        $query = ClassStreamAssignment::where('school_id', $school->id)
            ->with(['class', 'stream']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        } else {
            $currentYear = AcademicYear::where('school_id', $school->id)
                ->where('is_current', true)
                ->first();
            $query->where('academic_year_id', $currentYear?->id);
        }

        $assignments = $query->get()
            ->map(function ($assignment) {
                $currentCount = $assignment->studentEnrollments()
                    ->where('is_active', true)
                    ->count();

                return [
                    'id' => $assignment->id,
                    'name' => $assignment->display_name,
                    'class' => $assignment->class?->name,
                    'stream' => $assignment->stream?->name,
                    'current_enrollment' => $currentCount,
                ];
            });

        return response()->json(['classStreams' => $assignments]);
    }
}
