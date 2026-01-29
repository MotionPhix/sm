<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped as TenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'term_id',
        'fee_item_id',
        'amount',
        'quantity',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function feeItem(): BelongsTo
    {
        return $this->belongsTo(FeeItem::class);
    }

    /**
     * Get total amount (amount * quantity)
     */
    public function getTotalAmount(): float
    {
        return (float) ($this->amount * $this->quantity);
    }

    /**
     * Format amount as currency
     */
    public function getFormattedAmount(): string
    {
        return 'MK ' . number_format($this->amount, 2);
    }

    /**
     * Format total amount as currency
     */
    public function getFormattedTotal(): string
    {
        return 'MK ' . number_format($this->getTotalAmount(), 2);
    }
}
