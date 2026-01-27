<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\ClassStreamAssignment;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    /**
     * Display the attendance index page with class/stream selection.
     */
    public function index(Request $request): Response
    {
        $school = $request->user()->activeSchool();
        $year = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $classes = SchoolClass::query()
            ->where('school_id', $school->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $streams = Stream::query()
            ->where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        $date = $request->date
            ? Carbon::parse($request->date)->toDateString()
            : now()->toDateString();

        return Inertia::render('teacher/attendance/Index', [
            'date' => $date,
            'classes' => $classes,
            'streams' => $streams,
            'academicYear' => $year,
        ]);
    }

    /**
     * Get the student roster for a specific class/stream/date.
     */
    public function roster(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'date' => ['required', 'date'],
        ]);

        $school = $request->user()->activeSchool();
        $class = SchoolClass::where('school_id', $school->id)->findOrFail($request->class_id);
        $stream = $request->stream_id
            ? Stream::where('school_id', $school->id)->findOrFail($request->stream_id)
            : null;

        // Validate teacher authorization
        $this->authorizeTeacherForClass($request->user(), $class, $stream);

        // Validate date is within current academic year
        $currentYear = $this->validateAcademicYearContext($school->id, $request->date);

        // Validate date is within an active term
        $this->validateTermContext($school->id, $currentYear->id, $request->date);

        // Get students enrolled in this class/stream for the current year
        $students = $this->getEnrolledStudents($school->id, $currentYear->id, $class->id, $stream?->id);

        // Get existing attendance records
        $existing = AttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $currentYear->id)
            ->whereDate('date', $request->date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return response()->json([
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    /**
     * Display the attendance recording form.
     */
    public function record(Request $request): Response
    {
        $request->validate([
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'date' => ['required', 'date'],
        ]);

        $school = $request->user()->activeSchool();
        $class = SchoolClass::where('school_id', $school->id)->findOrFail($request->class_id);
        $stream = $request->stream_id
            ? Stream::where('school_id', $school->id)->findOrFail($request->stream_id)
            : null;

        // Validate teacher authorization
        $this->authorizeTeacherForClass($request->user(), $class, $stream);

        // Validate date is within current academic year
        $currentYear = $this->validateAcademicYearContext($school->id, $request->date);

        // Validate date is within an active term
        $this->validateTermContext($school->id, $currentYear->id, $request->date);

        // Get students enrolled in this class/stream for the current year
        $students = $this->getEnrolledStudents($school->id, $currentYear->id, $class->id, $stream?->id);

        // Get existing attendance records
        $existing = AttendanceRecord::query()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $currentYear->id)
            ->whereDate('date', $request->date)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return Inertia::render('teacher/attendance/Record', [
            'date' => $request->date,
            'classId' => $class->id,
            'className' => $class->name,
            'streamId' => $stream?->id,
            'streamName' => $stream?->name,
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    /**
     * Store or update attendance records.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late,excused'],
            'records.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $school = $request->user()->activeSchool();
        $year = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->firstOrFail();

        $class = SchoolClass::where('school_id', $school->id)->findOrFail($data['class_id']);
        $stream = $data['stream_id']
            ? Stream::where('school_id', $school->id)->findOrFail($data['stream_id'])
            : null;

        // Validate teacher authorization
        $this->authorizeTeacherForClass($request->user(), $class, $stream);

        // Validate date is within current academic year
        if (!Carbon::parse($data['date'])->between($year->starts_at, $year->ends_at)) {
            abort(400, 'Attendance cannot be recorded outside the current academic year.');
        }

        // Validate date is within an active term
        $this->validateTermContext($school->id, $year->id, $data['date']);

        $now = now();

        foreach ($data['records'] as $rec) {
            // Use updateOrCreate to prevent duplicates and handle updates atomically
            AttendanceRecord::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $year->id,
                    'date' => $data['date'],
                    'student_id' => $rec['student_id'],
                ],
                [
                    'school_class_id' => $class->id,
                    'stream_id' => $stream?->id,
                    'status' => $rec['status'],
                    'remarks' => $rec['remarks'] ?? null,
                    'recorded_by' => $request->user()->id,
                    'recorded_at' => $now,
                ]
            );
        }

        return redirect()
            ->route('teacher.attendance.index')
            ->with('success', 'Attendance saved successfully.');
    }

    /**
     * Display attendance history with filters.
     */
    public function history(Request $request): Response
    {
        $school = $request->user()->activeSchool();
        $year = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'status' => ['nullable', 'in:present,absent,late,excused'],
        ]);

        $query = AttendanceRecord::query()
            ->with(['student', 'schoolClass', 'stream', 'recorder'])
            ->where('school_id', $school->id);

        if ($year) {
            $query->where('academic_year_id', $year->id);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('school_class_id', $filters['class_id']);
        }

        if (!empty($filters['stream_id'])) {
            $query->where('stream_id', $filters['stream_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $records = $query
            ->orderBy('date', 'desc')
            ->orderBy('student_id')
            ->paginate(50)
            ->withQueryString();

        $classes = SchoolClass::query()
            ->where('school_id', $school->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $streams = Stream::query()
            ->where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('teacher/attendance/History', [
            'records' => $records,
            'filters' => array_merge([
                'date_from' => null,
                'date_to' => null,
                'class_id' => null,
                'stream_id' => null,
                'status' => null,
            ], $filters),
            'classes' => $classes,
            'streams' => $streams,
        ]);
    }

    /**
     * Export attendance records as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $school = $request->user()->activeSchool();
        $year = AcademicYear::query()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $filters = [
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'class_id' => $request->query('class_id'),
            'stream_id' => $request->query('stream_id'),
            'status' => $request->query('status'),
        ];

        $query = AttendanceRecord::query()
            ->with(['student', 'schoolClass', 'stream'])
            ->where('school_id', $school->id);

        if ($year) {
            $query->where('academic_year_id', $year->id);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        if (!empty($filters['class_id'])) {
            $query->where('school_class_id', $filters['class_id']);
        }

        if (!empty($filters['stream_id'])) {
            $query->where('stream_id', $filters['stream_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $filename = 'attendance_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Date',
                'Class',
                'Stream',
                'Student ID',
                'Student Name',
                'Status',
                'Remarks',
            ]);

            $query->orderBy('date')->chunk(500, function ($chunk) use ($output) {
                foreach ($chunk as $row) {
                    /** @var AttendanceRecord $row */
                    fputcsv($output, [
                        $row->date->format('Y-m-d'),
                        $row->schoolClass?->name ?? 'N/A',
                        $row->stream?->name ?? 'N/A',
                        $row->student?->admission_number ?? 'N/A',
                        $row->student
                            ? $row->student->last_name . ', ' . $row->student->first_name
                            : 'N/A',
                        $row->status,
                        $row->remarks,
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Verify teacher is authorized to take attendance for the given class/stream.
     */
    private function authorizeTeacherForClass($user, SchoolClass $class, ?Stream $stream): void
    {
        $assignmentExists = TeacherAssignment::where('user_id', $user->id)
            ->whereHas('classroom', function ($query) use ($class, $stream) {
                $query->where('school_class_id', $class->id);
                if ($stream) {
                    $query->where('stream_id', $stream->id);
                }
            })
            ->exists();

        if (!$assignmentExists) {
            abort(403, 'You are not authorized to take attendance for this class.');
        }
    }

    /**
     * Validate the attendance date is within the current academic year.
     */
    private function validateAcademicYearContext(int $schoolId, string $date): AcademicYear
    {
        $currentYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        if (!$currentYear) {
            abort(400, 'No active academic year found. Please contact your administrator.');
        }

        if (!Carbon::parse($date)->between($currentYear->starts_at, $currentYear->ends_at)) {
            abort(400, 'Attendance cannot be recorded outside the current academic year dates.');
        }

        return $currentYear;
    }

    /**
     * Validate the attendance date is within an active term.
     */
    private function validateTermContext(int $schoolId, int $academicYearId, string $date): Term
    {
        $dateCarbon = Carbon::parse($date);

        $currentTerm = Term::where('academic_year_id', $academicYearId)
            ->where('school_id', $schoolId)
            ->whereDate('starts_on', '<=', $dateCarbon)
            ->whereDate('ends_on', '>=', $dateCarbon)
            ->first();

        if (!$currentTerm) {
            abort(400, 'Attendance cannot be recorded outside an active term period.');
        }

        return $currentTerm;
    }

    /**
     * Get students enrolled in a specific class/stream for the current academic year.
     */
    private function getEnrolledStudents(int $schoolId, int $academicYearId, int $classId, ?int $streamId)
    {
        return Student::query()
            ->where('school_id', $schoolId)
            ->whereHas('enrollments', function ($query) use ($academicYearId, $classId, $streamId) {
                $query->where('is_active', true)
                    ->whereHas('classroom', function ($classroomQuery) use ($academicYearId, $classId, $streamId) {
                        $classroomQuery
                            ->where('academic_year_id', $academicYearId)
                            ->where('school_class_id', $classId);

                        if ($streamId) {
                            $classroomQuery->where('stream_id', $streamId);
                        }
                    });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }
}
