<?php

namespace App\Actions\Admin;

use App\Models\User;

class ToggleUserActiveAction
{
    public function execute(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);
        return $user;
    }
}
