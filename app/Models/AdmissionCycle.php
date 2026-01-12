<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionCycle extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'starts_at',
        'ends_at',
        'name',
        'is_active',
    ];

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
