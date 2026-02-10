<?php

use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Stream;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->school = School::factory()->create();

    $bursarRole = Role::factory()->create(['name' => 'bursar', 'label' => 'Bursar']);
    $this->bursar = User::factory()->create(['active_school_id' => $this->school->id]);
    $this->bursar->schools()->attach($this->school->id, ['role_id' => $bursarRole->id]);

    $permissions = [
        Permission::firstOrCreate(['name' => 'invoices.create'], ['label' => 'Create Invoices']),
        Permission::firstOrCreate(['name' => 'invoices.view'], ['label' => 'View Invoices']),
    ];

    $bursarRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    $this->academicYear = AcademicYear::factory()->forSchool($this->school)->current()->create();
    $this->term = Term::factory()->forAcademicYear($this->academicYear)->create([
        'sequence' => 1,
        'name' => 'Term 1',
        'is_active' => true,
        'starts_on' => now()->subDays(30),
        'ends_on' => now()->addDays(60),
    ]);

    // Create 2 more terms to satisfy onboarding (requires >= 3)
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 2, 'name' => 'Term 2']);
    Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 3, 'name' => 'Term 3']);

    // Satisfy onboarding: classes, streams, subjects
    SchoolClass::factory()->forSchool($this->school)->create();
    Stream::factory()->forSchool($this->school)->create();
    Subject::factory()->forSchool($this->school)->create();
});

it('displays the bursar dashboard', function () {
    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->has('stats')
        ->has('user')
    );
});

it('shows correct financial statistics', function () {
    $student = Student::factory()->forSchool($this->school)->create();

    // Create invoices with known amounts
    Invoice::factory()
        ->forSchool($this->school)
        ->forStudent($student)
        ->issued()
        ->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 20000,
            'paid_amount' => 5000,
        ]);

    Invoice::factory()
        ->forSchool($this->school)
        ->forStudent($student)
        ->overdue()
        ->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 15000,
            'paid_amount' => 0,
        ]);

    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->where('stats.totalInvoiced', fn ($value) => (float) $value === 35000.0)
        ->where('stats.totalCollected', fn ($value) => (float) $value === 5000.0)
        ->where('stats.totalOutstanding', fn ($value) => (float) $value === 30000.0)
        ->where('stats.overdueCount', 1)
        ->where('stats.invoiceCount', 2)
    );
});

it('shows recent invoices', function () {
    $student = Student::factory()->forSchool($this->school)->create();

    Invoice::factory()
        ->count(7)
        ->forSchool($this->school)
        ->forStudent($student)
        ->issued()
        ->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
        ]);

    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->has('recentInvoices', 5)
    );
});

it('shows recent payments', function () {
    $student = Student::factory()->forSchool($this->school)->create();
    $invoice = Invoice::factory()
        ->forSchool($this->school)
        ->forStudent($student)
        ->issued()
        ->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 50000,
        ]);

    Payment::factory()
        ->count(7)
        ->forInvoice($invoice)
        ->create([
            'amount' => 1000,
            'recorded_by' => $this->bursar->id,
        ]);

    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->has('recentPayments', 5)
    );
});

it('shows collection by payment method', function () {
    $student = Student::factory()->forSchool($this->school)->create();
    $invoice = Invoice::factory()
        ->forSchool($this->school)
        ->forStudent($student)
        ->issued()
        ->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term->id,
            'total_amount' => 100000,
        ]);

    Payment::factory()
        ->forInvoice($invoice)
        ->cash()
        ->create(['amount' => 5000, 'recorded_by' => $this->bursar->id]);

    Payment::factory()
        ->forInvoice($invoice)
        ->mobileMoney()
        ->create(['amount' => 3000, 'recorded_by' => $this->bursar->id]);

    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->has('collectionByMethod', 2)
    );
});

it('shows active academic year and term', function () {
    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->has('activeAcademicYear')
        ->where('activeAcademicYear.id', $this->academicYear->id)
        ->has('activeTerm')
        ->where('activeTerm.id', $this->term->id)
    );
});

it('requires authentication', function () {
    $response = $this->get(route('bursar.dashboard'));

    $response->assertRedirect(route('login'));
});

it('requires bursar role', function () {
    $teacherRole = Role::factory()->create(['name' => 'teacher', 'label' => 'Teacher']);
    $teacher = User::factory()->create(['active_school_id' => $this->school->id]);
    $teacher->schools()->attach($this->school->id, ['role_id' => $teacherRole->id]);

    $response = $this->actingAs($teacher)
        ->get(route('bursar.dashboard'));

    $response->assertForbidden();
});

it('shows zero stats when no invoices exist', function () {
    $response = $this->actingAs($this->bursar)
        ->get(route('bursar.dashboard'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('bursar/Dashboard')
        ->where('stats.totalInvoiced', fn ($value) => (float) $value === 0.0)
        ->where('stats.totalCollected', fn ($value) => (float) $value === 0.0)
        ->where('stats.totalOutstanding', fn ($value) => (float) $value === 0.0)
        ->where('stats.overdueCount', 0)
        ->where('stats.invoiceCount', 0)
        ->where('stats.paymentCount', 0)
        ->where('stats.collectionRate', 0)
        ->has('recentInvoices', 0)
        ->has('recentPayments', 0)
        ->has('collectionByMethod', 0)
    );
});
