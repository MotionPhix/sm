<?php

namespace App\Services;

use App\Models\ClassStreamAssignment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentPromotionService
{
    public function promote(Student $student, ClassStreamAssignment $nextClass)
    {
        DB::transaction(function () use ($student, $nextClass) {
            $student->enrollments()
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $student->enrollments()->create([
                'class_stream_assignment_id' => $nextClass->id,
                'is_active' => true,
            ]);
        });
    }
}
