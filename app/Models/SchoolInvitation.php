<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInvitation extends Model
{
    protected $fillable = [
        'school_id',
        'email',
        'name',
        'role_id',
        'token',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }
}
