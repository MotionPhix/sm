<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolClassController extends Controller
{
    /**
     * Display a listing of classes.
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $classes = SchoolClass::where('school_id', $school->id)
            ->withCount('streams')
            ->orderBy('order')
            ->get();

        return Inertia::render('admin/settings/classes/Index', [
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(Request $request)
    {
        $school = $request->user()->activeSchool;

        // Get the next order number
        $nextOrder = SchoolClass::where('school_id', $school->id)->max('order') + 1;

        return Inertia::render('admin/settings/classes/Create', [
            'nextOrder' => $nextOrder,
        ]);
    }

    /**
     * Store a newly created class.
     */
    public function store(StoreSchoolClassRequest $request)
    {
        $school = $request->user()->activeSchool;

        SchoolClass::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'order' => $request->order,
        ]);

        return redirect()
            ->route('admin.settings.classes.index')
            ->with('success', 'Class created successfully.');
    }

    /**
     * Show the form for editing a class.
     */
    public function edit(Request $request, SchoolClass $schoolClass)
    {
        return Inertia::render('admin/settings/classes/Edit', [
            'schoolClass' => $schoolClass,
        ]);
    }

    /**
     * Update a class.
     */
    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $schoolClass->update([
            'name' => $request->name,
            'order' => $request->order,
        ]);

        return redirect()
            ->route('admin.settings.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Remove a class.
     */
    public function destroy(Request $request, SchoolClass $schoolClass)
    {
        // Check if class has any streams assigned
        if ($schoolClass->streams()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete class with assigned streams. Remove streams first.',
            ]);
        }

        $schoolClass->delete();

        return redirect()
            ->route('admin.settings.classes.index')
            ->with('success', 'Class deleted successfully.');
    }
}
