<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DepartmentsImport;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('name')->paginate(20);

        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create(['name' => $request->name]);

        return back()->with('success', 'Department added successfully!');
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update(['name' => $request->name]);

        return back()->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Department deleted successfully!');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new DepartmentsImport;
            Excel::import($import, $request->file('file'));

            $imported = $import->getImported();
            $skipped = $import->getSkipped();

            return back()->with('success', "Import complete! {$imported} departments added, {$skipped} skipped.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportTemplate()
    {
        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['name']);
            fputcsv($file, ['Information Technology']);
            fputcsv($file, ['Human Resources']);
            fclose($file);
        };

        return response()->streamDownload($callback, 'template_import_departments.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
