<?php

namespace App\Actions\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteUserAction
{
    public function execute(User $user): array
    {
        if ($user->id === Auth::id()) {
            return ['success' => false, 'message' => 'You cannot delete your own account.'];
        }

        if ($user->hasRole('super_admin')) {
            return ['success' => false, 'message' => 'Cannot delete a super admin account.'];
        }

        $user->delete();

        return ['success' => true, 'message' => "User {$user->name} deleted."];
    }
}
