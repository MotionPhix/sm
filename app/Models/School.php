<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name', 'code', 'email', 'phone', 'location', 'is_active'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_users')
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

    public function admins()
    {
        return $this->users()
            ->wherePivot('role_id', Role::where('name', 'admin')->first()->id);
    }
}
