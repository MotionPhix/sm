<?php

namespace App\Models;

use App\Services\TimetableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_stream_assignment_id',
        'subject_id',
        'schedule_data',
    ];

    protected $casts = [
        'schedule_data' => 'array',
    ];

    protected static function booted(): void
    {
        // Scope by current school through the classroom (ClassStreamAssignment) relationship
        static::addGlobalScope('tenant_teacher_assignment', function (Builder $builder) {
            if (! app()->bound('currentSchool')) {
                return;
            }

            $schoolId = app('currentSchool')->id;
            $builder->whereHas('classroom', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        });

        static::saving(function (self $model) {
            return $model->validateSchedule();
        });
    }

    /**
     * Validates the schedule to prevent clashes before saving
     */
    private function validateSchedule(): bool
    {
        if (! $this->schedule_data || empty($this->schedule_data)) {
            return true; // No schedule data to validate
        }

        $timetableService = app(TimetableService::class);

        $teacher = $this->teacher;
        $assignment = $this->classroom;

        if (! $teacher || ! $assignment) {
            return true; // Can't validate without teacher or assignment
        }

        $clashes = $timetableService->validateAssignmentClash(
            $teacher,
            $assignment,
            $this->schedule_data,
            $this->id // Pass ID to exclude this assignment from clash checks
        );

        // If there are clashes, prevent saving
        if (! empty($clashes['teacher_clashes']) || ! empty($clashes['class_clashes'])) {
            throw new \Exception('Schedule conflict detected: This assignment conflicts with existing schedules.');
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

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
