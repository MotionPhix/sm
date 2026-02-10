<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubjectRequest;
use App\Http\Requests\Admin\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects.
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $subjects = Subject::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/subjects/Index', [
            'subjects' => $subjects,
        ]);
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create()
    {
        return Inertia::render('admin/settings/subjects/Create');
    }

    /**
     * Store a newly created subject.
     */
    public function store(StoreSubjectRequest $request)
    {
        $school = $request->user()->activeSchool;

        Subject::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()
            ->route('admin.settings.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Show the form for editing a subject.
     */
    public function edit(Subject $subject)
    {
        return Inertia::render('admin/settings/subjects/Edit', [
            'subject' => $subject,
        ]);
    }

    /**
     * Update a subject.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()
            ->route('admin.settings.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Remove a subject.
     */
    public function destroy(Subject $subject)
    {
        // Optional: Add check for existing assignments/usage
        $subject->delete();

        return redirect()
            ->route('admin.settings.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
