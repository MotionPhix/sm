<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionCycle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdmissionCycleController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $academicYear = $school->academicYears()
            ->where('is_current', true)
            ->firstOrFail();

        return Inertia::render('admin/settings/admission-cycles/Index', [
            'academicYear' => $academicYear,
            'cycles' => $academicYear->admissionCycles()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;

        $academicYear = $school->academicYears()
            ->where('is_current', true)
            ->firstOrFail();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        AdmissionCycle::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => $data['name'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Admission cycle created.');
    }
}
