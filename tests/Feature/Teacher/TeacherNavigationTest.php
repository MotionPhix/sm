<?php

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    $this->academicYear = AcademicYear::factory()->create([
        'school_id' => $this->school->id,
        'is_current' => true,
        'starts_at' => now()->startOfYear(),
        'ends_at' => now()->endOfYear(),
    ]);

    Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'starts_on' => now()->subMonth(),
        'ends_on' => now()->addMonth(),
        'is_active' => true,
        'sequence' => 1,
        'name' => 'Term 1',
    ]);
    Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'starts_on' => now()->addMonths(2),
        'ends_on' => now()->addMonths(4),
        'sequence' => 2,
        'name' => 'Term 2',
    ]);
    Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'starts_on' => now()->addMonths(5),
        'ends_on' => now()->addMonths(7),
        'sequence' => 3,
        'name' => 'Term 3',
    ]);

    $this->class = SchoolClass::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Form 1',
    ]);

    $this->stream = Stream::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'A',
    ]);

    $this->classroom = ClassStreamAssignment::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->class->id,
        'stream_id' => $this->stream->id,
    ]);

    $this->subject = Subject::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Mathematics',
        'code' => 'MATH',
    ]);

    $this->teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);

    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'students.view'], ['label' => 'View Students']),
        Permission::firstOrCreate(['name' => 'attendance.view'], ['label' => 'View Attendance']),
        Permission::firstOrCreate(['name' => 'attendance.record'], ['label' => 'Record Attendance']),
        Permission::firstOrCreate(['name' => 'exams.view'], ['label' => 'View Exams']),
        Permission::firstOrCreate(['name' => 'exams.enter-marks'], ['label' => 'Enter Marks']),
    ];

    $this->teacherRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    $this->teacher = User::factory()->create([
        'active_school_id' => $this->school->id,
    ]);

    $this->teacher->schools()->attach($this->school->id, ['role_id' => $this->teacherRole->id, 'is_active' => true]);

    TeacherAssignment::factory()->create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
    ]);

    app()->instance('currentSchool', $this->school);
});

it('allows teacher to access the students page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.students.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/students/Index')
            ->has('students')
            ->has('academicYear')
            ->has('totalStudents')
        );
});

it('allows teacher to access the exam marking page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.exam-marking.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/exam-marking/Index')
            ->has('academicYear')
            ->has('assignments')
        );
});

it('allows teacher to access the class reports page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Index')
            ->has('academicYear')
            ->has('classrooms')
            ->has('terms')
        );
});

it('allows teacher to access the announcements page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.announcements.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/announcements/Index')
            ->has('school')
        );
});

it('redirects guest to login for teacher pages', function () {
    $this->get(route('teacher.students.index'))->assertRedirect('/login');
    $this->get(route('teacher.exam-marking.index'))->assertRedirect('/login');
    $this->get(route('teacher.class-reports.index'))->assertRedirect('/login');
    $this->get(route('teacher.announcements.index'))->assertRedirect('/login');
});

it('forbids non-teacher role from accessing teacher pages', function () {
    $bursarRole = Role::factory()->create(['name' => 'bursar', 'label' => 'Bursar']);
    $bursar = User::factory()->create(['active_school_id' => $this->school->id]);
    $bursar->schools()->attach($this->school->id, ['role_id' => $bursarRole->id, 'is_active' => true]);

    $this->actingAs($bursar)
        ->get(route('teacher.students.index'))
        ->assertForbidden();
});
