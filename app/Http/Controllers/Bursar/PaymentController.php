<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bursar\RecordPaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Record a payment against an invoice.
     */
    public function store(RecordPaymentRequest $request, Invoice $invoice)
    {
        try {
            $payment = $this->invoiceService->recordPayment(
                invoice: $invoice,
                amount: $request->amount,
                paymentMethod: $request->payment_method,
                recordedBy: $request->user(),
                referenceNumber: $request->reference_number,
                paymentDate: $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : null,
                notes: $request->notes
            );

            return redirect()
                ->route('bursar.invoices.show', $invoice)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show payment details.
     */
    public function show(Request $request, Payment $payment)
    {
        $payment->load([
            'invoice.student',
            'invoice.academicYear',
            'invoice.term',
            'student',
            'recordedBy',
        ]);

        return Inertia::render('bursar/payments/Show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show payment history for a student.
     */
    public function history(Request $request, Student $student)
    {
        $query = Payment::where('student_id', $student->id)
            ->with(['invoice', 'recordedBy']);

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $payments = $query->latest('payment_date')
            ->paginate($request->input('per_page', 20))
            ->withQueryString();

        $totalPaid = Payment::where('student_id', $student->id)->sum('amount');

        return Inertia::render('bursar/payments/History', [
            'student' => $student->load('activeEnrollment.classroom.class'),
            'payments' => $payments,
            'totalPaid' => $totalPaid,
            'filters' => $request->only(['from_date', 'to_date', 'payment_method']),
        ]);
    }
}
