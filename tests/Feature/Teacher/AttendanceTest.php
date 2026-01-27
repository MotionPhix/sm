<?php

use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\ClassStreamAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create school
    $this->school = School::factory()->create();

    // Create academic year
    $this->academicYear = AcademicYear::factory()->create([
        'school_id' => $this->school->id,
        'is_current' => true,
        'starts_at' => now()->startOfYear(),
        'ends_at' => now()->endOfYear(),
    ]);

    // Create term within academic year
    $this->term = Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'starts_on' => now()->subMonth(),
        'ends_on' => now()->addMonth(),
        'is_active' => true,
    ]);

    // Create class and stream
    $this->class = SchoolClass::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Form 1',
    ]);

    $this->stream = Stream::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'A',
    ]);

    // Create classroom assignment
    $this->classroom = ClassStreamAssignment::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->class->id,
        'stream_id' => $this->stream->id,
    ]);

    // Create teacher role with permissions
    $this->teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);

    // Create permissions
    $this->viewPermission = Permission::firstOrCreate(['name' => 'attendance.view'], ['label' => 'View Attendance']);
    $this->recordPermission = Permission::firstOrCreate(['name' => 'attendance.record'], ['label' => 'Record Attendance']);
    $this->exportPermission = Permission::firstOrCreate(['name' => 'attendance.export'], ['label' => 'Export Attendance']);

    $this->teacherRole->permissions()->syncWithoutDetaching([
        $this->viewPermission->id,
        $this->recordPermission->id,
        $this->exportPermission->id,
    ]);

    // Create teacher user
    $this->teacher = User::factory()->create([
        'active_school_id' => $this->school->id,
    ]);

    // Attach teacher to school with role
    $this->teacher->schools()->attach($this->school->id, ['role_id' => $this->teacherRole->id, 'is_active' => true]);

    // Assign teacher to classroom
    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
    ]);

    // Create students
    $this->students = Student::factory()->count(3)->create([
        'school_id' => $this->school->id,
    ]);

    // Enroll students
    foreach ($this->students as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_stream_assignment_id' => $this->classroom->id,
            'is_active' => true,
        ]);
    }

    // Bind current school for tenant scoping
    app()->instance('currentSchool', $this->school);
});

it('shows attendance index page for authorized teacher', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.attendance.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/attendance/Index')
            ->has('classes')
            ->has('streams')
            ->has('academicYear')
        );
});

it('returns roster for valid class and date', function () {
    $response = $this->actingAs($this->teacher)
        ->getJson(route('teacher.attendance.roster', [
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'date' => now()->toDateString(),
        ]));

    $response->assertOk()
        ->assertJsonStructure([
            'students',
            'existing',
        ])
        ->assertJsonCount(3, 'students');
});

it('rejects roster request for unauthorized class', function () {
    // Create another class without teacher assignment
    $otherClass = SchoolClass::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Form 2',
    ]);

    $response = $this->actingAs($this->teacher)
        ->getJson(route('teacher.attendance.roster', [
            'class_id' => $otherClass->id,
            'date' => now()->toDateString(),
        ]));

    $response->assertForbidden();
});

it('rejects roster request for date outside academic year', function () {
    $response = $this->actingAs($this->teacher)
        ->getJson(route('teacher.attendance.roster', [
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'date' => now()->subYears(2)->toDateString(),
        ]));

    $response->assertStatus(400);
});

it('rejects roster request for date outside term', function () {
    // Update term to exclude today
    $this->term->update([
        'starts_on' => now()->addMonths(2),
        'ends_on' => now()->addMonths(4),
    ]);

    $response = $this->actingAs($this->teacher)
        ->getJson(route('teacher.attendance.roster', [
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'date' => now()->toDateString(),
        ]));

    $response->assertStatus(400);
});

it('shows attendance record page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.attendance.record', [
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'date' => now()->toDateString(),
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/attendance/Record')
            ->has('students', 3)
            ->where('classId', $this->class->id)
            ->where('className', $this->class->name)
        );
});

it('stores attendance records successfully', function () {
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'present',
        'remarks' => null,
    ])->toArray();

    $response = $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    $response->assertRedirect(route('teacher.attendance.index'));

    // Verify records were created
    expect(AttendanceRecord::count())->toBe(3);

    foreach ($this->students as $student) {
        $this->assertDatabaseHas('attendance_records', [
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $this->teacher->id,
        ]);
    }
});

it('updates existing attendance records', function () {
    // Create existing records
    foreach ($this->students as $student) {
        AttendanceRecord::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->toDateString(),
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $this->teacher->id,
            'recorded_at' => now()->subHour(),
        ]);
    }

    // Update to absent
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'absent',
        'remarks' => 'Updated',
    ])->toArray();

    $response = $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    $response->assertRedirect(route('teacher.attendance.index'));

    // Should still have 3 records (updated, not duplicated)
    expect(AttendanceRecord::count())->toBe(3);

    foreach ($this->students as $student) {
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'status' => 'absent',
            'remarks' => 'Updated',
        ]);
    }
});

it('prevents duplicate attendance records', function () {
    // Store initial records
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'present',
        'remarks' => null,
    ])->toArray();

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    // Submit again with different status
    $records = $this->students->map(fn ($student) => [
        'student_id' => $student->id,
        'status' => 'late',
        'remarks' => 'Re-submitted',
    ])->toArray();

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    // Should still have only 3 records (no duplicates)
    expect(AttendanceRecord::count())->toBe(3);

    // All should now be 'late'
    foreach ($this->students as $student) {
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $student->id,
            'status' => 'late',
        ]);
    }
});

it('validates attendance status values', function () {
    $records = [[
        'student_id' => $this->students->first()->id,
        'status' => 'invalid_status',
        'remarks' => null,
    ]];

    $response = $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    $response->assertSessionHasErrors('records.0.status');
});

it('shows attendance history', function () {
    // Create some attendance records
    foreach ($this->students as $student) {
        AttendanceRecord::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->toDateString(),
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $this->teacher->id,
        ]);
    }

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.attendance.history'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/attendance/History')
            ->has('records.data', 3)
            ->has('classes')
            ->has('streams')
        );
});

it('filters attendance history by date range', function () {
    // Create records on different dates
    AttendanceRecord::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => now()->subDays(5)->toDateString(),
        'school_class_id' => $this->class->id,
        'student_id' => $this->students->first()->id,
        'status' => 'present',
        'recorded_by' => $this->teacher->id,
    ]);

    AttendanceRecord::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => now()->toDateString(),
        'school_class_id' => $this->class->id,
        'student_id' => $this->students->first()->id,
        'status' => 'absent',
        'recorded_by' => $this->teacher->id,
    ]);

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.attendance.history', [
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('records.data', 1) // Only today's record
        );
});

it('exports attendance as CSV', function () {
    // Create attendance records
    foreach ($this->students as $student) {
        AttendanceRecord::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->toDateString(),
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'student_id' => $student->id,
            'status' => 'present',
            'recorded_by' => $this->teacher->id,
        ]);
    }

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.attendance.export'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

it('records metadata for attendance submissions', function () {
    $records = [[
        'student_id' => $this->students->first()->id,
        'status' => 'present',
        'remarks' => 'Test remark',
    ]];

    $this->actingAs($this->teacher)
        ->post(route('teacher.attendance.store'), [
            'date' => now()->toDateString(),
            'class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'records' => $records,
        ]);

    $record = AttendanceRecord::first();

    expect($record->recorded_by)->toBe($this->teacher->id)
        ->and($record->recorded_at)->not->toBeNull();
});
