<?php

namespace App\Http\Controllers\Bursar;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Term;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $school = $request->user()->activeSchool;
        $user = $request->user();

        // Financial totals
        $totalInvoiced = Invoice::where('school_id', $school->id)->sum('total_amount');
        $totalCollected = Invoice::where('school_id', $school->id)->sum('paid_amount');
        $totalOutstanding = $totalInvoiced - $totalCollected;
        $collectionRate = $totalInvoiced > 0
            ? round(($totalCollected / $totalInvoiced) * 100, 1)
            : 0;

        // Overdue stats
        $overdueInvoices = Invoice::where('school_id', $school->id)
            ->where('status', 'overdue');
        $overdueCount = (clone $overdueInvoices)->count();
        $overdueAmount = (clone $overdueInvoices)->selectRaw('SUM(total_amount - paid_amount) as total')
            ->value('total') ?? 0;

        // Counts
        $invoiceCount = Invoice::where('school_id', $school->id)->count();
        $paymentCount = Payment::where('school_id', $school->id)->count();

        // Recent invoices
        $recentInvoices = Invoice::where('school_id', $school->id)
            ->with('student:id,first_name,last_name,admission_number')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'uuid', 'student_id', 'invoice_number', 'total_amount', 'paid_amount', 'status', 'issue_date', 'due_date']);

        // Recent payments
        $recentPayments = Payment::where('school_id', $school->id)
            ->with([
                'student:id,first_name,last_name,admission_number',
                'invoice:id,uuid,invoice_number',
            ])
            ->latest('payment_date')
            ->limit(5)
            ->get(['id', 'uuid', 'student_id', 'invoice_id', 'amount', 'payment_method', 'payment_date']);

        // Collection by payment method
        $collectionByMethod = Payment::where('school_id', $school->id)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        // Active academic year and term
        $activeAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $activeTerm = $activeAcademicYear
            ? Term::where('academic_year_id', $activeAcademicYear->id)
                ->where('is_active', true)
                ->where('starts_on', '<=', now())
                ->where('ends_on', '>=', now())
                ->first()
            : null;

        return Inertia::render('bursar/Dashboard', [
            'user' => [
                'name' => $user->name,
            ],
            'stats' => [
                'totalInvoiced' => (float) $totalInvoiced,
                'totalCollected' => (float) $totalCollected,
                'totalOutstanding' => (float) $totalOutstanding,
                'collectionRate' => $collectionRate,
                'overdueCount' => $overdueCount,
                'overdueAmount' => (float) $overdueAmount,
                'invoiceCount' => $invoiceCount,
                'paymentCount' => $paymentCount,
            ],
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'collectionByMethod' => $collectionByMethod,
            'activeAcademicYear' => $activeAcademicYear ? [
                'id' => $activeAcademicYear->id,
                'name' => $activeAcademicYear->name,
            ] : null,
            'activeTerm' => $activeTerm ? [
                'id' => $activeTerm->id,
                'name' => $activeTerm->name,
            ] : null,
        ]);
    }
}
