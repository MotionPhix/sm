<?php

namespace App\Services;

use App\Models\AcademicYear;

class AcademicYearService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        public function activate(AcademicYear $year): void
        {
            AcademicYear::where('school_id', $year->school_id)
                ->update(['is_active' => false]);

            $year->update(['is_active' => true]);
        }
    }
}
