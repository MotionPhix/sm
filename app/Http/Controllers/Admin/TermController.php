<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;
        $year = $school->academicYears()->where('is_current', true)->firstOrFail();

        return Inertia::render('admin/settings/terms/Index', [
            'academicYear' => $year,
            'terms' => $year->terms()->orderBy('sequence')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $school = $request->user()->activeSchool;
        $year = $school->academicYears()->where('is_current', true)->firstOrFail();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('terms')->where(function ($query) use ($year) {
                    return $query->where('academic_year_id', $year->id);
                }),
            ],
            'sequence' => ['required', 'integer', 'min:1', 'max:6'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
        ]);

        Term::create([
            'school_id' => $school->id,
            'academic_year_id' => $year->id,
            'name' => $data['name'],
            'sequence' => $data['sequence'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Term created successfully');
    }

    public function update(Request $request, Term $term)
    {
        $school = $request->user()->activeSchool;
        abort_unless($term->school_id === $school->id, 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('terms')->where(function ($query) use ($term) {
                    return $query->where('academic_year_id', $term->academic_year_id);
                })->ignore($term->id),
            ],
            'sequence' => ['required', 'integer', 'min:1', 'max:6'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $term->update($data);

        return back()->with('success', 'Term updated successfully');
    }

    public function destroy(Request $request, Term $term)
    {
        $school = $request->user()->activeSchool;
        abort_unless($term->school_id === $school->id, 403);
        $term->delete();

        return back()->with('success', 'Term deleted successfully');
    }

    /**
     * Generate default 3-term structure for Malawian schools
     */
    public function generateDefaults(Request $request)
    {
        $school = $request->user()->activeSchool;
        $year = $school->academicYears()->where('is_current', true)->firstOrFail();

        // Check if terms already exist
        if ($year->terms()->exists()) {
            return back()->withErrors(['terms' => 'Terms already exist for this academic year. Delete existing terms first.']);
        }

        // Malawian school year typically runs September to July
        // Term 1: September - December
        // Term 2: January - April
        // Term 3: May - July
        $yearStart = $year->starts_on ?? now()->startOfYear();
        $yearEnd = $year->ends_on ?? now()->endOfYear();

        $defaults = [
            [
                'name' => 'Term 1',
                'sequence' => 1,
                'starts_on' => $yearStart->copy()->month(9)->day(1),
                'ends_on' => $yearStart->copy()->month(12)->day(15),
            ],
            [
                'name' => 'Term 2',
                'sequence' => 2,
                'starts_on' => $yearStart->copy()->addYear()->month(1)->day(6),
                'ends_on' => $yearStart->copy()->addYear()->month(4)->day(15),
            ],
            [
                'name' => 'Term 3',
                'sequence' => 3,
                'starts_on' => $yearStart->copy()->addYear()->month(5)->day(1),
                'ends_on' => $yearStart->copy()->addYear()->month(7)->day(31),
            ],
        ];

        foreach ($defaults as $termData) {
            Term::create([
                'school_id' => $school->id,
                'academic_year_id' => $year->id,
                'name' => $termData['name'],
                'sequence' => $termData['sequence'],
                'starts_on' => $termData['starts_on'],
                'ends_on' => $termData['ends_on'],
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Default 3-term structure created successfully');
    }
}
