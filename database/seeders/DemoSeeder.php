<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@quiz.com'],
            [
                'name' => 'Dr. Sarah Johnson',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $teacher->addRole('teacher');

        $student1 = User::firstOrCreate(
            ['email' => 'student1@quiz.com'],
            [
                'name' => 'Ahmed Al-Rashid',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $student1->addRole('student');

        $student2 = User::firstOrCreate(
            ['email' => 'student2@quiz.com'],
            [
                'name' => 'Lena Fischer',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $student2->addRole('student');
    }
}
