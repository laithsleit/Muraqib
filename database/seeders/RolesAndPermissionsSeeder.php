<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage-users',
            'manage-subjects',
            'manage-quizzes',
            'view-reports',
            'take-quizzes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(['manage-users']);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions(['manage-subjects', 'manage-quizzes', 'view-reports']);

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->syncPermissions(['take-quizzes']);
    }
}
