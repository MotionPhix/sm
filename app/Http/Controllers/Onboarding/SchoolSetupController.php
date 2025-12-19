<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\Role;

class SchoolSetupController extends Controller
{
    public function create()
    {
        return Inertia::render('onboarding/CreateSchool');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'district' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
        ]);

        $school = School::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'district' => $validated['district'],
            'country' => $validated['country'],
            'code' => Str::upper(Str::random(8)),
        ]);

        $request->user()->schools()->attach($school->id, [
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);

        return to_route('home');
    }
}
