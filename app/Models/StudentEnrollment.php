<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_id',
        'class_stream_assignment_id',
        'is_active',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(ClassStreamAssignment::class);
    }
}
