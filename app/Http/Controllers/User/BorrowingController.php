<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $query = Borrowing::with('asset')
            ->where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', fn($q) => $q->where('asset_name', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->orderBy('created_at', 'desc')->paginate(10);
        $assets = Asset::where('available_stock', '>', 0)->get();

        return view('user.borrowings.index', compact('borrowings', 'assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date|after_or_equal:today',
            'due_date' => 'required|date|after:borrow_date',
            'purpose' => 'nullable|string|max:500',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        if ($asset->available_stock < $request->quantity) {
            return back()->with('error', 'Not enough stock available!')->withInput();
        }

        $borrowing = Borrowing::create([
            'user_id' => auth()->id(),
            'asset_id' => $request->asset_id,
            'quantity' => $request->quantity,
            'borrow_date' => $request->borrow_date,
            'due_date' => $request->due_date,
            'status' => 'pending',
            'purpose' => $request->purpose,
        ]);

        $asset->decrement('available_stock', $request->quantity);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::send(
                $admin->id,
                'new_request',
                'New Borrow Request',
                auth()->user()->name . " has requested to borrow {$asset->asset_name} (Qty: {$request->quantity}).",
                ['borrowing_id' => $borrowing->id]
            );
        }

        return redirect()->route('user.borrowings.index')->with('success', 'Borrow request submitted successfully!');
    }
}
