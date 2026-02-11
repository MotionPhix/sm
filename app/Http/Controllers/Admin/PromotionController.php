<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $school = $request->user()->activeSchool;

        $activeYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $classes = SchoolClass::where('school_id', $school->id)
            ->orderBy('order')
            ->get()
            ->map(function ($class) use ($school) {
                $count = Student::where('school_id', $school->id)
                    ->whereHas('currentClassroom', function ($q) use ($class) {
                        $q->where('school_class_id', $class->id);
                    })
                    ->count();

                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'order' => $class->order,
                    'student_count' => $count,
                ];
            });

        return Inertia::render('admin/promotion/Index', [
            'activeYear' => $activeYear ? [
                'id' => $activeYear->id,
                'name' => $activeYear->name,
            ] : null,
            'classes' => $classes,
        ]);
    }
}
