<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const HEAD_TEACHER = 'head_teacher';
    public const TEACHER = 'teacher';
    public const ACCOUNTANT = 'accountant';
    public const REGISTRAR = 'registrar';
    public const BURSAR = 'bursar';
    public const PARENT = 'parent';
    public const STUDENT = 'student';

    protected $fillable = ['name', 'label'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

}
