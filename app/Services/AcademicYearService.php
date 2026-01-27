<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Term;
use Carbon\Carbon;

class AcademicYearService
{
    /**
     * Activate the provided academic year for its school by toggling is_current.
     */
    public function activate(AcademicYear $year): void
    {
        AcademicYear::where('school_id', $year->school_id)
            ->update(['is_current' => false]);

        $year->update(['is_current' => true]);
    }

    /**
     * Seed three default terms if none exist for the given academic year.
     * Splits the year into three contiguous segments.
     */
    public function createDefaultTerms(AcademicYear $year): void
    {
        if ($year->terms()->exists()) {
            return;
        }

        $start = Carbon::parse($year->starts_at)->startOfDay();
        $end = Carbon::parse($year->ends_at)->endOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            return;
        }

        $totalDays = $start->diffInDays($end) + 1;
        $segment = (int) floor($totalDays / 3);

        $t1Start = $start->copy();
        $t1End = $t1Start->copy()->addDays($segment - 1);

        $t2Start = $t1End->copy()->addDay();
        $t2End = $t2Start->copy()->addDays($segment - 1);

        $t3Start = $t2End->copy()->addDay();
        $t3End = $end->copy();

        $definitions = [
            ['name' => 'Term 1', 'sequence' => 1, 'starts_on' => $t1Start, 'ends_on' => $t1End],
            ['name' => 'Term 2', 'sequence' => 2, 'starts_on' => $t2Start, 'ends_on' => $t2End],
            ['name' => 'Term 3', 'sequence' => 3, 'starts_on' => $t3Start, 'ends_on' => $t3End],
        ];

        foreach ($definitions as $def) {
            Term::create([
                'school_id' => $year->school_id,
                'academic_year_id' => $year->id,
                'name' => $def['name'],
                'sequence' => $def['sequence'],
                'starts_on' => $def['starts_on'],
                'ends_on' => $def['ends_on'],
                'is_active' => true,
            ]);
        }
    }
}
