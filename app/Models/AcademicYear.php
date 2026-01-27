<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Term;
use App\Models\Concerns\TenantScoped as TenantScope;

class AcademicYear extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'starts_at',
        'ends_at',
        'is_current',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        // Ensure school_id is set for new records if missing
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

    public function admissionCycles(): HasMany
    {
        return $this->hasMany(AdmissionCycle::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class)->orderBy('sequence');
    }

    public function isPast(): bool
    {
        return ! $this->is_current;
    }
}
