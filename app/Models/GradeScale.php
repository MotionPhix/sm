<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped as TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'description',
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

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(GradeScaleStep::class)->orderBy('ordering');
    }
}
