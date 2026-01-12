<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($validated, $request) {
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

            $request->user()->setActiveSchool($school);

            $this->createInitialAcademicYear($school);
        });

        return to_route('admin.dashboard');
    }

    private function createInitialAcademicYear(School $school): void
    {
        $currentYear = Carbon::now()->year;

        $startYear = $currentYear - 1;
        $endYear = $currentYear;

        AcademicYear::create([
            'school_id' => $school->id,
            'name' => "{$startYear}/{$endYear}",
            'starts_at' => Carbon::create($startYear, 9, 1), // configurable later
            'ends_at' => Carbon::create($endYear, 8, 31),
            'is_current' => true,
        ]);
    }
}
