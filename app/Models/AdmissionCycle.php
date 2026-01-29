<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantScoped as TenantScope;

class AdmissionCycle extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'starts_at',
        'ends_at',
        'name',
        'target_class',
        'max_intake',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
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

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
}
