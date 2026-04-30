<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('employee_id','like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('employee_id', 'asc')->paginate(10);
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Operations', 'Engineering', 'Sales', 'Support'];

        return view('admin.users.index', compact('users', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|unique:users,employee_id',
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email', 'regex:/^[a-zA-Z0-9._%+-]+@ptbpi\.co\.id$/'],
            'password' => 'required|string|min:8',
            'department' => 'required|string',
            'role' => 'required|in:admin,user',
        ], [
            'email.regex' => 'Email must use the @ptbpi.co.id domain.',
        ]);

        User::create([
            'employee_id'=> $request->employee_id,
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
            'employee_id' => [
                'required', 
                Rule::unique('users', 'employee_id')->ignore($user->id),
            ],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email,' . $user->id, 'regex:/^[a-zA-Z0-9._%+-]+@ptbpi\.co\.id$/'],
            'department' => 'required|string',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
        ], [
            'email.regex' => 'Email must use the @ptbpi.co.id domain.',
        ]);

        $data = $request->only(['employee_id', 'name', 'email', 'department', 'role', 'status']);

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

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));

            $msg = "Import complete! {$import->getImported()} users imported.";
            if ($import->getSkipped() > 0) {
                $msg .= " {$import->getSkipped()} rows skipped (duplicate email or missing data).";
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportTemplate()
    {
        $callback = function () {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['employee_id', 'name', 'email', 'department', 'password']);
            fputcsv($file, ['EMP001', 'Budi Santoso', 'budi@gmail.com', 'IT', 'password123']);
            fputcsv($file, ['EMP002', 'Siti Rahayu', 'siti@gmail.com', 'HR', 'password123']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_import_users.csv', [
            'Content-Type' => 'text/csv',
        ]);
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
