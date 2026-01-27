<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function switch(Request $request, AcademicYear $academicYear, AcademicYearService $service)
    {
        // Ensure the target year belongs to the user's active school
        $activeSchoolId = $request->user()?->active_school_id;
        abort_if(!$activeSchoolId || $academicYear->school_id !== $activeSchoolId, 403);

        $service->activate($academicYear);

        return back()->with('status', 'Academic year switched.');
    }
}
