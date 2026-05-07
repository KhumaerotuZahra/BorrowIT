<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AssetLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // filter action (optional)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(10);

        return view('admin.asset-logs.index', compact('logs'));
    }
}