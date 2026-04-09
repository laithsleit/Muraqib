<?php

namespace App\Actions\Admin;

use App\Models\User;

class UpdateUserAction
{
    public function execute(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        $user->syncRoles([$data['role']]);

        return $user;
    }
}
