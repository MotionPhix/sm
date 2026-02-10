<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
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
        Permission::firstOrCreate(['name' => 'payments.create'], ['label' => 'Record Payments']),
        Permission::firstOrCreate(['name' => 'payments.view'], ['label' => 'View Payments']),
    ];

    $bursarRole->permissions()->syncWithoutDetaching(collect($permissions)->pluck('id'));

    $this->student = Student::factory()->forSchool($this->school)->create();

    $this->invoice = Invoice::factory()
        ->forSchool($this->school)
        ->forStudent($this->student)
        ->issued()
        ->create([
            'total_amount' => 10000,
            'paid_amount' => 0,
        ]);

    $this->invoiceService = app(InvoiceService::class);
});

it('can record a payment against an invoice', function () {
    $payment = $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 5000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    expect($payment)->toBeInstanceOf(Payment::class);
    expect((float) $payment->amount)->toBe(5000.00);
    expect($payment->payment_method)->toBe('cash');
    expect($payment->invoice_id)->toBe($this->invoice->id);
    expect($payment->student_id)->toBe($this->student->id);
});

it('updates invoice paid amount when payment recorded', function () {
    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 3000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    $this->invoice->refresh();
    expect((float) $this->invoice->paid_amount)->toBe(3000.00);
});

it('updates invoice status to partially paid', function () {
    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 4000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('partially_paid');
});

it('updates invoice status to paid when fully paid', function () {
    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 10000,
        paymentMethod: 'bank_transfer',
        recordedBy: $this->bursar,
        referenceNumber: 'TRX-12345'
    );

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('paid');
    expect((float) $this->invoice->paid_amount)->toBe(10000.00);
});

it('can record multiple payments for one invoice', function () {
    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 3000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 4000,
        paymentMethod: 'mobile_money',
        recordedBy: $this->bursar,
        referenceNumber: 'MM123456'
    );

    $this->invoice->refresh();
    expect($this->invoice->payments)->toHaveCount(2);
    expect((float) $this->invoice->paid_amount)->toBe(7000.00);
    expect($this->invoice->status)->toBe('partially_paid');
});

it('cannot record payment greater than outstanding balance', function () {
    expect(fn () => $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 15000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    ))->toThrow(InvalidArgumentException::class, 'exceeds outstanding balance');
});

it('cannot record zero or negative payment', function () {
    expect(fn () => $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 0,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    ))->toThrow(InvalidArgumentException::class, 'must be greater than zero');

    expect(fn () => $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: -100,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    ))->toThrow(InvalidArgumentException::class, 'must be greater than zero');
});

it('stores payment reference number', function () {
    $payment = $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 5000,
        paymentMethod: 'bank_transfer',
        recordedBy: $this->bursar,
        referenceNumber: 'REF-98765'
    );

    expect($payment->reference_number)->toBe('REF-98765');
});

it('tracks who recorded the payment', function () {
    $payment = $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 5000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    expect($payment->recorded_by)->toBe($this->bursar->id);
    expect($payment->recordedBy->id)->toBe($this->bursar->id);
});

it('stores payment notes', function () {
    $payment = $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 5000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar,
        notes: 'Partial payment for Term 1 fees'
    );

    expect($payment->notes)->toBe('Partial payment for Term 1 fees');
});

it('supports various payment methods', function () {
    $paymentMethods = ['cash', 'bank_transfer', 'mobile_money', 'cheque', 'card', 'other'];

    foreach ($paymentMethods as $method) {
        $invoice = Invoice::factory()
            ->forSchool($this->school)
            ->forStudent($this->student)
            ->issued()
            ->create(['total_amount' => 5000, 'paid_amount' => 0]);

        $payment = $this->invoiceService->recordPayment(
            invoice: $invoice,
            amount: 1000,
            paymentMethod: $method,
            recordedBy: $this->bursar
        );

        expect($payment->payment_method)->toBe($method);
    }
});

it('can cancel an invoice', function () {
    $this->invoiceService->cancelInvoice(
        invoice: $this->invoice,
        reason: 'Student transferred to another school',
        cancelledBy: $this->bursar
    );

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('cancelled');
    expect($this->invoice->cancellation_reason)->toBe('Student transferred to another school');
    expect($this->invoice->cancelled_by)->toBe($this->bursar->id);
    expect($this->invoice->cancelled_at)->not->toBeNull();
});

it('cannot cancel a fully paid invoice', function () {
    $this->invoice->update(['paid_amount' => 10000, 'status' => 'paid']);

    expect(fn () => $this->invoiceService->cancelInvoice(
        invoice: $this->invoice,
        reason: 'Test cancellation',
        cancelledBy: $this->bursar
    ))->toThrow(RuntimeException::class, 'Cannot cancel a fully paid invoice');
});

it('cannot cancel invoice with payments', function () {
    $this->invoiceService->recordPayment(
        invoice: $this->invoice,
        amount: 3000,
        paymentMethod: 'cash',
        recordedBy: $this->bursar
    );

    expect(fn () => $this->invoiceService->cancelInvoice(
        invoice: $this->invoice,
        reason: 'Test cancellation',
        cancelledBy: $this->bursar
    ))->toThrow(RuntimeException::class, 'Cannot cancel an invoice with payments');
});
