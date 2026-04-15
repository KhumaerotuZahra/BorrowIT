<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\User;
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

        $assets = Asset::where('available_stock', '>', 0)->orderBy('asset_name')->get();
        $users = User::where('role', 'user')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.borrow-requests.index', compact(
            'borrowings', 'pendingCount', 'approvedCount', 'activeCount', 'assets', 'users'
        ));
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'asset_id' => 'required|exists:assets,id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
            'purpose' => 'nullable|string|max:500',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        if ($asset->available_stock < $request->quantity) {
            return back()->with('error', 'Not enough stock available!');
        }

        $borrowing = Borrowing::create([
            'user_id' => $request->user_id,
            'asset_id' => $request->asset_id,
            'quantity' => $request->quantity,
            'borrow_date' => $request->borrow_date,
            'due_date' => $request->due_date,
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        $asset->decrement('available_stock', $request->quantity);

        Notification::send(
            $request->user_id,
            'borrow_request',
            'Borrow Request Created',
            "A borrow request for {$asset->asset_name} has been created on your behalf by admin.",
            ['borrowing_id' => $borrowing->id]
        );

        return back()->with('success', 'Borrow request created successfully!');
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
            'borrow_date' => 'nullable|date',
            'handover_by' => 'required|string',
            'handover_notes' => 'nullable|string',
        ]);

        $data = [
            'status' => 'active',
            'handover_by' => $request->handover_by,
            'handover_date' => Carbon::now(),
            'handover_notes' => $request->handover_notes,
        ];

        if ($request->filled('borrow_date')) {
            $data['borrow_date'] = $request->borrow_date;
        }

        $borrowing->update($data);

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
