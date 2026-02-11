<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGradeScaleRequest;
use App\Http\Requests\Admin\UpdateGradeScaleRequest;
use App\Models\GradeScale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GradeScaleController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $gradeScales = GradeScale::where('school_id', $school->id)
            ->with(['steps' => fn ($q) => $q->orderBy('ordering')])
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/grade-scales/Index', [
            'gradeScales' => $gradeScales,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/settings/grade-scales/Create');
    }

    public function store(StoreGradeScaleRequest $request)
    {
        $school = $request->user()->activeSchool;
        $validated = $request->validated();

        DB::transaction(function () use ($school, $validated) {
            $gradeScale = GradeScale::create([
                'school_id' => $school->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['steps'] as $index => $step) {
                $gradeScale->steps()->create([
                    'min_percent' => $step['min_percent'],
                    'max_percent' => $step['max_percent'],
                    'grade' => $step['grade'],
                    'comment' => $step['comment'] ?? null,
                    'ordering' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Grading scale created successfully.');
    }

    public function edit(Request $request, GradeScale $gradeScale)
    {
        $school = $request->user()->activeSchool;
        abort_unless($gradeScale->school_id === $school->id, 403);

        return Inertia::render('admin/settings/grade-scales/Edit', [
            'gradeScale' => $gradeScale->load(['steps' => fn ($q) => $q->orderBy('ordering')]),
        ]);
    }

    public function update(UpdateGradeScaleRequest $request, GradeScale $gradeScale)
    {
        $school = $request->user()->activeSchool;
        abort_unless($gradeScale->school_id === $school->id, 403);

        $validated = $request->validated();

        DB::transaction(function () use ($gradeScale, $validated) {
            $gradeScale->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            // Replace all steps
            $gradeScale->steps()->delete();

            foreach ($validated['steps'] as $index => $step) {
                $gradeScale->steps()->create([
                    'min_percent' => $step['min_percent'],
                    'max_percent' => $step['max_percent'],
                    'grade' => $step['grade'],
                    'comment' => $step['comment'] ?? null,
                    'ordering' => $index + 1,
                ]);
            }
        });

        return back()->with('success', 'Grading scale updated successfully.');
    }

    public function destroy(Request $request, GradeScale $gradeScale)
    {
        $school = $request->user()->activeSchool;
        abort_unless($gradeScale->school_id === $school->id, 403);

        $gradeScale->delete();

        return back()->with('success', 'Grading scale deleted successfully.');
    }
}
