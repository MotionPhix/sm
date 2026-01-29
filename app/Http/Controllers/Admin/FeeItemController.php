<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFeeItemRequest;
use App\Http\Requests\Admin\UpdateFeeItemRequest;
use App\Models\FeeItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeeItemController extends Controller
{
    /**
     * Display a listing of fee items
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $feeItems = $school->feeItems()
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(25);

        return Inertia::render('admin/settings/fee-items/Index', [
            'feeItems' => $feeItems,
            'categories' => FeeItem::categories(),
        ]);
    }

    /**
     * Show form to create new fee item
     */
    public function create()
    {
        return Inertia::render('admin/settings/fee-items/Create', [
            'categories' => FeeItem::categories(),
        ]);
    }

    /**
     * Store a newly created fee item
     */
    public function store(StoreFeeItemRequest $request)
    {
        $school = $request->user()->activeSchool;
        $validated = $request->validated();

        FeeItem::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'code' => strtoupper($validated['code']),
            'category' => $validated['category'],
            'is_mandatory' => $validated['is_mandatory'] ?? true,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Fee item created successfully.');
    }

    /**
     * Show form to edit fee item
     */
    public function edit(Request $request, FeeItem $feeItem)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeItem->school_id === $school->id, 403);

        return Inertia::render('admin/settings/fee-items/Edit', [
            'feeItem' => $feeItem,
            'categories' => FeeItem::categories(),
        ]);
    }

    /**
     * Update a fee item
     */
    public function update(UpdateFeeItemRequest $request, FeeItem $feeItem)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeItem->school_id === $school->id, 403);

        $validated = $request->validated();

        $feeItem->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'code' => strtoupper($validated['code']),
            'category' => $validated['category'],
            'is_mandatory' => $validated['is_mandatory'] ?? $feeItem->is_mandatory,
            'is_active' => $validated['is_active'] ?? $feeItem->is_active,
        ]);

        return back()->with('success', 'Fee item updated successfully.');
    }

    /**
     * Delete a fee item
     */
    public function destroy(Request $request, FeeItem $feeItem)
    {
        $school = $request->user()->activeSchool;
        abort_unless($feeItem->school_id === $school->id, 403);

        // Prevent deletion if fee item is used in fee structures
        if ($feeItem->feeStructures()->exists()) {
            return back()->withErrors([
                'fee_item' => 'Cannot delete this fee item as it\'s being used in fee structures.',
            ]);
        }

        $feeItem->delete();

        return back()->with('success', 'Fee item deleted successfully.');
    }
}
