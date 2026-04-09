<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
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

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(['manage-users']);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions(['manage-subjects', 'manage-quizzes', 'view-reports']);

        $student = Role::firstOrCreate(['name' => 'student']);
        $student->syncPermissions(['take-quizzes']);
    }
}
