<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStreamRequest;
use App\Http\Requests\Admin\UpdateStreamRequest;
use App\Models\Stream;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StreamController extends Controller
{
    /**
     * Display a listing of streams.
     */
    public function index(Request $request)
    {
        $school = $request->user()->activeSchool;

        $streams = Stream::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/settings/streams/Index', [
            'streams' => $streams,
        ]);
    }

    /**
     * Show the form for creating a new stream.
     */
    public function create()
    {
        return Inertia::render('admin/settings/streams/Create');
    }

    /**
     * Store a newly created stream.
     */
    public function store(StoreStreamRequest $request)
    {
        $school = $request->user()->activeSchool;

        Stream::create([
            'school_id' => $school->id,
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.settings.streams.index')
            ->with('success', 'Stream created successfully.');
    }

    /**
     * Show the form for editing a stream.
     */
    public function edit(Stream $stream)
    {
        return Inertia::render('admin/settings/streams/Edit', [
            'stream' => $stream,
        ]);
    }

    /**
     * Update a stream.
     */
    public function update(UpdateStreamRequest $request, Stream $stream)
    {
        $stream->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.settings.streams.index')
            ->with('success', 'Stream updated successfully.');
    }

    /**
     * Remove a stream.
     */
    public function destroy(Stream $stream)
    {
        // Check if stream has any assignments
        if ($stream->hasMany('ClassStreamAssignment')->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete stream with class assignments. Remove assignments first.',
            ]);
        }

        $stream->delete();

        return redirect()
            ->route('admin.settings.streams.index')
            ->with('success', 'Stream deleted successfully.');
    }
}
