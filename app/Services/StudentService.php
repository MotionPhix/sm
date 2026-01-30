<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Applicant;
use App\Models\ClassStreamAssignment;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentService
{
    /**
     * Generate a unique admission number for a school.
     */
    public function generateAdmissionNumber(School $school): string
    {
        $schoolCode = Str::upper(Str::substr($school->code, 0, 5));
        $year = date('Y');
        $prefix = "{$schoolCode}-{$year}-";

        // Find the highest sequence number for this school/year
        $lastStudent = Student::where('school_id', $school->id)
            ->where('admission_number', 'like', "{$prefix}%")
            ->orderBy('admission_number', 'desc')
            ->first();

        if ($lastStudent) {
            $parts = explode('-', $lastStudent->admission_number);
            $sequence = (int) end($parts) + 1;
        } else {
            $sequence = 1;
        }

        $admissionNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Ensure uniqueness (in case of race condition)
        $attempts = 0;
        while (Student::where('admission_number', $admissionNumber)->exists() && $attempts < 10) {
            $sequence++;
            $admissionNumber = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            $attempts++;
        }

        if ($attempts >= 10) {
            throw new \RuntimeException('Unable to generate unique admission number after 10 attempts.');
        }

        return $admissionNumber;
    }

    /**
     * Create a new student directly (not from applicant).
     */
    public function createStudent(int $schoolId, array $data): Student
    {
        $school = School::findOrFail($schoolId);

        return Student::create([
            'school_id' => $schoolId,
            'admission_number' => $this->generateAdmissionNumber($school),
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'admission_date' => $data['admission_date'],
            'national_id' => $data['national_id'] ?? null,
        ]);
    }

    /**
     * Enroll an admitted applicant as a student.
     */
    public function enrollFromApplicant(Applicant $applicant, array $data = []): Student
    {
        $school = $applicant->school;

        return DB::transaction(function () use ($applicant, $school, $data) {
            // Create student from applicant data
            $student = Student::create([
                'school_id' => $school->id,
                'admission_number' => $this->generateAdmissionNumber($school),
                'first_name' => $data['first_name'] ?? $applicant->first_name,
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? $applicant->last_name,
                'gender' => $data['gender'] ?? $applicant->gender,
                'date_of_birth' => $data['date_of_birth'] ?? $applicant->date_of_birth,
                'admission_date' => $data['admission_date'] ?? now()->toDateString(),
                'national_id' => $data['national_id'] ?? $applicant->national_id,
            ]);

            // Update applicant status
            $applicant->update(['status' => 'enrolled']);

            // Link to admission cycle if exists
            if ($applicant->admission_cycle_id) {
                // Could store reference in student if needed
            }

            return $student;
        });
    }

    /**
     * Enroll a student in a class/stream.
     */
    public function enrollStudent(Student $student, array $data): StudentEnrollment
    {
        $school = $student->school;
        $academicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->firstOrFail();

        $classStreamAssignment = ClassStreamAssignment::where('id', $data['class_stream_assignment_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        // Check if already enrolled in an active class for this year
        $existingEnrollment = $student->enrollments()
            ->where('is_active', true)
            ->first();

        return DB::transaction(function () use ($student, $classStreamAssignment, $academicYear, $data, $existingEnrollment) {
            // Deactivate existing enrollment if any
            if ($existingEnrollment) {
                $existingEnrollment->update(['is_active' => false]);
            }

            // Create new enrollment
            $enrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'class_stream_assignment_id' => $classStreamAssignment->id,
                'is_active' => true,
            ]);

            // Update student's current class reference
            $student->update([
                'current_class_stream_assignment_id' => $classStreamAssignment->id,
            ]);

            // Calculate and assign fees (placeholder - can be extended)
            $this->calculateFeeAssignment($student, $classStreamAssignment, $academicYear);

            return $enrollment;
        });
    }

    /**
     * Transfer a student to a new class/stream.
     */
    public function transferStudent(Student $student, array $data): StudentEnrollment
    {
        $school = $student->school;

        $targetAssignment = ClassStreamAssignment::where('id', $data['class_stream_assignment_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        $currentEnrollment = $student->enrollments()
            ->where('is_active', true)
            ->first();

        return DB::transaction(function () use ($student, $targetAssignment, $data, $currentEnrollment) {
            // Deactivate current enrollment
            if ($currentEnrollment) {
                $currentEnrollment->update([
                    'is_active' => false,
                    'transferred_out_at' => $data['transfer_date'],
                    'transfer_reason' => $data['reason'],
                    'notes' => $data['notes'] ?? null,
                ]);
            }

            // Create new enrollment
            $newEnrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'class_stream_assignment_id' => $targetAssignment->id,
                'is_active' => true,
                'transferred_in_at' => $data['transfer_date'],
            ]);

            // Update student's current class reference
            $student->update([
                'current_class_stream_assignment_id' => $targetAssignment->id,
            ]);

            return $newEnrollment;
        });
    }

    /**
     * Withdraw a student from the school.
     */
    public function withdrawStudent(Student $student, array $data): void
    {
        DB::transaction(function () use ($student, $data) {
            // Deactivate any active enrollment
            $student->enrollments()
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'transferred_out_at' => $data['withdrawal_date'],
                    'transfer_reason' => 'Withdrawal: ' . $data['reason'],
                ]);

            // Clear current class reference
            $student->update([
                'current_class_stream_assignment_id' => null,
                'withdrawn_at' => $data['withdrawal_date'],
                'withdrawal_reason' => $data['reason'],
            ]);
        });
    }

    /**
     * Re-enroll a previously withdrawn student.
     */
    public function reenrollStudent(Student $student, array $data): StudentEnrollment
    {
        $school = $student->school;
        $academicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->firstOrFail();

        $classStreamAssignment = ClassStreamAssignment::where('id', $data['class_stream_assignment_id'])
            ->where('school_id', $school->id)
            ->firstOrFail();

        return DB::transaction(function () use ($student, $classStreamAssignment, $data) {
            // Clear withdrawal status
            $student->update([
                'withdrawn_at' => null,
                'withdrawal_reason' => null,
            ]);

            // Enroll in new class
            return $this->enrollStudent($student, [
                'class_stream_assignment_id' => $classStreamAssignment->id,
                'enrollment_date' => $data['enrollment_date'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * Check if a class has available capacity.
     */
    public function validateClassCapacity(ClassStreamAssignment $classStreamAssignment, ?int $additionalStudents = 1): array
    {
        // If no capacity limit is set, always return valid
        if (empty($classStreamAssignment->capacity)) {
            return [
                'valid' => true,
                'current_count' => 0,
                'capacity' => null,
                'available_spots' => null,
            ];
        }

        $currentCount = $classStreamAssignment->studentEnrollments()
            ->where('is_active', true)
            ->count();

        $availableSpots = $classStreamAssignment->capacity - $currentCount;

        return [
            'valid' => $availableSpots >= $additionalStudents,
            'current_count' => $currentCount,
            'capacity' => $classStreamAssignment->capacity,
            'available_spots' => max(0, $availableSpots),
        ];
    }

    /**
     * Calculate and link student to appropriate fee structures.
     */
    public function calculateFeeAssignment(
        Student $student,
        ClassStreamAssignment $classStreamAssignment,
        AcademicYear $academicYear
    ): void {
        // This is a placeholder implementation
        // In a full implementation, this would:
        // 1. Find fee structures for the student's class
        // 2. Create student-fee assignments
        // 3. Handle prorated fees for mid-year enrollments

        // For now, this is just a marker that the fee assignment should happen
        // Actual fee assignment logic would go here
    }

    /**
     * Get a student's enrollment history.
     */
    public function getEnrollmentHistory(Student $student): \Illuminate\Database\Eloquent\Collection
    {
        return $student->enrollments()
            ->with(['classroom.class', 'classroom.stream'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get a student's current enrollment status.
     */
    public function getCurrentStatus(Student $student): array
    {
        $currentEnrollment = $student->enrollments()
            ->where('is_active', true)
            ->with(['classroom.class', 'classroom.stream'])
            ->first();

        return [
            'is_enrolled' => $currentEnrollment !== null,
            'current_class' => $currentEnrollment?->classroom?->display_name ?? null,
            'academic_year' => $currentEnrollment?->classroom?->academicYear?->name ?? null,
            'enrollment_date' => $currentEnrollment?->created_at?->toDateString() ?? null,
            'withdrawn_at' => $student->withdrawn_at?->toDateString(),
            'withdrawal_reason' => $student->withdrawal_reason,
        ];
    }
}
