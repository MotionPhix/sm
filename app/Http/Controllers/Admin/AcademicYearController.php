<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        return Inertia::render('admin/settings/academic-years/Index', [
            'academicYears' => AcademicYear::where('school_id', $school->id)
                ->orderByDesc('starts_at')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        // Safety: prevent overlapping academic years
        $overlap = AcademicYear::where('school_id', $school->id)
            ->where(function ($q) use ($data) {
                $q->whereBetween('starts_at', [$data['starts_at'], $data['ends_at']])
                    ->orWhereBetween('ends_at', [$data['starts_at'], $data['ends_at']]);
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'starts_at' => 'Academic year dates overlap with an existing academic year.',
            ]);
        }

        // Lock existing years
        AcademicYear::where('school_id', $school->id)
            ->update(['is_current' => false]);

        AcademicYear::create([
            'school_id' => $school->id,
            'name' => $data['name'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'is_current' => true,
        ]);

        return back()->with('success', 'Academic year created and set as current.');
    }
}
