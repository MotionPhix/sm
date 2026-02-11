<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'is_active',
        'activated_at',
        'type', // primary, secondary, private, etc
        'district',
        'country', // Malawi, Zambia, Tanzania, etc
        'logo_path',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot('role_id', 'is_active')
            ->withTimestamps();
    }

    public function settings()
    {
        return $this->hasOne(SchoolSetting::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function currentAcademicYear()
    {
        return $this->hasOne(AcademicYear::class)->where('is_current', true);
    }

    public function admissionCycles()
    {
        return $this->hasMany(AdmissionCycle::class);
    }

    public function admins()
    {
        return $this->users()
            ->whereHas('schools', function ($q) {
                $q->where('role_id', Role::where('name', Role::ADMIN)->value('id'));
            });
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function feeItems()
    {
        return $this->hasMany(FeeItem::class);
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function applicants()
    {
        return $this->hasMany(Applicant::class);
    }
}
