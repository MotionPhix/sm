<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\StoreSchoolRequest;
use App\Models\AcademicYear;
use App\Models\School;
use Carbon\Carbon;
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

    public function store(StoreSchoolRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            $school = School::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'district' => $validated['district'],
                'country' => $validated['country'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'code' => Str::upper(Str::random(8)),
            ]);

            $request->user()->schools()->attach($school->id, [
                'role_id' => Role::where('name', 'admin')->first()->id,
            ]);

            $request->user()->setActiveSchool($school);

            $this->createInitialAcademicYear($school);
        });

        return to_route('onboarding.classes.create');
    }

    private function createInitialAcademicYear(School $school): void
    {
        $currentYear = Carbon::now()->year;

        $startYear = $currentYear - 1;
        $endYear = $currentYear;

        $year = AcademicYear::create([
            'school_id' => $school->id,
            'name' => "{$startYear}/{$endYear}",
            'starts_at' => Carbon::create($startYear, 9, 1),
            'ends_at' => Carbon::create($endYear, 8, 31),
            'is_current' => true,
        ]);

        app(\App\Services\AcademicYearService::class)->createDefaultTerms($year);
    }
}
