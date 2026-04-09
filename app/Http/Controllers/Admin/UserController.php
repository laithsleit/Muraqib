<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\CreateUserAction;
use App\Actions\Admin\DeleteUserAction;
use App\Actions\Admin\ToggleUserActiveAction;
use App\Actions\Admin\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->latest();

        if ($request->query('filter') === 'teachers') {
            $query->teachers();
        } elseif ($request->query('filter') === 'students') {
            $query->students();
        }

        $users = $query->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request, CreateUserAction $action)
    {
        $action->execute($request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot edit the super admin account.');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $action)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot edit the super admin account.');
        }

        $action->execute($user, $request->validated());

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function toggleActive(User $user, ToggleUserActiveAction $action)
    {
        $action->execute($user);
        $status = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$user->name} has been {$status}.");
    }

    public function destroy(User $user, DeleteUserAction $action)
    {
        $result = $action->execute($user);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
