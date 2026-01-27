<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScoped implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only apply when currentSchool is bound and model has school_id
        if (! app()->bound('currentSchool')) {
            return;
        }

        // Guard: only apply if table has school_id
        $table = $model->getTable();
        // We cannot introspect columns cheaply here; rely on convention that models using this scope have school_id
        $schoolId = app('currentSchool')->id;
        $builder->where($table . '.school_id', '=', $schoolId);
    }
}
