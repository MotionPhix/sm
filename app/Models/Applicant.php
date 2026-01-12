<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $fillable = [
        'school_id',
        'admission_cycle_id',
        'first_name',
        'last_name',
        'national_id',
        'date_of_birth',
        'gender',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function admissionCycle()
    {
        return $this->belongsTo(AdmissionCycle::class);
    }
}
