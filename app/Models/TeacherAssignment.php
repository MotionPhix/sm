<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\TenantScoped as TenantScope;
use App\Services\TimetableService;
use App\Models\AcademicYear;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'user_id',
        'class_stream_assignment_id',
        'school_id',
        'academic_year_id',
        'schedule_data',
    ];

    protected $casts = [
        'schedule_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model) {
            if (empty($model->school_id) && app()->bound('currentSchool')) {
                $model->school_id = app('currentSchool')->id;
            }

            // Default academic year to school's current year if provided
            if (empty($model->academic_year_id) && app()->bound('currentSchool')) {
                $currentYearId = AcademicYear::where('school_id', app('currentSchool')->id)
                    ->where('is_current', true)
                    ->value('id');
                if ($currentYearId) {
                    $model->academic_year_id = $currentYearId;
                }
            }
        });
        
        static::saving(function(self $model) {
            return $model->validateSchedule();
        });
    }

    /**
     * Validates the schedule to prevent clashes before saving
     */
    private function validateSchedule(): bool
    {
        if (!$this->schedule_data || empty($this->schedule_data)) {
            return true; // No schedule data to validate
        }

        $timetableService = app(TimetableService::class);
        
        $teacher = $this->teacher;
        $assignment = $this->classroom;
        
        if (!$teacher || !$assignment) {
            return true; // Can't validate without teacher or assignment
        }

        $clashes = $timetableService->validateAssignmentClash(
            $teacher,
            $assignment,
            $this->schedule_data,
            $this->id // Pass ID to exclude this assignment from clash checks
        );

        // If there are clashes, prevent saving
        if (!empty($clashes['teacher_clashes']) || !empty($clashes['class_clashes'])) {
            throw new \Exception("Schedule conflict detected: This assignment conflicts with existing schedules.");
        }

        return true;
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(ClassStreamAssignment::class, 'class_stream_assignment_id');
    }
}