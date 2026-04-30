<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetGroup;
use App\Imports\AssetsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('assetGroup');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_id', 'like', "%{$search}%")
                  ->orWhere('asset_number', 'like', "%{$search}%")
                  ->orWhere('asset_name', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('created_at', 'asc')->paginate(10);
        $assetGroups = AssetGroup::orderBy('group_name')->get();

        return view('admin.assets.index', compact('assets', 'assetGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_group_id' => 'required|exists:asset_groups,id',
            'asset_number' => 'required|string|max:30',
            'asset_name' => 'required|string|max:255',
            'available_stock' => 'required|integer|min:0',
        ]);

        $assetId = Asset::generateAssetId();

        Asset::create([
            'asset_id' => $assetId,
            'asset_group_id' => $request->asset_group_id,
            'asset_number' => $request->asset_number,
            'asset_name' => $request->asset_name,
            'total_stock' => $request->available_stock,
            'available_stock' => $request->available_stock,
        ]);

        return redirect()->route('admin.assets.index')->with('success', 'Asset created successfully! Asset ID: ' . $assetId);
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_group_id' => 'required|exists:asset_groups,id',
            'asset_number' => 'required|string|max:30',
            'asset_name' => 'required|string|max:255',
            'available_stock' => 'required|integer|min:0',
        ]);

        $asset->update([
            'asset_group_id' => $request->asset_group_id,
            'asset_number' => $request->asset_number,
            'asset_name' => $request->asset_name,
            'total_stock' => $request->available_stock,
            'available_stock' => $request->available_stock,
        ]);

        return redirect()->route('admin.assets.index')->with('success', 'Asset updated successfully!');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new AssetsImport();
            Excel::import($import, $request->file('file'));

            $msg = "Import complete! {$import->getImported()} assets imported.";
            if ($import->getSkipped() > 0) {
                $msg .= " {$import->getSkipped()} rows skipped (duplicate or missing data).";
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportTemplate()
    {
        $filename = 'template_import_assets.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['group_code', 'asset_number', 'asset_name', 'available_stock']);
            fputcsv($file, ['LTP', 'LAP-001', 'Laptop Dell XPS 15', 5]);
            fputcsv($file, ['MSE', 'MSE-001', 'Mouse Logitech M590', 10]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Asset $asset)
    {
        if ($asset->borrowings()->whereIn('status', ['active', 'overdue'])->exists()) {
            return back()->with('error', 'Cannot delete asset with active borrowings!');
        }

        $asset->delete();
        return redirect()->route('admin.assets.index')->with('success', 'Asset deleted successfully!');
    }
}
