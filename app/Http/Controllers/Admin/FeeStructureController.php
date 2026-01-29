<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFeeStructureRequest;
use App\Models\AcademicYear;
use App\Models\FeeItem;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FeeStructureController extends Controller
{
    /**
     * Display fee structures for a specific academic year
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->academicYears()
            ->where('is_current', true)
            ->firstOrFail();

        $feeStructures = $academicYear->feeStructures()
            ->with(['schoolClass', 'term', 'feeItem'])
            ->orderBy('school_class_id')
            ->orderBy('term_id')
            ->paginate(50);

        return Inertia::render('admin/settings/fee-structures/Index', [
            'academicYear' => $academicYear,
            'feeStructures' => $feeStructures,
        ]);
    }

    /**
     * Show form to create fee structure
     */
    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;
        $academicYear = $school->academicYears()
            ->where('is_current', true)
            ->firstOrFail();

        $classes = $school->classes()->orderBy('order')->get();
        $terms = $academicYear->terms()->orderBy('sequence')->get();
        $feeItems = $school->feeItems()->where('is_active', true)->orderBy('category')->get();

        return Inertia::render('admin/settings/fee-structures/Create', [
            'academicYear' => $academicYear,
            'classes' => $classes,
            'terms' => $terms,
            'feeItems' => $feeItems,
        ]);
    }

    /**
     * Store fee structure
     */
    public function store(StoreFeeStructureRequest $request)
    {
        $school = $request->user()->activeSchool;
        $validated = $request->validated();

        // Expand class IDs based on grouping strategy
        $classIds = $this->expandClassIds($school, $validated);

        DB::transaction(function () use ($school, $validated, $classIds) {
            foreach ($classIds as $classId) {
                foreach ($validated['fee_items'] as $feeItemData) {
                    // Check for duplicate before creating
                    $existing = FeeStructure::where([
                        ['academic_year_id', '=', $validated['academic_year_id']],
                        ['school_class_id', '=', $classId],
                        ['term_id', '=', $validated['term_id'] ?? null],
                        ['fee_item_id', '=', $feeItemData['fee_item_id']],
                    ])->first();

                    if ($existing) {
                        // Update instead of creating duplicate
                        $existing->update([
                            'amount' => $feeItemData['amount'],
                            'quantity' => $feeItemData['quantity'] ?? 1,
                            'notes' => $feeItemData['notes'] ?? null,
                        ]);
                    } else {
                        FeeStructure::create([
                            'school_id' => $school->id,
                            'academic_year_id' => $validated['academic_year_id'],
                            'school_class_id' => $classId,
                            'term_id' => $validated['term_id'] ?? null,
                            'fee_item_id' => $feeItemData['fee_item_id'],
                            'amount' => $feeItemData['amount'],
                            'quantity' => $feeItemData['quantity'] ?? 1,
                            'notes' => $feeItemData['notes'] ?? null,
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Fee structure created successfully.');
    }

    /**
     * Expand class IDs based on grouping strategy
     * Converts 'primary' and 'secondary' groups into actual class IDs
     */
    private function expandClassIds(School $school, array $validated): array
    {
        $strategy = $validated['grouping_strategy'];
        $classIds = $validated['school_class_ids'];

        if ($strategy === 'primary-secondary') {
            $expandedIds = [];

            foreach ($classIds as $classId) {
                if ($classId === 'primary') {
                    // Primary classes: order <= 4 (typically Form 1-4)
                    $expandedIds = array_merge(
                        $expandedIds,
                        $school->classes()->where('order', '<=', 4)->pluck('id')->toArray()
                    );
                } elseif ($classId === 'secondary') {
                    // Secondary classes: order > 4 (typically Form 5+)
                    $expandedIds = array_merge(
                        $expandedIds,
                        $school->classes()->where('order', '>', 4)->pluck('id')->toArray()
                    );
                }
            }

            return array_unique($expandedIds);
        }

        // Individual strategy: use class IDs as-is
        return array_map('intval', $classIds);
    }

    /**
     * Show form to edit fee structure
     */
    public function edit(Request $request, FeeStructure $feeStructure)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeStructure->school_id === $school->id, 403);

        $classes = $school->classes()->orderBy('order')->get();
        $terms = $feeStructure->academicYear->terms()->orderBy('sequence')->get();
        $feeItems = $school->feeItems()->where('is_active', true)->orderBy('category')->get();

        return Inertia::render('admin/settings/fee-structures/Edit', [
            'feeStructure' => $feeStructure->load(['schoolClass', 'term', 'feeItem', 'academicYear']),
            'classes' => $classes,
            'terms' => $terms,
            'feeItems' => $feeItems,
        ]);
    }

    /**
     * Update fee structure
     */
    public function update(Request $request, FeeStructure $feeStructure)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeStructure->school_id === $school->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999.99'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $feeStructure->update([
            'amount' => $validated['amount'],
            'quantity' => $validated['quantity'] ?? $feeStructure->quantity,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Fee structure updated successfully.');
    }

    /**
     * Delete fee structure
     */
    public function destroy(Request $request, FeeStructure $feeStructure)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeStructure->school_id === $school->id, 403);

        $feeStructure->delete();

        return back()->with('success', 'Fee structure deleted successfully.');
    }
}
