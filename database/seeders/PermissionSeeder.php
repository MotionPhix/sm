<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'schools.view', 'schools.create', 'schools.edit', 'schools.delete',

            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.assign',

            'academic-years.view', 'academic-years.create', 'academic-years.edit', 'academic-years.delete',
            'classes.view', 'classes.create', 'classes.edit', 'classes.delete',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',

            'students.view', 'students.create', 'students.edit', 'students.delete', 'students.promote',
            'guardians.view', 'guardians.create', 'guardians.edit', 'guardians.delete',

            'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete', 'teachers.assign-classes',

            'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
            'exams.enter-marks', 'exams.view-marks',

            'grading.view', 'grading.configure',

            'fees.view', 'fees.create', 'fees.edit', 'fees.delete',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete', 'payments.generate-receipt',

            'reports.view', 'reports.generate', 'reports.print',

            'dashboard.view',
            'analytics.view',
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ], [
                'label' => str($permission)->replace('.', ' ')->title(),
            ]);
        }
    }
}
