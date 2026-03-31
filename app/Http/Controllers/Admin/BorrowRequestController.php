<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BorrowRequestController extends Controller
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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->whereIn('status', ['pending', 'approved', 'active'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'active')")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pendingCount = Borrowing::where('status', 'pending')->count();
        $approvedCount = Borrowing::where('status', 'approved')->count();
        $activeCount = Borrowing::whereIn('status', ['active', 'overdue'])->count();

        return view('admin.borrow-requests.index', compact(
            'borrowings', 'pendingCount', 'approvedCount', 'activeCount'
        ));
    }

    public function approve(Borrowing $borrowing)
    {
        $borrowing->update([
            'status' => 'approved',
            'approved_date' => Carbon::now(),
            'approved_by' => auth()->id(),
        ]);

        Notification::send(
            $borrowing->user_id,
            'borrow_approved',
            'Borrow Request Approved',
            "Your request to borrow {$borrowing->asset->asset_name} has been approved.",
            ['borrowing_id' => $borrowing->id]
        );

        return back()->with('success', 'Request approved successfully!');
    }

    public function reject(Borrowing $borrowing)
    {
        $borrowing->update(['status' => 'rejected']);

        $borrowing->asset->increment('available_stock', $borrowing->quantity);

        Notification::send(
            $borrowing->user_id,
            'borrow_rejected',
            'Borrow Request Rejected',
            "Your request to borrow {$borrowing->asset->asset_name} has been rejected.",
            ['borrowing_id' => $borrowing->id]
        );

        return back()->with('success', 'Request rejected.');
    }

    public function handover(Request $request, Borrowing $borrowing)
    {
        $request->validate([
            'handover_by' => 'required|string',
            'handover_notes' => 'nullable|string',
        ]);

        $borrowing->update([
            'status' => 'active',
            'handover_by' => $request->handover_by,
            'handover_date' => Carbon::now(),
            'handover_notes' => $request->handover_notes,
        ]);

        Notification::send(
            $borrowing->user_id,
            'borrow_handover',
            'Asset Handed Over',
            "The asset {$borrowing->asset->asset_name} has been handed over to you. Due date: {$borrowing->due_date->format('d M Y')}.",
            ['borrowing_id' => $borrowing->id]
        );

        return back()->with('success', 'Asset handed over successfully!');
    }
}
