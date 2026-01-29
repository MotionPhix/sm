<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped as TenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'code',
        'category',
        'is_mandatory',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
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

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    /**
     * Category options for UI selection
     */
    public static function categories(): array
    {
        return [
            'tuition' => 'Tuition',
            'exam' => 'Exam Fees',
            'development' => 'Development Levy',
            'extra_curriculum' => 'Extra-Curriculum',
            'other' => 'Other',
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabel(): string
    {
        return self::categories()[$this->category] ?? $this->category;
    }
}
