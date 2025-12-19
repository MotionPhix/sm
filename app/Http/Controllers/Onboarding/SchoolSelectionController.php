<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolSelectionController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('schools/Select', [
            'schools' => $request->user()->schools()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
        ]);

        $user = $request->user();

        abort_unless(
            $user->schools()->where('schools.id', $request->school_id)->exists(),
            403
        );

        $user->setActiveSchool(
            $user->schools()->findOrFail($request->school_id)
        );

        return back();
    }

}
