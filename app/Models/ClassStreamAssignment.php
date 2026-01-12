<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStreamAssignment extends Model
{
    protected $fillable = [
        'school_id',
        'academic_year_id',
        'school_class_id',
        'class_stream_id',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }
}
