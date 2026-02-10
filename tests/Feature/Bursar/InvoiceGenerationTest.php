<?php

use App\Models\AcademicYear;
use App\Models\ClassStreamAssignment;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Term;
use App\Models\User;
use App\Services\InvoiceService;
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
    $this->term = Term::factory()->forAcademicYear($this->academicYear)->create(['sequence' => 1, 'name' => 'Term 1']);

    $this->schoolClass = SchoolClass::factory()->forSchool($this->school)->create(['name' => 'Form 1']);
    $this->classStreamAssignment = ClassStreamAssignment::factory()
        ->forSchool($this->school)
        ->forAcademicYear($this->academicYear)
        ->forClass($this->schoolClass)
        ->create();

    $this->student = Student::factory()->forSchool($this->school)->create();
    StudentEnrollment::factory()
        ->forStudent($this->student)
        ->forClassroom($this->classStreamAssignment)
        ->create(['is_active' => true]);

    // Create fee structures for the class
    $this->tuitionFeeItem = FeeItem::factory()->forSchool($this->school)->create([
        'name' => 'Tuition Fee',
        'category' => 'tuition',
    ]);

    $this->examFeeItem = FeeItem::factory()->forSchool($this->school)->create([
        'name' => 'Exam Fee',
        'category' => 'exam',
    ]);

    FeeStructure::factory()->forSchool($this->school)->create([
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->schoolClass->id,
        'term_id' => $this->term->id,
        'fee_item_id' => $this->tuitionFeeItem->id,
        'amount' => 15000,
        'quantity' => 1,
        'is_active' => true,
    ]);

    FeeStructure::factory()->forSchool($this->school)->create([
        'academic_year_id' => $this->academicYear->id,
        'school_class_id' => $this->schoolClass->id,
        'term_id' => $this->term->id,
        'fee_item_id' => $this->examFeeItem->id,
        'amount' => 3000,
        'quantity' => 1,
        'is_active' => true,
    ]);

    $this->invoiceService = app(InvoiceService::class);
});

it('can generate an invoice for a student', function () {
    $invoice = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    expect($invoice)->toBeInstanceOf(Invoice::class);
    expect($invoice->student_id)->toBe($this->student->id);
    expect($invoice->academic_year_id)->toBe($this->academicYear->id);
    expect($invoice->term_id)->toBe($this->term->id);
    expect($invoice->status)->toBe('issued');
    expect((float) $invoice->total_amount)->toBe(18000.00); // 15000 + 3000
    expect((float) $invoice->paid_amount)->toBe(0.00);
});

it('creates invoice items from fee structures', function () {
    $invoice = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    expect($invoice->items)->toHaveCount(2);

    $tuitionItem = $invoice->items->where('fee_item_id', $this->tuitionFeeItem->id)->first();
    expect((float) $tuitionItem->amount)->toBe(15000.00);

    $examItem = $invoice->items->where('fee_item_id', $this->examFeeItem->id)->first();
    expect((float) $examItem->amount)->toBe(3000.00);
});

it('generates unique invoice numbers', function () {
    $invoice1 = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    $student2 = Student::factory()->forSchool($this->school)->create();
    StudentEnrollment::factory()
        ->forStudent($student2)
        ->forClassroom($this->classStreamAssignment)
        ->create(['is_active' => true]);

    $invoice2 = $this->invoiceService->generateInvoiceForStudent(
        student: $student2,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    expect($invoice1->invoice_number)->not->toBe($invoice2->invoice_number);
    expect($invoice1->invoice_number)->toMatch('/^INV-\d{4}-\d{4}$/');
    expect($invoice2->invoice_number)->toMatch('/^INV-\d{4}-\d{4}$/');
});

it('cannot generate invoice for student without active enrollment', function () {
    $unenrolledStudent = Student::factory()->forSchool($this->school)->create();

    expect(fn () => $this->invoiceService->generateInvoiceForStudent(
        student: $unenrolledStudent,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    ))->toThrow(RuntimeException::class, 'Student has no active enrollment.');
});

it('cannot generate invoice without active fee structures', function () {
    // Deactivate all fee structures
    FeeStructure::where('school_class_id', $this->schoolClass->id)->update(['is_active' => false]);

    expect(fn () => $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    ))->toThrow(RuntimeException::class, 'No active fee structures found');
});

it('can prorate fees for mid-term enrollments', function () {
    // Set enrollment date to halfway through the term
    $termDuration = $this->term->starts_on->diffInDays($this->term->ends_on);
    $midTermDate = $this->term->starts_on->addDays($termDuration / 2);

    $this->student->enrollments()->update(['enrollment_date' => $midTermDate]);

    $invoice = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar,
        prorate: true
    );

    // Prorated amount should be approximately half
    expect((float) $invoice->total_amount)->toBeLessThan(18000.00);
    expect((float) $invoice->total_amount)->toBeGreaterThan(8000.00);
});

it('sets default due date 30 days from issue', function () {
    $invoice = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    $expectedDueDate = now()->addDays(30)->format('Y-m-d');
    $actualDueDate = $invoice->due_date->format('Y-m-d');

    expect($actualDueDate)->toBe($expectedDueDate);
});

it('tracks who issued the invoice', function () {
    $invoice = $this->invoiceService->generateInvoiceForStudent(
        student: $this->student,
        academicYear: $this->academicYear,
        term: $this->term,
        issuedBy: $this->bursar
    );

    expect($invoice->issued_by)->toBe($this->bursar->id);
    expect($invoice->issuedBy->id)->toBe($this->bursar->id);
});
