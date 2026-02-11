<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeScaleStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_scale_id',
        'min_percent',
        'max_percent',
        'grade',
        'comment',
        'ordering',
    ];

    protected function casts(): array
    {
        return [
            'min_percent' => 'integer',
            'max_percent' => 'integer',
            'ordering' => 'integer',
        ];
    }

    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(GradeScale::class);
    }
}
