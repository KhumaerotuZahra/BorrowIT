<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetGroup;
use Illuminate\Http\Request;

class AssetGroupController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetGroup::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('group_code', 'like', "%{$search}%")
                  ->orWhere('group_name', 'like', "%{$search}%");
            });
        }

        $groups = $query->orderBy('created_at', 'asc')->paginate(10);

        return view('admin.asset-groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_code' => 'required|string|max:30|unique:asset_groups,group_code',
            'group_name' => 'required|string|max:255',
        ]);

        AssetGroup::create($request->only('group_code', 'group_name'));

        return redirect()->route('admin.asset-groups.index')->with('success', 'Asset Group created successfully!');
    }

    public function update(Request $request, AssetGroup $assetGroup)
    {
        $request->validate([
            'group_code' => 'required|string|max:30|unique:asset_groups,group_code,' . $assetGroup->id,
            'group_name' => 'required|string|max:255',
        ]);

        $assetGroup->update($request->only('group_code', 'group_name'));

        return redirect()->route('admin.asset-groups.index')->with('success', 'Asset Group updated successfully!');
    }

    public function destroy(AssetGroup $assetGroup)
    {
        if ($assetGroup->assets()->count() > 0) {
            return back()->with('error', 'Cannot delete group that has assets!');
        }

        $assetGroup->delete();
        return redirect()->route('admin.asset-groups.index')->with('success', 'Asset Group deleted successfully!');
    }
}
