<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_id', 'like', "%{$search}%")
                  ->orWhere('asset_number', 'like', "%{$search}%")
                  ->orWhere('asset_name', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('admin.assets.index', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_number' => 'required|string|max:30',
            'asset_name' => 'required|string|max:255',
            'available_stock' => 'required|integer|min:0',
        ]);

        $assetId = Asset::generateAssetId();

        Asset::create([
            'asset_id' => $assetId,
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
            'asset_number' => 'required|string|max:30',
            'asset_name' => 'required|string|max:255',
            'available_stock' => 'required|integer|min:0',
        ]);

        $asset->update([
            'asset_number' => $request->asset_number,
            'asset_name' => $request->asset_name,
            'total_stock' => $request->available_stock,
            'available_stock' => $request->available_stock,
        ]);

        return redirect()->route('admin.assets.index')->with('success', 'Asset updated successfully!');
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
