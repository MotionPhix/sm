<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\AdmissionCycle;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('registrar/admissions/Index', [
            'cycles' => AdmissionCycle::where('school_id', $school->id)->get(),
            'applicants' => Applicant::where('school_id', $school->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;

        $data = $request->validate([
            'admission_cycle_id' => ['required', 'exists:admission_cycles,id'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'national_id' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string'],
        ]);

        Applicant::create([
            ...$data,
            'school_id' => $school->id,
        ]);

        return back()->with('success', 'Applicant added.');
    }

    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('registrar/admissions/components/ApplicantForm', [
            'cycles' => AdmissionCycle::where('school_id', $school->id)->get(),
        ]);
    }

    public function update(Request $request, Applicant $applicant)
    {
        $request->validate([
            'status' => ['required', 'in:applied,admitted,rejected'],
        ]);

        $applicant->update([
            'status' => $request->status,
        ]);

        return back();
    }
}
