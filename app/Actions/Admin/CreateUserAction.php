<?php

namespace App\Actions\Admin;

use App\Models\User;

class CreateUserAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'is_active' => true,
        ]);

        $user->addRole($data['role']);

        return $user;
    }
}
