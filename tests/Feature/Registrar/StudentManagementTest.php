<?php

use App\Models\AcademicYear;
use App\Models\Applicant;
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
use Inertia\Inertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    // Create a registrar role
    $registrarRole = Role::factory()->create(['name' => 'registrar', 'label' => 'Registrar']);
    $this->registrar = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->registrar->schools()->attach($this->school->id, ['role_id' => $registrarRole->id]);

    // Create student-related permissions
    $permissions = [
        Permission::firstOrCreate(['name' => 'students.view'], ['label' => 'View Students']),
        Permission::firstOrCreate(['name' => 'students.create'], ['label' => 'Create Students']),
        Permission::firstOrCreate(['name' => 'students.edit'], ['label' => 'Edit Students']),
        Permission::firstOrCreate(['name' => 'students.delete'], ['label' => 'Delete Students']),
        Permission::firstOrCreate(['name' => 'students.enroll'], ['label' => 'Enroll Students']),
        Permission::firstOrCreate(['name' => 'students.transfer'], ['label' => 'Transfer Students']),
    ];

    $registrarRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Create onboarding data
    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);

    $this->schoolClass = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    $this->stream = Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    $this->classStreamAssignment = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($academicYear)
        ->forClass($this->schoolClass)
        ->forStream($this->stream)
        ->create();
});

it('can list students', function () {
    Student::factory()->forSchool($this->school)->count(3)->create();

    $response = $this->actingAs($this->registrar)
        ->get('/registrar/students');

    $response->assertOk();
    $response->assertInertia(fn (Inertia\Inertia $page) => $page
        ->component('registrar/students/Index')
        ->has('students.data', 3)
    );
});

it('can show student details', function () {
    $student = Student::factory()->forSchool($this->school)->create();

    $response = $this->actingAs($this->registrar)
        ->get("/registrar/students/{$student->id}");

    $response->assertOk();
    $response->assertInertia(fn (Inertia\Inertia $page) => $page
        ->component('registrar/students/Show')
        ->where('student.id', $student->id)
    );
});

it('can create a student directly', function () {
    $response = $this->actingAs($this->registrar)
        ->post('/registrar/students', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'date_of_birth' => '2010-01-15',
            'admission_date' => '2024-01-15',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('students', [
        'school_id' => $this->school->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'gender' => 'male',
    ]);

    $student = Student::where('first_name', 'John')->first();
    expect($student->admission_number)->not->toBeNull();
});

it('can create a student from an admitted applicant', function () {
    $applicant = Applicant::factory()
        ->forSchool($this->school)
        ->admitted()
        ->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

    $response = $this->actingAs($this->registrar)
        ->post('/registrar/students', [
            'applicant_id' => $applicant->id,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('students', [
        'school_id' => $this->school->id,
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    // Applicant status should be updated
    $applicant->refresh();
    expect($applicant->status)->toBe('enrolled');
});

it('generates unique admission numbers', function () {
    $student1 = Student::factory()->forSchool($this->school)->create();
    $student2 = Student::factory()->forSchool($this->school)->create();

    expect($student1->admission_number)->not->toEqual($student2->admission_number);
});

it('can update student information', function () {
    $student = Student::factory()->forSchool($this->school)->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $response = $this->actingAs($this->registrar)
        ->put("/registrar/students/{$student->id}", [
            'first_name' => 'Johnny',
            'last_name' => 'Doe',
            'gender' => $student->gender,
            'date_of_birth' => $student->date_of_birth,
            'admission_date' => $student->admission_date,
        ]);

    $response->assertRedirect();

    $student->refresh();
    expect($student->first_name)->toBe('Johnny');
});

it('can soft delete a student', function () {
    $student = Student::factory()->forSchool($this->school)->create();

    $response = $this->actingAs($this->registrar)
        ->delete("/registrar/students/{$student->id}");

    $response->assertRedirect('/registrar/students');

    $this->assertSoftDeleted('students', [
        'id' => $student->id,
    ]);
});

it('filters students by class', function () {
    $class1 = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    $class2 = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 2']);

    $streamA = Stream::factory()->forSchool($this->school)->create(['name' => 'A']);

    $assignment1 = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forClass($class1)
        ->forStream($streamA)
        ->create();

    $assignment2 = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forClass($class2)
        ->forStream($streamA)
        ->create();

    $student1 = Student::factory()->forSchool($this->school)->create();
    $student2 = Student::factory()->forSchool($this->school)->create();
    $student3 = Student::factory()->forSchool($this->school)->create();

    // Enroll student1 and student2 in class1
    StudentEnrollment::factory()->forStudent($student1)->forClassroom($assignment1)->create(['is_active' => true]);
    StudentEnrollment::factory()->forStudent($student2)->forClassroom($assignment1)->create(['is_active' => true]);
    // Enroll student3 in class2
    StudentEnrollment::factory()->forStudent($student3)->forClassroom($assignment2)->create(['is_active' => true]);

    $response = $this->actingAs($this->registrar)
        ->get('/registrar/students', ['class_id' => $class1->id]);

    $response->assertOk();
    $response->assertInertia(fn (Inertia\Inertia $page) => $page
        ->has('students.data', 2)
    );
});

it('searches students by name or admission number', function () {
    $student1 = Student::factory()->forSchool($this->school)->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'admission_number' => 'ABC-001',
    ]);
    $student2 = Student::factory()->forSchool($this->school)->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'admission_number' => 'XYZ-999',
    ]);

    $response = $this->actingAs($this->registrar)
        ->get('/registrar/students', ['search' => 'John']);

    $response->assertOk();
    $response->assertInertia(fn (Inertia\Inertia $page) => $page
        ->has('students.data', 1)
        ->where('students.data.0.first_name', 'John')
    );
});
