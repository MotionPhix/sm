<?php

use App\Models\AcademicYear;
use App\Models\GradeScale;
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

    // Create and assign permissions
    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
        Permission::firstOrCreate(['name' => 'settings.view'], ['label' => 'View Settings']),
        Permission::firstOrCreate(['name' => 'grading.configure'], ['label' => 'Configure Grading']),
    ];

    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Create onboarding data to satisfy EnsureOnboardingComplete middleware
    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();

    // Create 3 terms for the academic year
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    SchoolClass::factory()->forSchool($this->school)->create();
    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    app()->instance('currentSchool', $this->school);
});

it('displays grade scales', function () {
    $scale = GradeScale::factory()->forSchool($this->school)->create();
    $scale->steps()->create([
        'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A', 'comment' => 'Excellent', 'ordering' => 1,
    ]);

    actingAs($this->user)
        ->get(route('admin.settings.grade-scales.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/grade-scales/Index')
            ->has('gradeScales', 1)
        );
});

it('can create a grade scale with steps', function () {
    actingAs($this->user)
        ->post(route('admin.settings.grade-scales.store'), [
            'name' => 'Primary Scale',
            'description' => 'Grading scale for primary school',
            'steps' => [
                ['min_percent' => 80, 'max_percent' => 100, 'grade' => 'A', 'comment' => 'Excellent'],
                ['min_percent' => 60, 'max_percent' => 79, 'grade' => 'B', 'comment' => 'Good'],
                ['min_percent' => 40, 'max_percent' => 59, 'grade' => 'C', 'comment' => 'Average'],
                ['min_percent' => 0, 'max_percent' => 39, 'grade' => 'F', 'comment' => 'Fail'],
            ],
        ])
        ->assertRedirect();

    $scale = GradeScale::where('school_id', $this->school->id)->first();
    expect($scale)->not->toBeNull();
    expect($scale->name)->toBe('Primary Scale');
    expect($scale->steps()->count())->toBe(4);
});

it('validates scale name is required', function () {
    actingAs($this->user)
        ->post(route('admin.settings.grade-scales.store'), [
            'name' => '',
            'steps' => [
                ['min_percent' => 80, 'max_percent' => 100, 'grade' => 'A'],
            ],
        ])
        ->assertSessionHasErrors(['name']);
});

it('validates steps are required', function () {
    actingAs($this->user)
        ->post(route('admin.settings.grade-scales.store'), [
            'name' => 'Test Scale',
            'steps' => [],
        ])
        ->assertSessionHasErrors(['steps']);
});

it('validates step grade is required', function () {
    actingAs($this->user)
        ->post(route('admin.settings.grade-scales.store'), [
            'name' => 'Test Scale',
            'steps' => [
                ['min_percent' => 80, 'max_percent' => 100, 'grade' => ''],
            ],
        ])
        ->assertSessionHasErrors(['steps.0.grade']);
});

it('can update a grade scale', function () {
    $scale = GradeScale::factory()->forSchool($this->school)->create(['name' => 'Old Name']);
    $scale->steps()->create([
        'min_percent' => 80, 'max_percent' => 100, 'grade' => 'A', 'ordering' => 1,
    ]);

    actingAs($this->user)
        ->put(route('admin.settings.grade-scales.update', $scale), [
            'name' => 'New Name',
            'description' => 'Updated description',
            'steps' => [
                ['min_percent' => 70, 'max_percent' => 100, 'grade' => 'A', 'comment' => 'Great'],
                ['min_percent' => 0, 'max_percent' => 69, 'grade' => 'B', 'comment' => 'Good'],
            ],
        ])
        ->assertRedirect();

    expect($scale->fresh()->name)->toBe('New Name');
    expect($scale->steps()->count())->toBe(2);
});

it('can delete a grade scale', function () {
    $scale = GradeScale::factory()->forSchool($this->school)->create();

    actingAs($this->user)
        ->delete(route('admin.settings.grade-scales.destroy', $scale))
        ->assertRedirect();

    expect(GradeScale::find($scale->id))->toBeNull();
});

it('validates unique scale name per school', function () {
    GradeScale::factory()->forSchool($this->school)->create(['name' => 'Primary Scale']);

    actingAs($this->user)
        ->post(route('admin.settings.grade-scales.store'), [
            'name' => 'Primary Scale',
            'steps' => [
                ['min_percent' => 80, 'max_percent' => 100, 'grade' => 'A'],
            ],
        ])
        ->assertSessionHasErrors(['name']);
});
