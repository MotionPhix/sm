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
            // Attendance
            'attendance.view',
            'attendance.record',
            'attendance.export',
            // Terms
            'terms.view',
            ],
            
            'teacher' => [
            'dashboard.view',
            'students.view',
            'classes.view',
            'subjects.view',
            'exams.view',
            'exams.enter-marks',
            // Attendance
            'attendance.view',
            'attendance.record',
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
                // Attendance (view)
                'attendance.view',
                // Terms (view)
                'terms.view',
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
                // Attendance (view/export optional for finance summaries)
                'attendance.view',
                'attendance.export',
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
