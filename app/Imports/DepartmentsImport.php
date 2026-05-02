<?php

namespace App\Imports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DepartmentsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    private $imported = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        $name = $row['name'] ?? $row['nama'] ?? $row['department'] ?? $row['departemen'] ?? null;

        if (!$name) {
            $this->skipped++;
            return null;
        }

        $name = trim($name);

        if (Department::where('name', $name)->exists()) {
            $this->skipped++;
            return null;
        }

        $this->imported++;

        return new Department([
            'name' => $name,
        ]);
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
