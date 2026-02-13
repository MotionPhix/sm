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

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    $adminRole = Role::factory()->create(['name' => 'admin', 'label' => 'Administrator']);
    $this->admin = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->admin->schools()->attach($this->school->id, ['role_id' => $adminRole->id]);

    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'settings.view'], ['label' => 'View Settings']),
    ];
    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Onboarding requirements
    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    $this->class = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1', 'order' => 1]);
    $this->stream = Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    $this->subject = Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    $this->classroom = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($this->academicYear)
        ->forClass($this->class)
        ->forStream($this->stream)
        ->create();

    // Teacher user
    $teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);
    $this->teacher = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->teacher->schools()->attach($this->school->id, ['role_id' => $teacherRole->id, 'is_active' => true]);

    app()->instance('currentSchool', $this->school);
});

it('displays the teacher assignments index page', function () {
    TeacherAssignment::factory()
        ->forTeacher($this->teacher)
        ->forClassroom($this->classroom)
        ->forSubject($this->subject)
        ->create();

    actingAs($this->admin)
        ->get(route('admin.settings.teacher-assignments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/teacher-assignments/Index')
            ->has('assignments', 1)
        );
});

it('displays empty state when no assignments exist', function () {
    actingAs($this->admin)
        ->get(route('admin.settings.teacher-assignments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/teacher-assignments/Index')
            ->has('assignments', 0)
        );
});

it('displays the create teacher assignment page', function () {
    actingAs($this->admin)
        ->get(route('admin.settings.teacher-assignments.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/teacher-assignments/Create')
            ->has('teachers')
            ->has('classrooms')
            ->has('subjects')
        );
});

it('can create a teacher assignment', function () {
    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'subject_ids' => [$this->subject->id],
        ])
        ->assertRedirect(route('admin.settings.teacher-assignments.index'));

    expect(TeacherAssignment::count())->toBe(1);

    $this->assertDatabaseHas('teacher_assignments', [
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
    ]);
});

it('can create multiple assignments for multiple subjects', function () {
    $subject2 = Subject::factory()->forSchool($this->school)->create(['name' => 'English', 'code' => 'ENG']);
    $subject3 = Subject::factory()->forSchool($this->school)->create(['name' => 'Science', 'code' => 'SCI']);

    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'subject_ids' => [$this->subject->id, $subject2->id, $subject3->id],
        ])
        ->assertRedirect(route('admin.settings.teacher-assignments.index'));

    expect(TeacherAssignment::count())->toBe(3);

    $this->assertDatabaseHas('teacher_assignments', [
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
    ]);
    $this->assertDatabaseHas('teacher_assignments', [
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $subject2->id,
    ]);
    $this->assertDatabaseHas('teacher_assignments', [
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $subject3->id,
    ]);
});

it('skips duplicate assignments when creating', function () {
    // Pre-existing assignment
    TeacherAssignment::create([
        'user_id' => $this->teacher->id,
        'class_stream_assignment_id' => $this->classroom->id,
        'subject_id' => $this->subject->id,
    ]);

    $subject2 = Subject::factory()->forSchool($this->school)->create(['name' => 'English', 'code' => 'ENG']);

    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'subject_ids' => [$this->subject->id, $subject2->id],
        ])
        ->assertRedirect(route('admin.settings.teacher-assignments.index'))
        ->assertSessionHas('success');

    expect(TeacherAssignment::count())->toBe(2);
});

it('validates required fields when creating', function () {
    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [])
        ->assertSessionHasErrors(['user_id', 'school_class_id', 'stream_id', 'subject_ids']);
});

it('validates foreign keys exist when creating', function () {
    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => 99999,
            'school_class_id' => 99999,
            'stream_id' => 99999,
            'subject_ids' => [99999],
        ])
        ->assertSessionHasErrors(['user_id', 'school_class_id', 'stream_id', 'subject_ids.0']);
});

it('validates subject_ids must be an array', function () {
    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'subject_ids' => 'not-an-array',
        ])
        ->assertSessionHasErrors(['subject_ids']);
});

it('returns 404 when class and stream combination does not exist', function () {
    $otherStream = Stream::factory()->forSchool($this->school)->create(['name' => 'Z']);

    actingAs($this->admin)
        ->post(route('admin.settings.teacher-assignments.store'), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $otherStream->id,
            'subject_ids' => [$this->subject->id],
        ])
        ->assertNotFound();
});

it('displays the edit teacher assignment page', function () {
    $assignment = TeacherAssignment::factory()
        ->forTeacher($this->teacher)
        ->forClassroom($this->classroom)
        ->forSubject($this->subject)
        ->create();

    actingAs($this->admin)
        ->get(route('admin.settings.teacher-assignments.edit', $assignment))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/teacher-assignments/Edit')
            ->where('assignment.id', $assignment->id)
            ->has('teachers')
            ->has('classrooms')
            ->has('subjects')
        );
});

it('can update a teacher assignment', function () {
    $assignment = TeacherAssignment::factory()
        ->forTeacher($this->teacher)
        ->forClassroom($this->classroom)
        ->forSubject($this->subject)
        ->create();

    $newSubject = Subject::factory()->forSchool($this->school)->create(['name' => 'English', 'code' => 'ENG']);

    actingAs($this->admin)
        ->put(route('admin.settings.teacher-assignments.update', $assignment), [
            'user_id' => $this->teacher->id,
            'school_class_id' => $this->class->id,
            'stream_id' => $this->stream->id,
            'subject_id' => $newSubject->id,
        ])
        ->assertRedirect(route('admin.settings.teacher-assignments.index'));

    expect($assignment->fresh()->subject_id)->toBe($newSubject->id);
});

it('can delete a teacher assignment', function () {
    $assignment = TeacherAssignment::factory()
        ->forTeacher($this->teacher)
        ->forClassroom($this->classroom)
        ->forSubject($this->subject)
        ->create();

    actingAs($this->admin)
        ->delete(route('admin.settings.teacher-assignments.destroy', $assignment))
        ->assertRedirect(route('admin.settings.teacher-assignments.index'));

    expect(TeacherAssignment::find($assignment->id))->toBeNull();
});

it('prevents non-admin users from accessing teacher assignments', function () {
    $teacherRole = Role::where('name', 'teacher')->first();
    $teacherUser = User::factory()->create(['active_school_id' => $this->school->id]);
    $teacherUser->schools()->attach($this->school->id, ['role_id' => $teacherRole->id]);

    actingAs($teacherUser)
        ->get(route('admin.settings.teacher-assignments.index'))
        ->assertForbidden();
});
