<?php

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    $registrarRole = Role::factory()->create(['name' => 'registrar', 'label' => 'Registrar']);
    $this->registrar = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->registrar->schools()->attach($this->school->id, ['role_id' => $registrarRole->id]);

    $permissions = [
        Permission::firstOrCreate(['name' => 'students.view'], ['label' => 'View Students']),
        Permission::firstOrCreate(['name' => 'students.transfer'], ['label' => 'Transfer Students']),
    ];

    $registrarRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);

    $this->schoolClass = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    $this->streamA = Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    $this->streamB = Stream::factory()->forSchool($this->school)->create(['name' => 'B']);

    $this->classStreamAssignmentA = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($academicYear)
        ->forClass($this->schoolClass)
        ->forStream($this->streamA)
        ->create();

    $this->classStreamAssignmentB = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($academicYear)
        ->forClass($this->schoolClass)
        ->forStream($this->streamB)
        ->create();

    $this->student = Student::factory()->forSchool($this->school)->create();

    StudentEnrollment::factory()
        ->forStudent($this->student)
        ->forClassroom($this->classStreamAssignmentA)
        ->create(['is_active' => true]);
});

it('can transfer student between classes', function () {
    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => $this->classStreamAssignmentB->id,
            'transfer_date' => '2024-02-01',
            'reason' => 'Class size reduction',
        ]);

    $response->assertRedirect();

    // Old enrollment should be deactivated
    $oldEnrollment = $this->student->enrollments()
        ->where('class_stream_assignment_id', $this->classStreamAssignmentA->id)
        ->first();
    expect($oldEnrollment->is_active)->toBeFalse();

    // New enrollment should be created and active
    $this->assertDatabaseHas('student_enrollments', [
        'student_id' => $this->student->id,
        'class_stream_assignment_id' => $this->classStreamAssignmentB->id,
        'is_active' => true,
        'transfer_reason' => 'Class size reduction',
    ]);

    // Student should have updated current assignment
    $this->student->refresh();
    expect($this->student->current_class_stream_assignment_id)->toBe($this->classStreamAssignmentB->id);
});

it('requires reason for transfer', function () {
    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => $this->classStreamAssignmentB->id,
            'transfer_date' => '2024-02-01',
        ]);

    $response->assertSessionHasErrors(['reason']);
});

it('cannot transfer to same class', function () {
    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => $this->classStreamAssignmentA->id,
            'transfer_date' => '2024-02-01',
            'reason' => 'Test transfer',
        ]);

    $response->assertSessionHasErrors(['target_class_stream_assignment_id']);
});

it('validates target class exists', function () {
    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => 'non-existent-id',
            'transfer_date' => '2024-02-01',
            'reason' => 'Test transfer',
        ]);

    $response->assertSessionHasErrors(['target_class_stream_assignment_id']);
});

it('creates transfer history record', function () {
    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => $this->classStreamAssignmentB->id,
            'transfer_date' => '2024-02-01',
            'reason' => 'Academic reasons',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('student_enrollments', [
        'student_id' => $this->student->id,
        'class_stream_assignment_id' => $this->classStreamAssignmentB->id,
        'is_active' => true,
        'transfer_reason' => 'Academic reasons',
    ]);
});

it('can transfer student between different classes', function () {
    $class2 = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 2']);

    $classStreamAssignmentC = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($this->classStreamAssignmentB->academic_year)
        ->forClass($class2)
        ->forStream($this->streamA)
        ->create();

    $response = $this->actingAs($this->registrar)
        ->post("/registrar/students/{$this->student->id}/transfer", [
            'target_class_stream_assignment_id' => $classStreamAssignmentC->id,
            'transfer_date' => '2024-02-01',
            'reason' => 'Promoted to next class',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('student_enrollments', [
        'student_id' => $this->student->id,
        'class_stream_assignment_id' => $classStreamAssignmentC->id,
        'is_active' => true,
    ]);

    $this->student->refresh();
    expect($this->student->current_class_stream_assignment_id)->toBe($classStreamAssignmentC->id);
});
