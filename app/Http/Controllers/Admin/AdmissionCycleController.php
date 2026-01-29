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

        return Inertia::render('admin/settings/admission-cycles/Index', [
            'admissionCycles' => $school->admissionCycles()->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('admin/settings/admission-cycles/Create');
    }

    public function edit(Request $request, AdmissionCycle $admissionCycle)
    {
        $school = $request->user()->activeSchool;
        abort_unless($admissionCycle->school_id === $school->id, 403);

        return Inertia::render('admin/settings/admission-cycles/Edit', [
            'admissionCycle' => $admissionCycle,
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:Y-m-d'],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after:starts_at'],
            'target_class' => ['required', 'string', 'max:255'],
            'max_intake' => ['nullable', 'integer', 'min:1'],
        ]);

        AdmissionCycle::create([
            'school_id' => $school->id,
            ...$data,
        ]);

        return back()->with('success', 'Admission cycle created.');
    }

    public function update(Request $request, AdmissionCycle $admissionCycle)
    {
        $school = $request->user()->activeSchool;

        if ($admissionCycle->school_id !== $school->id) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:Y-m-d'],
            'ends_at' => ['required', 'date_format:Y-m-d', 'after:starts_at'],
            'target_class' => ['required', 'string', 'max:255'],
            'max_intake' => ['nullable', 'integer', 'min:1'],
        ]);

        $admissionCycle->update($data);

        return back()->with('success', 'Admission cycle updated.');
    }

    public function destroy(Request $request, AdmissionCycle $admissionCycle)
    {
        $school = $request->user()->activeSchool;

        if ($admissionCycle->school_id !== $school->id) {
            abort(403);
        }

        $admissionCycle->delete();

        return back()->with('success', 'Admission cycle deleted.');
    }
}
