<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\EmailHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActiveBorrowController extends Controller
{
    public function index(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        // Show only child borrowings (individual assets) that are active/overdue/returned
        $query = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->whereNotNull('parent_borrowing_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('asset', fn($q2) => $q2->where('asset_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['active', 'overdue', 'returned']);
        }

        $borrowings = $query
            ->orderByRaw("FIELD(status, 'overdue', 'active', 'returned')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.active-borrows.index', compact('borrowings', 'admins'));
    }

    public function markReturned(Request $request, Borrowing $borrowing)
    {
        $request->validate([
            'return_pic' => 'required|string',
            'return_notes' => 'nullable|string',
        ]);

        $borrowing->update([
            'status' => 'returned',
            'return_date' => Carbon::now(),
            'return_pic' => $request->return_pic,
            'return_notes' => $request->return_notes,
        ]);

        if ($borrowing->asset) {
            $borrowing->asset->increment('available_stock', 1);
        }

        // Check if all child borrowings for parent are returned
        if ($borrowing->parent_borrowing_id) {
            $parent = $borrowing->parentBorrowing;
            $allReturned = $parent->childBorrowings()->where('status', '!=', 'returned')->count() === 0;
            if ($allReturned) {
                $parent->update([
                    'status' => 'returned',
                    'return_date' => Carbon::now(),
                    'return_pic' => $request->return_pic,
                ]);
            }
        }

        $assetName = $borrowing->asset->asset_name ?? 'Asset';
        $msg = "The asset {$assetName} has been marked as returned. Thank you!";
        Notification::send(
            $borrowing->user_id,
            'borrow_returned',
            'Asset Returned',
            $msg,
            ['borrowing_id' => $borrowing->id]
        );

        EmailHelper::sendBorrowEmail($borrowing->user_id, 'returned', 'Asset Returned', $msg, [
            'Asset' => $assetName,
            'Return Date' => now()->format('d M Y'),
            'Return PIC' => $request->return_pic ?? '-',
        ]);

        return back()->with('success', 'Asset marked as returned successfully!');
    }
}
