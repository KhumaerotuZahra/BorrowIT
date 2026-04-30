<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    private $imported = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        // Map common column name variations
        $name = $row['name'] ?? $row['nama'] ?? $row['full_name'] ?? $row['fullname'] ?? $row['employee_name'] ?? null;
        $email = $row['email'] ?? $row['email_address'] ?? null;
        $department = $row['department'] ?? $row['depart'] ?? $row['dept'] ?? $row['departemen'] ?? null;
        $role = $row['role'] ?? 'user';
        $employeeId = $row['employee_id'] ?? $row['employe'] ?? $row['emp_id'] ?? $row['nik'] ?? $row['id_karyawan'] ?? null;
        $status = $row['status'] ?? 'active';

        if (!$name || !$email) {
            $this->skipped++;
            return null;
        }

        // Skip if email already exists
        if (User::where('email', $email)->exists()) {
            $this->skipped++;
            return null;
        }

        // Normalize role
        $role = strtolower(trim($role));
        if (!in_array($role, ['admin', 'user'])) {
            $role = 'user';
        }

        // Normalize status
        $status = strtolower(trim($status));
        if (!in_array($status, ['active', 'inactive'])) {
            $status = 'active';
        }

        $this->imported++;

        return new User([
            'employee_id' => $employeeId,
            'name'        => $name,
            'email'       => $email,
            'password'    => Hash::make($row['password'] ?? 'password123'),
            'department'  => $department,
            'role'        => $role,
            'status'      => $status,
        ]);
    }

    public function rules(): array
    {
        return [];
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }
}
