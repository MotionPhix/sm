<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssessmentPlanRequest;
use App\Http\Requests\Admin\UpdateAssessmentPlanRequest;
use App\Models\AssessmentPlan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssessmentPlanController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->currentAcademicYear;

        $assessmentPlans = AssessmentPlan::where('school_id', $school->id)
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with(['term', 'subject'])
            ->orderBy('term_id')
            ->orderBy('subject_id')
            ->orderBy('ordering')
            ->paginate(50);

        $terms = $academicYear
            ? $academicYear->terms()->orderBy('sequence')->get()
            : collect();

        $subjects = $school->classes()->exists()
            ? \App\Models\Subject::where('school_id', $school->id)->orderBy('name')->get()
            : collect();

        return Inertia::render('admin/settings/assessment-plans/Index', [
            'assessmentPlans' => $assessmentPlans,
            'terms' => $terms,
            'subjects' => $subjects,
            'academicYear' => $academicYear,
        ]);
    }

    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->currentAcademicYear;

        $terms = $academicYear
            ? $academicYear->terms()->orderBy('sequence')->get()
            : collect();

        $subjects = \App\Models\Subject::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/assessment-plans/Create', [
            'terms' => $terms,
            'subjects' => $subjects,
            'academicYear' => $academicYear,
        ]);
    }

    public function store(StoreAssessmentPlanRequest $request)
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->currentAcademicYear;
        $validated = $request->validated();

        AssessmentPlan::create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'term_id' => $validated['term_id'],
            'subject_id' => $validated['subject_id'],
            'name' => $validated['name'],
            'ordering' => $validated['ordering'],
            'max_score' => $validated['max_score'],
            'weight' => $validated['weight'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Assessment plan created successfully.');
    }

    public function edit(Request $request, AssessmentPlan $assessmentPlan)
    {
        $school = $request->user()->activeSchool;
        abort_unless($assessmentPlan->school_id === $school->id, 403);

        $academicYear = $school->currentAcademicYear;

        $terms = $academicYear
            ? $academicYear->terms()->orderBy('sequence')->get()
            : collect();

        $subjects = \App\Models\Subject::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/assessment-plans/Edit', [
            'assessmentPlan' => $assessmentPlan->load(['term', 'subject']),
            'terms' => $terms,
            'subjects' => $subjects,
        ]);
    }

    public function update(UpdateAssessmentPlanRequest $request, AssessmentPlan $assessmentPlan)
    {
        $school = $request->user()->activeSchool;
        abort_unless($assessmentPlan->school_id === $school->id, 403);

        $validated = $request->validated();

        $assessmentPlan->update([
            'term_id' => $validated['term_id'],
            'subject_id' => $validated['subject_id'],
            'name' => $validated['name'],
            'ordering' => $validated['ordering'],
            'max_score' => $validated['max_score'],
            'weight' => $validated['weight'],
            'is_active' => $validated['is_active'] ?? $assessmentPlan->is_active,
        ]);

        return back()->with('success', 'Assessment plan updated successfully.');
    }

    public function destroy(Request $request, AssessmentPlan $assessmentPlan)
    {
        $school = $request->user()->activeSchool;
        abort_unless($assessmentPlan->school_id === $school->id, 403);

        if ($assessmentPlan->grades()->exists()) {
            return back()->withErrors([
                'assessment_plan' => 'Cannot delete this assessment plan as it has recorded grades.',
            ]);
        }

        $assessmentPlan->delete();

        return back()->with('success', 'Assessment plan deleted successfully.');
    }
}
