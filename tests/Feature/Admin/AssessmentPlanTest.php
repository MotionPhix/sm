<?php

use App\Models\AcademicYear;
use App\Models\AssessmentPlan;
use App\Models\Grade;
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
        Permission::firstOrCreate(['name' => 'exams.create'], ['label' => 'Create Exams']),
        Permission::firstOrCreate(['name' => 'exams.edit'], ['label' => 'Edit Exams']),
        Permission::firstOrCreate(['name' => 'exams.delete'], ['label' => 'Delete Exams']),
    ];
    $adminRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    // Create onboarding data to satisfy middleware
    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    $this->term = Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    Stream::factory()->forSchool($this->school)->create(['name' => 'A']);
    $this->subject = Subject::factory()->forSchool($this->school)->create(['name' => 'Mathematics', 'code' => 'MATH']);

    // Create a class to satisfy onboarding middleware
    SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1', 'order' => 1]);

    app()->instance('currentSchool', $this->school);
});

it('displays assessment plans for the school', function () {
    AssessmentPlan::factory()
        ->count(3)
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create();

    actingAs($this->user)
        ->get(route('admin.settings.assessment-plans.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/settings/assessment-plans/Index')
            ->has('assessmentPlans.data', 3)
        );
});

it('does not show plans from other schools', function () {
    $otherSchool = School::factory()->create();
    AssessmentPlan::factory()->forSchool($otherSchool)->create();

    AssessmentPlan::factory()
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create();

    actingAs($this->user)
        ->get(route('admin.settings.assessment-plans.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('assessmentPlans.data', 1)
        );
});

it('can create an assessment plan', function () {
    actingAs($this->user)
        ->post(route('admin.settings.assessment-plans.store'), [
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'name' => 'Midterm Test',
            'ordering' => 1,
            'max_score' => 100,
            'weight' => 50,
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(AssessmentPlan::where('school_id', $this->school->id)->count())->toBe(1);
    expect(AssessmentPlan::first()->name)->toBe('Midterm Test');
});

it('validates required fields', function () {
    actingAs($this->user)
        ->post(route('admin.settings.assessment-plans.store'), [])
        ->assertSessionHasErrors(['term_id', 'subject_id', 'name', 'max_score', 'weight']);
});

it('enforces unique constraint on term, subject, and name', function () {
    AssessmentPlan::factory()
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create(['name' => 'Midterm Test', 'school_id' => $this->school->id]);

    actingAs($this->user)
        ->post(route('admin.settings.assessment-plans.store'), [
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'name' => 'Midterm Test',
            'ordering' => 2,
            'max_score' => 100,
            'weight' => 50,
        ])
        ->assertSessionHasErrors(['name']);
});

it('can update an assessment plan', function () {
    $plan = AssessmentPlan::factory()
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create(['name' => 'Old Name', 'school_id' => $this->school->id]);

    actingAs($this->user)
        ->put(route('admin.settings.assessment-plans.update', $plan), [
            'term_id' => $this->term->id,
            'subject_id' => $this->subject->id,
            'name' => 'New Name',
            'ordering' => 1,
            'max_score' => 50,
            'weight' => 30,
        ])
        ->assertRedirect();

    expect($plan->fresh()->name)->toBe('New Name');
    expect($plan->fresh()->max_score)->toBe(50);
});

it('can delete an assessment plan without grades', function () {
    $plan = AssessmentPlan::factory()
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create(['school_id' => $this->school->id]);

    actingAs($this->user)
        ->delete(route('admin.settings.assessment-plans.destroy', $plan))
        ->assertRedirect();

    expect(AssessmentPlan::find($plan->id))->toBeNull();
});

it('prevents deletion if grades exist', function () {
    $plan = AssessmentPlan::factory()
        ->forTerm($this->term)
        ->forSubject($this->subject)
        ->create(['school_id' => $this->school->id]);

    // Create a grade for this plan
    Grade::factory()
        ->forAssessment($plan)
        ->create(['school_id' => $this->school->id]);

    actingAs($this->user)
        ->delete(route('admin.settings.assessment-plans.destroy', $plan))
        ->assertSessionHasErrors();

    expect(AssessmentPlan::find($plan->id))->not->toBeNull();
});
