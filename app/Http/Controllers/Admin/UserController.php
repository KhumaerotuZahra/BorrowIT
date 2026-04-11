<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'asc')->paginate(10);
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations', 'Engineering', 'Sales', 'Support'];

        return view('admin.users.index', compact('users', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email', 'regex:/^[a-zA-Z0-9._%+-]+@ptbpi\.co\.id$/'],
            'password' => 'required|string|min:8',
            'department' => 'required|string',
            'role' => 'required|in:admin,user',
        ], [
            'email.regex' => 'Email must use the @ptbpi.co.id domain.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'department' => $request->department,
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email,' . $user->id, 'regex:/^[a-zA-Z0-9._%+-]+@ptbpi\.co\.id$/'],
            'department' => 'required|string',
            'role' => 'required|in:admin,user',
        ], [
            'email.regex' => 'Email must use the @ptbpi.co.id domain.',
        ]);

        $data = $request->only(['name', 'email', 'department', 'role']);

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function resetPassword(User $user)
    {
        $newPassword = Str::random(10);
        $user->update(['password' => $newPassword]);

        Notification::send(
            $user->id,
            'password_reset',
            'Password Reset by Admin',
            "Your password has been reset by admin. New password: {$newPassword}. Please change it after login.",
            ['user_id' => $user->id]
        );

        return back()->with('success', "Password reset for {$user->name}. New password: {$newPassword}");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
    }
}
