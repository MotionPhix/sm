<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'class_stream_assignment_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Scope enrollments by current school via the related class_stream_assignment
        static::addGlobalScope('tenant_enrollment', function (Builder $builder) {
            if (!app()->bound('currentSchool')) {
                return;
            }

            $schoolId = app('currentSchool')->id;
            $builder->whereHas('classroom', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        });
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(ClassStreamAssignment::class, 'class_stream_assignment_id');
    }

    /**
     * Get the school class through the classroom assignment.
     */
    public function schoolClass(): ?SchoolClass
    {
        return $this->classroom?->class;
    }

    /**
     * Get the stream through the classroom assignment.
     */
    public function stream(): ?Stream
    {
        return $this->classroom?->stream;
    }
}
