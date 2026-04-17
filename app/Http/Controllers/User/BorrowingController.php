<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AssetGroup;
use App\Models\Borrowing;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $query = Borrowing::with(['asset', 'assetGroup'])
            ->where('user_id', auth()->id())
            ->whereNull('parent_borrowing_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('assetGroup', fn($q2) => $q2->where('group_name', 'like', "%{$search}%"))
                  ->orWhereHas('asset', fn($q2) => $q2->where('asset_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->orderBy('created_at', 'desc')->paginate(10);
        $assetGroups = AssetGroup::orderBy('group_name')->get();

        return view('user.borrowings.index', compact('borrowings', 'assetGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_group_id' => 'required|exists:asset_groups,id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after:borrow_date',
        ]);

        $group = AssetGroup::findOrFail($request->asset_group_id);
        $availableStock = $group->totalAvailableStock();

        if ($availableStock < $request->quantity) {
            return back()->with('error', "Not enough stock in '{$group->group_name}'! Available: {$availableStock}")->withInput();
        }

        $borrowing = Borrowing::create([
            'user_id' => auth()->id(),
            'asset_group_id' => $request->asset_group_id,
            'quantity' => $request->quantity,
            'borrow_date' => $request->borrow_date,
            'due_date' => $request->due_date,
            'status' => 'pending',
        ]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                'new_request',
                'New Borrow Request',
                auth()->user()->name . " has requested to borrow {$group->group_name} (Qty: {$request->quantity}).",
                ['borrowing_id' => $borrowing->id]
            );
        }

        return redirect()->route('user.borrowings.index')->with('success', 'Borrow request submitted successfully!');
    }
}
