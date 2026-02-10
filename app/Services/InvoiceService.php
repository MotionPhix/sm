<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate an invoice for a student based on active fee structures.
     *
     * @param  Student  $student  The student to generate invoice for
     * @param  AcademicYear  $academicYear  Academic year for the invoice
     * @param  Term|null  $term  Specific term (if null, generates for entire year)
     * @param  User  $issuedBy  User who issued the invoice
     * @param  bool  $prorate  Whether to prorate fees based on enrollment date
     */
    public function generateInvoiceForStudent(
        Student $student,
        AcademicYear $academicYear,
        ?Term $term,
        User $issuedBy,
        bool $prorate = false
    ): Invoice {
        return DB::transaction(function () use ($student, $academicYear, $term, $issuedBy, $prorate) {
            // Get current enrollment to find the student's class
            $currentEnrollment = $student->enrollments()
                ->with('classroom.class')
                ->where('is_active', true)
                ->first();

            if (! $currentEnrollment) {
                throw new \RuntimeException('Student has no active enrollment.');
            }

            $classId = $currentEnrollment->classroom->school_class_id;

            // Fetch active fee structures for the student's class
            $query = FeeStructure::query()
                ->with('feeItem')
                ->where('school_class_id', $classId)
                ->where('academic_year_id', $academicYear->id)
                ->where('is_active', true);

            if ($term) {
                $query->where('term_id', $term->id);
            }

            $feeStructures = $query->get();

            if ($feeStructures->isEmpty()) {
                throw new \RuntimeException('No active fee structures found for this class and period.');
            }

            // Calculate total amount
            $totalAmount = 0;
            $items = [];

            foreach ($feeStructures as $feeStructure) {
                $amount = $feeStructure->getTotalAmount();

                // Apply proration if requested
                if ($prorate && $term && $currentEnrollment->enrollment_date) {
                    $amount = $this->calculateProration(
                        $amount,
                        $term,
                        $currentEnrollment->enrollment_date
                    );
                }

                $totalAmount += $amount;

                $items[] = [
                    'fee_item_id' => $feeStructure->fee_item_id,
                    'description' => $feeStructure->feeItem->name,
                    'quantity' => $feeStructure->quantity,
                    'unit_price' => $feeStructure->amount,
                    'amount' => $amount,
                ];
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber($student->school_id);

            // Create invoice
            $invoice = Invoice::create([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
                'term_id' => $term?->id,
                'invoice_number' => $invoiceNumber,
                'issue_date' => now(),
                'due_date' => $this->calculateDueDate($term),
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'status' => 'draft',
                'issued_by' => $issuedBy->id,
            ]);

            // Create invoice items
            foreach ($items as $itemData) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    ...$itemData,
                ]);
            }

            // Issue the invoice
            $this->updateInvoiceStatus($invoice, 'issued');

            return $invoice->fresh('items', 'payments');
        });
    }

    /**
     * Record a payment against an invoice.
     *
     * @param  Invoice  $invoice  The invoice to payment is for
     * @param  float  $amount  Payment amount
     * @param  string  $paymentMethod  Payment method (cash, bank_transfer, mobile_money, cheque, card, other)
     * @param  User  $recordedBy  User recording the payment
     * @param  string|null  $referenceNumber  Reference/transaction number
     * @param  Carbon|null  $paymentDate  Date of payment (defaults to now)
     * @param  string|null  $notes  Additional notes
     */
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $paymentMethod,
        User $recordedBy,
        ?string $referenceNumber = null,
        ?Carbon $paymentDate = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $paymentMethod, $recordedBy, $referenceNumber, $paymentDate, $notes) {
            // Validate payment amount
            $outstandingBalance = $invoice->total_amount - $invoice->paid_amount;

            if ($amount <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than zero.');
            }

            if ($amount > $outstandingBalance) {
                throw new \InvalidArgumentException(
                    sprintf('Payment amount (MK %.2f) exceeds outstanding balance (MK %.2f).', $amount, $outstandingBalance)
                );
            }

            // Create payment
            $payment = Payment::create([
                'school_id' => $invoice->school_id,
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $amount,
                'payment_date' => $paymentDate ?? now(),
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'recorded_by' => $recordedBy->id,
            ]);

            // Update invoice paid amount
            $invoice->update([
                'paid_amount' => $invoice->paid_amount + $amount,
            ]);

            // Update invoice status
            $this->updateInvoiceStatus($invoice);

            return $payment->fresh();
        });
    }

    /**
     * Update invoice status based on payment state.
     * Can also be used to manually change status (e.g., draft -> issued, issued -> cancelled).
     *
     * @param  string|null  $manualStatus  Manual status override
     */
    public function updateInvoiceStatus(Invoice $invoice, ?string $manualStatus = null): void
    {
        if ($manualStatus) {
            // Manual status change (e.g., draft -> issued, issued -> cancelled)
            $invoice->update(['status' => $manualStatus]);

            return;
        }

        // Automatic status based on payment state
        $invoice->refresh();

        if ($invoice->status === 'cancelled') {
            // Don't change status of cancelled invoices
            return;
        }

        if ($invoice->paid_amount >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } elseif ($invoice->paid_amount > 0) {
            $invoice->update(['status' => 'partially_paid']);
        } elseif ($invoice->due_date && now()->isAfter($invoice->due_date) && $invoice->status === 'issued') {
            $invoice->update(['status' => 'overdue']);
        }
    }

    /**
     * Cancel an invoice with a reason.
     */
    public function cancelInvoice(Invoice $invoice, string $reason, User $cancelledBy): void
    {
        if ($invoice->status === 'paid') {
            throw new \RuntimeException('Cannot cancel a fully paid invoice.');
        }

        if ($invoice->paid_amount > 0) {
            throw new \RuntimeException('Cannot cancel an invoice with payments. Please refund payments first.');
        }

        $invoice->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => $cancelledBy->id,
        ]);
    }

    /**
     * Calculate prorated amount based on enrollment date within a term.
     *
     * @param  float  $amount  Original amount
     * @param  Term  $term  The term
     * @param  Carbon  $enrollmentDate  Date student enrolled
     * @return float Prorated amount
     */
    protected function calculateProration(float $amount, Term $term, Carbon $enrollmentDate): float
    {
        // If enrolled before term starts, no proration
        if ($enrollmentDate->isBefore($term->starts_on)) {
            return $amount;
        }

        // If enrolled after term ends, return 0 (shouldn't happen normally)
        if ($enrollmentDate->isAfter($term->ends_on)) {
            return 0;
        }

        // Calculate days: total term days and days remaining from enrollment
        $totalDays = $term->starts_on->diffInDays($term->ends_on) + 1;
        $remainingDays = $enrollmentDate->diffInDays($term->ends_on) + 1;

        // Calculate prorated amount
        $proratedAmount = ($amount / $totalDays) * $remainingDays;

        return round($proratedAmount, 2);
    }

    /**
     * Generate a unique invoice number for a school.
     * Format: INV-YYYY-NNNN (e.g., INV-2024-0001)
     */
    protected function generateInvoiceNumber(int $schoolId): string
    {
        $year = now()->year;
        $prefix = "INV-{$year}-";

        // Get the latest invoice number for this school and year
        $latestInvoice = Invoice::where('school_id', $schoolId)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($latestInvoice) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($latestInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate due date for an invoice based on term.
     * Default: 30 days from issue date, or term end date if sooner.
     */
    protected function calculateDueDate(?Term $term): Carbon
    {
        $defaultDueDate = now()->addDays(30);

        if (! $term) {
            return $defaultDueDate;
        }

        // Use term end date if it's sooner than default
        return $term->ends_on->isBefore($defaultDueDate)
            ? $term->ends_on
            : $defaultDueDate;
    }

    /**
     * Mark overdue invoices.
     * Should be called via a scheduled command.
     *
     * @return int Number of invoices marked overdue
     */
    public function markOverdueInvoices(): int
    {
        $invoices = Invoice::where('status', 'issued')
            ->where('due_date', '<', now())
            ->get();

        foreach ($invoices as $invoice) {
            $this->updateInvoiceStatus($invoice);
        }

        return $invoices->count();
    }
}
