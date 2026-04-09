<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\User;
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

        $itEmployees = User::where('department', 'IT')->orderBy('name')->get();

        return view('admin.active-borrows.index', compact('borrowings', 'itEmployees'));
    }

    public function update(Request $request, Borrowing $borrowing)
    {
        $request->validate([
            'handover_by' => 'nullable|string',
            'status' => 'nullable|in:active,overdue,returned',
            'return_pic' => 'nullable|string',
        ]);

        $data = [];

        if ($request->filled('handover_by')) {
            $data['handover_by'] = $request->handover_by;
        }

        if ($request->filled('return_pic')) {
            $data['return_pic'] = $request->return_pic;
        }

        if ($request->filled('status') && $request->status !== $borrowing->status) {
            $data['status'] = $request->status;

            if ($request->status === 'returned' && !$borrowing->return_date) {
                $data['return_date'] = Carbon::now();
                $borrowing->asset->increment('available_stock', $borrowing->quantity);

                Notification::send(
                    $borrowing->user_id,
                    'borrow_returned',
                    'Asset Returned',
                    "The asset {$borrowing->asset->asset_name} has been marked as returned. Thank you!",
                    ['borrowing_id' => $borrowing->id]
                );
            }
        }

        if (!empty($data)) {
            $borrowing->update($data);
        }

        return back()->with('success', 'Borrowing updated successfully!');
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
