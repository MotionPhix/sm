<?php

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    // Create an admin role with specific name to match route middleware
    $adminRole = Role::factory()->create(['name' => 'admin', 'label' => 'Administrator']);
    $this->user = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->user->schools()->attach($this->school->id, ['role_id' => $adminRole->id]);

    // Create permissions
    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'settings.view'], ['label' => 'View Settings']),
    ];
    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Create onboarding data to satisfy middleware
    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    // Create a class to satisfy onboarding middleware
    SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1', 'order' => 1]);

    app()->instance('currentSchool', $this->school);
});

it('displays the classes index page', function () {
    // 3 more classes (1 already created in beforeEach)
    SchoolClass::factory()
        ->count(3)
        ->forSchool($this->school)
        ->create();

    actingAs($this->user)
        ->get(route('admin.settings.classes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/classes/Index')
            ->has('classes', 4)
        );
});

it('displays the create class page with next order number', function () {
    SchoolClass::factory()
        ->forSchool($this->school)
        ->create(['order' => 5]);

    actingAs($this->user)
        ->get(route('admin.settings.classes.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/classes/Create')
            ->where('nextOrder', 6)
        );
});

it('can create a new class', function () {
    actingAs($this->user)
        ->post(route('admin.settings.classes.store'), [
            'name' => 'Form 2',
            'order' => 2,
        ])
        ->assertRedirect(route('admin.settings.classes.index'));

    // 1 from beforeEach + 1 new = 2
    expect(SchoolClass::where('school_id', $this->school->id)->count())->toBe(2);
    expect(SchoolClass::where('name', 'Form 2')->first()->order)->toBe(2);
});

it('validates class name is required', function () {
    actingAs($this->user)
        ->post(route('admin.settings.classes.store'), [
            'name' => '',
            'order' => 1,
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates class name is unique within school', function () {
    // 'Form 1' already exists from beforeEach
    actingAs($this->user)
        ->post(route('admin.settings.classes.store'), [
            'name' => 'Form 1',
            'order' => 2,
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates order number is unique within school', function () {
    // Order 1 already exists from beforeEach
    actingAs($this->user)
        ->post(route('admin.settings.classes.store'), [
            'name' => 'Form 2',
            'order' => 1,
        ])
        ->assertSessionHasErrors(['order']);
});

it('allows same class name in different schools', function () {
    $otherSchool = School::factory()->create();

    SchoolClass::factory()
        ->forSchool($otherSchool)
        ->create(['name' => 'Form 2']);

    actingAs($this->user)
        ->post(route('admin.settings.classes.store'), [
            'name' => 'Form 2',
            'order' => 2,
        ])
        ->assertRedirect();

    // Use withoutGlobalScopes to count across all schools
    expect(SchoolClass::withoutGlobalScopes()->where('name', 'Form 2')->count())->toBe(2);
});

it('displays the edit class page', function () {
    $class = SchoolClass::factory()
        ->forSchool($this->school)
        ->create();

    actingAs($this->user)
        ->get(route('admin.settings.classes.edit', $class))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/classes/Edit')
            ->where('schoolClass.id', $class->id)
            ->where('schoolClass.name', $class->name)
        );
});

it('can update a class', function () {
    $class = SchoolClass::factory()
        ->forSchool($this->school)
        ->create(['name' => 'Form 2', 'order' => 2]);

    actingAs($this->user)
        ->put(route('admin.settings.classes.update', $class), [
            'name' => 'Form 2 Updated',
            'order' => 3,
        ])
        ->assertRedirect(route('admin.settings.classes.index'));

    expect($class->fresh()->name)->toBe('Form 2 Updated');
    expect($class->fresh()->order)->toBe(3);
});

it('can delete a class without stream assignments', function () {
    $class = SchoolClass::factory()
        ->forSchool($this->school)
        ->create();

    actingAs($this->user)
        ->delete(route('admin.settings.classes.destroy', $class))
        ->assertRedirect(route('admin.settings.classes.index'));

    expect(SchoolClass::find($class->id))->toBeNull();
});

it('cannot delete a class with assigned streams', function () {
    $academicYear = AcademicYear::factory()->forSchool($this->school)->create();
    $class = SchoolClass::factory()->forSchool($this->school)->create();
    $stream = Stream::factory()->forSchool($this->school)->create();

    ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($academicYear)
        ->forClass($class)
        ->forStream($stream)
        ->create();

    actingAs($this->user)
        ->delete(route('admin.settings.classes.destroy', $class))
        ->assertSessionHasErrors();

    expect(SchoolClass::find($class->id))->not->toBeNull();
});
