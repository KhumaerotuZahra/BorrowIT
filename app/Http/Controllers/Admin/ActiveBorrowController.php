<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActiveBorrowController extends Controller
{
    public function index(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $query = Borrowing::with(['user', 'asset']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('asset', fn($q2) => $q2->where('asset_name', 'like', "%{$search}%"));
            });
        }

        $borrowings = $query->whereIn('status', ['active', 'overdue', 'returned'])
            ->orderByRaw("FIELD(status, 'overdue', 'active', 'returned')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.active-borrows.index', compact('borrowings'));
    }

    public function markReturned(Request $request, Borrowing $borrowing)
    {
        $request->validate([
            'return_pic' => 'nullable|string',
            'return_notes' => 'nullable|string',
        ]);

        $borrowing->update([
            'status' => 'returned',
            'return_date' => Carbon::now(),
            'return_pic' => $request->return_pic,
            'return_notes' => $request->return_notes,
        ]);

        $borrowing->asset->increment('available_stock', $borrowing->quantity);

        Notification::send(
            $borrowing->user_id,
            'borrow_returned',
            'Asset Returned',
            "The asset {$borrowing->asset->asset_name} has been marked as returned. Thank you!",
            ['borrowing_id' => $borrowing->id]
        );

        return back()->with('success', 'Asset marked as returned successfully!');
    }
}
