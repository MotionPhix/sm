<?php

use App\Models\AcademicYear;
use App\Models\AssessmentPlan;
use App\Models\AttendanceRecord;
use App\Models\ClassStreamAssignment;
use App\Models\Grade;
use App\Models\GradeScale;
use App\Models\GradeScaleStep;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentEnrollment;
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

    $this->term = Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'name' => 'Term 1',
        'sequence' => 1,
        'starts_on' => now()->subMonth(),
        'ends_on' => now()->addMonth(),
        'is_active' => true,
    ]);

    Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'name' => 'Term 2',
        'sequence' => 2,
        'starts_on' => now()->addMonths(2),
        'ends_on' => now()->addMonths(4),
        'is_active' => false,
    ]);

    Term::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'name' => 'Term 3',
        'sequence' => 3,
        'starts_on' => now()->addMonths(5),
        'ends_on' => now()->addMonths(7),
        'is_active' => false,
    ]);

    $this->schoolClass = SchoolClass::factory()->create([
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
        'school_class_id' => $this->schoolClass->id,
        'stream_id' => $this->stream->id,
    ]);

    $this->subject = Subject::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'Mathematics',
        'code' => 'MAT',
    ]);

    $this->teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);

    $viewPermission = Permission::firstOrCreate(['name' => 'students.view'], ['label' => 'View Students']);
    $this->teacherRole->permissions()->syncWithoutDetaching([$viewPermission->id]);

    $this->teacher = User::factory()->create([
        'active_school_id' => $this->school->id,
    ]);

    $this->teacher->schools()->attach($this->school->id, [
        'role_id' => $this->teacherRole->id,
        'is_active' => true,
    ]);

    $this->teacherAssignment = TeacherAssignment::create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
    ]);

    $this->students = Student::factory()->count(3)->create([
        'school_id' => $this->school->id,
    ]);

    foreach ($this->students as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'class_stream_assignment_id' => $this->classroom->id,
            'is_active' => true,
        ]);
    }

    app()->instance('currentSchool', $this->school);
});

it('shows class reports index page', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Index')
            ->has('academicYear')
            ->has('classrooms', 1)
            ->has('terms', 3)
        );
});

it('groups classrooms with their subjects on index', function () {
    $subject2 = Subject::factory()->create([
        'school_id' => $this->school->id,
        'name' => 'English',
        'code' => 'ENG',
    ]);

    TeacherAssignment::create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $subject2->id,
    ]);

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Index')
            ->has('classrooms', 1)
            ->where('classrooms.0.subjects', fn ($subjects) => count($subjects) === 2)
        );
});

it('shows empty state when teacher has no assignments', function () {
    $unassignedTeacher = User::factory()->create([
        'active_school_id' => $this->school->id,
    ]);
    $unassignedTeacher->schools()->attach($this->school->id, [
        'role_id' => $this->teacherRole->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($unassignedTeacher)
        ->get(route('teacher.class-reports.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Index')
            ->has('classrooms', 0)
        );
});

it('shows class report with subject analysis and student performance', function () {
    $gradeScale = GradeScale::factory()->create(['school_id' => $this->school->id]);
    GradeScaleStep::factory()->create([
        'grade_scale_id' => $gradeScale->id,
        'min_percent' => 75,
        'max_percent' => 100,
        'grade' => 'A',
        'comment' => 'Excellent',
        'ordering' => 1,
    ]);
    GradeScaleStep::factory()->create([
        'grade_scale_id' => $gradeScale->id,
        'min_percent' => 50,
        'max_percent' => 74,
        'grade' => 'B',
        'comment' => 'Good',
        'ordering' => 2,
    ]);
    GradeScaleStep::factory()->create([
        'grade_scale_id' => $gradeScale->id,
        'min_percent' => 0,
        'max_percent' => 49,
        'grade' => 'F',
        'comment' => 'Fail',
        'ordering' => 3,
    ]);

    $assessmentPlan = AssessmentPlan::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'term_id' => $this->term->id,
        'subject_id' => $this->subject->id,
        'name' => 'Midterm Test',
        'max_score' => 100,
        'weight' => 100,
        'ordering' => 1,
    ]);

    foreach ($this->students as $index => $student) {
        Grade::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'assessment_plan_id' => $assessmentPlan->id,
            'class_stream_assignment_id' => $this->classroom->id,
            'score' => 80 - ($index * 15),
            'entered_by' => $this->teacher->id,
        ]);
    }

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $this->classroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Show')
            ->has('classroom')
            ->has('term')
            ->where('totalStudents', 3)
            ->has('subjectAnalysis', 1)
            ->has('studentPerformance', 3)
            ->has('attendanceStats')
            ->has('gradeScale')
        );
});

it('computes correct subject analysis statistics', function () {
    $assessmentPlan = AssessmentPlan::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'term_id' => $this->term->id,
        'subject_id' => $this->subject->id,
        'name' => 'Final Exam',
        'max_score' => 100,
        'weight' => 100,
        'ordering' => 1,
    ]);

    $scores = [80, 60, 40];
    foreach ($this->students as $index => $student) {
        Grade::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'assessment_plan_id' => $assessmentPlan->id,
            'class_stream_assignment_id' => $this->classroom->id,
            'score' => $scores[$index],
            'entered_by' => $this->teacher->id,
        ]);
    }

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $this->classroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Show')
            ->where('subjectAnalysis.0.average_score', fn ($v) => (float) $v === 60.0)
            ->where('subjectAnalysis.0.pass_rate', fn ($v) => (float) $v === 66.7)
            ->where('subjectAnalysis.0.total_graded', 3)
            ->where('subjectAnalysis.0.total_students', 3)
        );
});

it('ranks students correctly by overall performance', function () {
    $assessmentPlan = AssessmentPlan::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'term_id' => $this->term->id,
        'subject_id' => $this->subject->id,
        'name' => 'Midterm',
        'max_score' => 100,
        'weight' => 100,
        'ordering' => 1,
    ]);

    $scores = [90, 70, 50];
    foreach ($this->students as $index => $student) {
        Grade::factory()->create([
            'school_id' => $this->school->id,
            'student_id' => $student->id,
            'assessment_plan_id' => $assessmentPlan->id,
            'class_stream_assignment_id' => $this->classroom->id,
            'score' => $scores[$index],
            'entered_by' => $this->teacher->id,
        ]);
    }

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $this->classroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('studentPerformance.0.rank', 1)
            ->where('studentPerformance.0.percentage', fn ($v) => (float) $v === 90.0)
            ->where('studentPerformance.1.rank', 2)
            ->where('studentPerformance.1.percentage', fn ($v) => (float) $v === 70.0)
            ->where('studentPerformance.2.rank', 3)
            ->where('studentPerformance.2.percentage', fn ($v) => (float) $v === 50.0)
        );
});

it('includes attendance statistics for the class', function () {
    foreach ($this->students as $student) {
        AttendanceRecord::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now(),
            'school_class_id' => $this->schoolClass->id,
            'stream_id' => $this->stream->id,
            'student_id' => $student->id,
            'status' => 'present',
        ]);
    }

    AttendanceRecord::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => now()->subDay(),
        'school_class_id' => $this->schoolClass->id,
        'stream_id' => $this->stream->id,
        'student_id' => $this->students->first()->id,
        'status' => 'absent',
    ]);

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $this->classroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('attendanceStats.present', 3)
            ->where('attendanceStats.absent', 1)
            ->where('attendanceStats.total_records', 4)
            ->where('attendanceStats.total_days', 2)
            ->where('attendanceStats.rate', fn ($v) => (float) $v === 75.0)
        );
});

it('forbids access to a class the teacher is not assigned to', function () {
    $otherClassroom = ClassStreamAssignment::factory()->create([
        'school_id' => $this->school->id,
        'academic_year_id' => $this->academicYear->id,
    ]);

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $otherClassroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertForbidden();
});

it('returns not found for a classroom from another school', function () {
    $otherSchool = School::factory()->create();
    $otherYear = AcademicYear::factory()->create([
        'school_id' => $otherSchool->id,
        'is_current' => true,
    ]);
    $otherClassroom = ClassStreamAssignment::factory()->create([
        'school_id' => $otherSchool->id,
        'academic_year_id' => $otherYear->id,
    ]);

    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $otherClassroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertNotFound();
});

it('validates required parameters on show', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show'));

    $response->assertRedirect();
});

it('shows report with no grades yet', function () {
    $response = $this->actingAs($this->teacher)
        ->get(route('teacher.class-reports.show', [
            'class_stream_assignment_id' => $this->classroom->id,
            'term_id' => $this->term->id,
        ]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teacher/class-reports/Show')
            ->where('totalStudents', 3)
            ->where('overallAverage', 0)
            ->where('subjectAnalysis.0.total_graded', 0)
        );
});
