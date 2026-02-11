<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $school = $request->user()->activeSchool;

        $totalStudents = Student::where('school_id', $school->id)->count();

        $totalFeeExpected = Invoice::where('school_id', $school->id)->sum('total_amount');
        $totalFeeCollected = Payment::whereHas('invoice', function ($q) use ($school) {
            $q->where('school_id', $school->id);
        })->sum('amount');

        $attendanceRate = 0;
        $totalRecords = AttendanceRecord::where('school_id', $school->id)->count();
        if ($totalRecords > 0) {
            $presentRecords = AttendanceRecord::where('school_id', $school->id)
                ->where('status', 'present')
                ->count();
            $attendanceRate = round(($presentRecords / $totalRecords) * 100, 1);
        }

        return Inertia::render('admin/reports/Index', [
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalFeeExpected' => $totalFeeExpected,
                'totalFeeCollected' => $totalFeeCollected,
                'totalFeePending' => $totalFeeExpected - $totalFeeCollected,
                'attendanceRate' => $attendanceRate,
            ],
        ]);
    }
}
