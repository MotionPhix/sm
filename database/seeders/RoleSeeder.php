<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'System Administrator'],
            ['name' => 'admin', 'label' => 'School Administrator'],
            ['name' => 'head_teacher', 'label' => 'Head Teacher'],
            ['name' => 'teacher', 'label' => 'Teacher'],
            ['name' => 'accountant', 'label' => 'Accountant'],
            ['name' => 'registrar', 'label' => 'Registrar'],
            ['name' => 'bursar', 'label' => 'Bursar'],
            ['name' => 'parent', 'label' => 'Parent/Guardian'],
            ['name' => 'student', 'label' => 'Student'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
