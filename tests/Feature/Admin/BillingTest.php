<?php

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    $adminRole = Role::factory()->create(['name' => 'admin', 'label' => 'Administrator']);
    $this->admin = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->admin->schools()->attach($this->school->id, ['role_id' => $adminRole->id]);

    $permissions = [
        Permission::firstOrCreate(['name' => 'dashboard.view'], ['label' => 'View Dashboard']),
    ];

    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Onboarding data
    $academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);
    SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    app()->instance('currentSchool', $this->school);
});

it('displays the billing page for admin users', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.billing.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/billing/Index')
        ->has('school')
        ->where('school.name', $this->school->name)
    );
});

it('redirects guests to the login page', function () {
    $response = $this->get(route('admin.billing.index'));

    $response->assertRedirect(route('login'));
});

it('denies access to non-admin users', function () {
    $teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);
    $teacher = User::factory()->create(['active_school_id' => $this->school->id]);
    $teacher->schools()->attach($this->school->id, ['role_id' => $teacherRole->id]);

    $response = $this->actingAs($teacher)->get(route('admin.billing.index'));

    $response->assertForbidden();
});
