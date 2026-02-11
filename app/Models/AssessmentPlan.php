<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\TenantScoped as TenantScope;

class AssessmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'academic_year_id', 'term_id', 'subject_id', 'name', 'ordering', 'max_score', 'weight', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $model) {
            if (empty($model->school_id) && app()->bound('currentSchool')) {
                $model->school_id = app('currentSchool')->id;
            }
        });
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function term(): BelongsTo { return $this->belongsTo(Term::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
