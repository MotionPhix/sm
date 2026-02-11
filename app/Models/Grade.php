<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped as TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'assessment_plan_id',
        'class_stream_assignment_id',
        'score',
        'remarks',
        'entered_by',
        'entered_at',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'entered_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model) {
            if (empty($model->school_id) && app()->bound('currentSchool')) {
                $model->school_id = app('currentSchool')->id;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assessmentPlan(): BelongsTo
    {
        return $this->belongsTo(AssessmentPlan::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(ClassStreamAssignment::class, 'class_stream_assignment_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }
}
