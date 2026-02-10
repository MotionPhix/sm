<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bursar\StoreInvoiceRequest;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\Term;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * List all invoices with filters.
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $query = Invoice::where('school_id', $school->id)
            ->with(['student', 'academicYear', 'term']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by academic year
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        // Filter by term
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Search by invoice number or student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('admission_number', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest('created_at')
            ->paginate($request->input('per_page', 20))
            ->withQueryString();

        // Load summary statistics
        $stats = [
            'total_invoiced' => Invoice::where('school_id', $school->id)->sum('total_amount'),
            'total_paid' => Invoice::where('school_id', $school->id)->sum('paid_amount'),
            'overdue_count' => Invoice::where('school_id', $school->id)->where('status', 'overdue')->count(),
            'unpaid_count' => Invoice::where('school_id', $school->id)->whereIn('status', ['issued', 'partially_paid', 'overdue'])->count(),
        ];

        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $terms = Term::where('school_id', $school->id)->get();

        return Inertia::render('bursar/invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'academicYears' => $academicYears,
            'terms' => $terms,
            'filters' => $request->only(['search', 'status', 'student_id', 'academic_year_id', 'term_id']),
        ]);
    }

    /**
     * Show form to create a new invoice.
     */
    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;

        $students = Student::where('school_id', $school->id)
            ->whereHas('enrollments', fn ($q) => $q->where('is_active', true))
            ->with('activeEnrollment.classroom.class')
            ->get();

        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        $terms = Term::where('school_id', $school->id)->get();

        return Inertia::render('bursar/invoices/Create', [
            'students' => $students,
            'academicYears' => $academicYears,
            'terms' => $terms,
        ]);
    }

    /**
     * Generate a new invoice for a student.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $student = Student::findOrFail($request->student_id);
        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        $term = $request->term_id ? Term::findOrFail($request->term_id) : null;

        try {
            $invoice = $this->invoiceService->generateInvoiceForStudent(
                student: $student,
                academicYear: $academicYear,
                term: $term,
                issuedBy: $request->user(),
                prorate: $request->boolean('prorate', false)
            );

            return redirect()
                ->route('bursar.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show invoice details with payment history.
     */
    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load([
            'student',
            'academicYear',
            'term',
            'items.feeItem',
            'payments.recordedBy',
            'issuedBy',
        ]);

        return Inertia::render('bursar/invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->invoiceService->cancelInvoice(
                invoice: $invoice,
                reason: $request->reason,
                cancelledBy: $request->user()
            );

            return redirect()
                ->route('bursar.invoices.index')
                ->with('success', 'Invoice cancelled successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
