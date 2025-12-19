<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $map = [

            'admin' => [
                '*',
            ],

            'head_teacher' => [
                'dashboard.view',
                'students.view',
                'teachers.view',
                'classes.view',
                'subjects.view',
                'exams.view',
                'exams.view-marks',
                'grading.view',
                'reports.view',
                'reports.generate',
            ],

            'teacher' => [
                'dashboard.view',
                'students.view',
                'classes.view',
                'subjects.view',
                'exams.view',
                'exams.enter-marks',
            ],

            'registrar' => [
                'dashboard.view',
                'students.view',
                'students.create',
                'students.edit',
                'students.promote',
                'guardians.view',
                'guardians.create',
                'guardians.edit',
            ],

            'bursar' => [
                'dashboard.view',
                'fees.view',
                'fees.create',
                'fees.edit',
                'payments.view',
                'payments.create',
                'payments.generate-receipt',
                'reports.view',
            ],

            'parent' => [
                'dashboard.view',
                'students.view',
                'reports.view',
            ],

            'student' => [
                'dashboard.view',
                'reports.view',
            ],
        ];

        foreach ($map as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) continue;

            if (in_array('*', $permissions, true)) {
                $role->permissions()->sync(
                    Permission::pluck('id')
                );
                continue;
            }

            $role->permissions()->sync(
                Permission::whereIn('name', $permissions)->pluck('id')
            );
        }
    }
}
