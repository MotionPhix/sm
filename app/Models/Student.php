<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped as TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'applicant_id',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'admission_date',
        'national_id',
        'current_class_stream_assignment_id',
        'withdrawn_at',
        'withdrawal_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'withdrawn_at' => 'date',
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

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function activeEnrollment(): HasOne
    {
        return $this->hasOne(StudentEnrollment::class)
            ->where('is_active', true);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function currentClassroom(): BelongsTo
    {
        return $this->belongsTo(ClassStreamAssignment::class, 'current_class_stream_assignment_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
