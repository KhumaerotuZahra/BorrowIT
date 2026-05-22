<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetGroup;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\EmailHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowRequestController extends Controller
{
    public function index(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $query = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->whereNull('parent_borrowing_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('assetGroup', fn($q2) => $q2->where('group_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->whereIn('status', ['pending', 'approved', 'rejected'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $pendingCount = Borrowing::where('status', 'pending')->whereNull('parent_borrowing_id')->count();
        $approvedCount = Borrowing::where('status', 'approved')->whereNull('parent_borrowing_id')->count();

        $assetGroups = AssetGroup::orderBy('group_name')->get();
        $users = User::where('status', 'active')
            ->orderBy('name')
            ->get();

        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.borrow-requests.index', compact(
            'borrowings', 'pendingCount', 'approvedCount', 'assetGroups', 'users', 'admins'
        ));
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'asset_group_id' => 'required|exists:asset_groups,id',
            'quantity' => 'required|integer|min:1',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after:borrow_date',
        ]);

        $group = AssetGroup::findOrFail($request->asset_group_id);
        $availableStock = $group->totalAvailableStock();

        if ($availableStock < $request->quantity) {
            return back()->with('error', "Not enough stock in group '{$group->group_name}'! Available: {$availableStock}");
        }

        $borrowing = Borrowing::create([
            'user_id' => $request->user_id,
            'asset_group_id' => $request->asset_group_id,
            'quantity' => $request->quantity,
            'borrow_date' => $request->borrow_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        $user = User::find($request->user_id);
        $userMsg = "A borrow request for {$group->group_name} (qty: {$request->quantity}) has been created on your behalf by admin.";
        Notification::send(
            $request->user_id,
            'borrow_request',
            'Borrow Request Created',
            $userMsg,
            ['borrowing_id' => $borrowing->id]
        );

        EmailHelper::sendBorrowEmail($request->user_id, 'borrow_request', 'Borrow Request Created', $userMsg, [
            'Asset Group' => $group->group_name,
            'Quantity' => $request->quantity,
            'Borrow Date' => $request->borrow_date,
            'Due Date' => $request->due_date,
        ]);

        return back()->with('success', 'Borrow request created successfully!');
    }

    public function approve(Borrowing $borrowing)
    {
        $borrowing->update([
            'status' => 'approved',
            'approved_date' => Carbon::now(),
            'approved_by' => auth()->id(),
        ]);

        $groupName = $borrowing->assetGroup->group_name ?? 'Asset';
        $msg = "Your request to borrow {$groupName} (qty: {$borrowing->quantity}) has been approved.";
        Notification::send(
            $borrowing->user_id,
            'borrow_approved',
            'Borrow Request Approved',
            $msg,
            ['borrowing_id' => $borrowing->id]
        );

        EmailHelper::sendBorrowEmail($borrowing->user_id, 'borrow_approved', 'Borrow Request Approved', $msg, [
            'Asset Group' => $groupName,
            'Quantity' => $borrowing->quantity,
            'Borrow Date' => $borrowing->borrow_date->format('d M Y'),
            'Due Date' => $borrowing->due_date->format('d M Y'),
        ]);

        $borrowerName = $borrowing->user->name ?? 'User';
        $adminMsg = "{$borrowerName}'s request to borrow {$groupName} (qty: {$borrowing->quantity}) has been approved.";
        $this->notifyAdmins('borrow_approved', 'Borrow Request Approved', $adminMsg, [
            'Borrower' => $borrowerName,
            'Asset Group' => $groupName,
            'Quantity' => $borrowing->quantity,
        ], ['borrowing_id' => $borrowing->id]);

        return back()->with('success', 'Request approved successfully!');
    }

    public function reject(Request $request, Borrowing $borrowing)
    {
        $rejectNotes = $request->reject_notes ?? '';
        $borrowing->update([
            'status' => 'rejected',
            'notes' => $rejectNotes,
        ]);

        $groupName = $borrowing->assetGroup->group_name ?? 'Asset';
        $reasonText = $rejectNotes ? " Reason: {$rejectNotes}" : '';
        $msg = "Your request to borrow {$groupName} has been rejected.{$reasonText}";
        Notification::send(
            $borrowing->user_id,
            'borrow_rejected',
            'Borrow Request Rejected',
            $msg,
            ['borrowing_id' => $borrowing->id]
        );

        EmailHelper::sendBorrowEmail($borrowing->user_id, 'borrow_rejected', 'Borrow Request Rejected', $msg, [
            'Asset Group' => $groupName,
            'Quantity' => $borrowing->quantity,
            'Reason' => $rejectNotes ?: '-',
        ]);

        $borrowerName = $borrowing->user->name ?? 'User';
        $adminMsg = "{$borrowerName}'s request to borrow {$groupName} has been rejected." . ($rejectNotes ? " Reason: {$rejectNotes}" : '');
        $this->notifyAdmins('borrow_rejected', 'Borrow Request Rejected', $adminMsg, [
            'Borrower' => $borrowerName,
            'Asset Group' => $groupName,
            'Reason' => $rejectNotes ?: '-',
        ], ['borrowing_id' => $borrowing->id]);

        return back()->with('success', 'Request rejected.');
    }

    public function cancel(Borrowing $borrowing)
    {
        $borrowing->update(['status' => 'cancelled']);

        $groupName = $borrowing->assetGroup->group_name ?? 'Asset';
        $msg = "Your approved request to borrow {$groupName} has been cancelled by admin.";
        Notification::send(
            $borrowing->user_id,
            'borrow_cancelled',
            'Borrow Request Cancelled',
            $msg,
            ['borrowing_id' => $borrowing->id]
        );

        EmailHelper::sendBorrowEmail($borrowing->user_id, 'borrow_cancelled', 'Borrow Request Cancelled', $msg, [
            'Asset Group' => $groupName,
            'Quantity' => $borrowing->quantity,
        ]);

        $borrowerName = $borrowing->user->name ?? 'User';
        $adminMsg = "{$borrowerName}'s approved request to borrow {$groupName} has been cancelled.";
        $this->notifyAdmins('borrow_cancelled', 'Borrow Request Cancelled', $adminMsg, [
            'Borrower' => $borrowerName,
            'Asset Group' => $groupName,
            'Quantity' => $borrowing->quantity,
        ], ['borrowing_id' => $borrowing->id]);

        return back()->with('success', 'Request cancelled.');
    }

    // Get available assets for a group (API for handover modal)
    public function getGroupAssets(AssetGroup $assetGroup)
    {
        $assets = Asset::where('asset_group_id', $assetGroup->id)
            ->where('available_stock', '>', 0)
            ->where('condition', 'good')
            ->orderBy('asset_name')
            ->get(['id', 'asset_name', 'asset_number', 'available_stock']);

        return response()->json($assets);
    }

    public function handover(Request $request, Borrowing $borrowing)
{
    $request->validate([
        'borrow_date' => 'nullable|date',
        'handover_by' => 'required|string',
        'handover_notes' => 'nullable|string',
        'asset_ids' => 'required|array|min:1',
        'asset_ids.*' => 'required|exists:assets,id',
    ]);

    $quantity = $borrowing->quantity;
    $assetIds = $request->asset_ids;

    if (count($assetIds) != $quantity) {
        return back()->with('error', "Please select exactly {$quantity} asset(s).");
    }

    // Prevent duplicate asset selection
    if (count($assetIds) !== count(array_unique($assetIds))) {
        return back()->with('error', 'Duplicate asset selected. Please select different assets.');
    }

    $borrowDate = $request->filled('borrow_date')
        ? $request->borrow_date
        : $borrowing->borrow_date;

    DB::beginTransaction();

    try {

        // Create individual borrowing records
        foreach ($assetIds as $assetId) {

            $asset = Asset::findOrFail($assetId);

            if ($asset->available_stock < 1) {
                throw new \Exception("Asset '{$asset->asset_name}' is no longer available!");
            }

            Borrowing::create([
                'user_id' => $borrowing->user_id,
                'asset_id' => $assetId,
                'asset_group_id' => $borrowing->asset_group_id,
                'quantity' => 1,
                'borrow_date' => $borrowDate,
                'due_date' => $borrowing->due_date,
                'status' => 'active',
                'approved_date' => $borrowing->approved_date,
                'approved_by' => $borrowing->approved_by,
                'handover_by' => $request->handover_by,
                'handover_date' => Carbon::now(),
                'handover_notes' => $request->handover_notes,
                'parent_borrowing_id' => $borrowing->id,
            ]);

            $asset->decrement('available_stock', 1);
        }

        // Update parent request
        $borrowing->update([
            'status' => 'active',
            'handover_by' => $request->handover_by,
            'handover_date' => Carbon::now(),
            'handover_notes' => $request->handover_notes,
            'borrow_date' => $borrowDate,
        ]);

        DB::commit();

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with('error', $e->getMessage());
    }

    $groupName = $borrowing->assetGroup->group_name ?? 'Asset';

    $msg = "The asset(s) {$groupName} (qty: {$quantity}) have been handed over to you. Due: {$borrowing->due_date->format('d M Y')}.";

    Notification::send(
        $borrowing->user_id,
        'borrow_handover',
        'Asset Handed Over',
        $msg,
        ['borrowing_id' => $borrowing->id]
    );

    EmailHelper::sendBorrowEmail(
        $borrowing->user_id,
        'borrow_handover',
        'Asset Handed Over',
        $msg,
        [
            'Asset Group' => $groupName,
            'Quantity' => $quantity,
            'Borrow Date' => $borrowDate,
            'Due Date' => $borrowing->due_date->format('d M Y'),
            'Handover By' => $request->handover_by,
        ]
    );

    $borrowerName = $borrowing->user->name ?? 'User';

    $adminMsg = "Asset(s) {$groupName} (qty: {$quantity}) have been handed over to {$borrowerName}. Due: {$borrowing->due_date->format('d M Y')}.";

    $this->notifyAdmins(
        'borrow_handover',
        'Asset Handed Over',
        $adminMsg,
        [
            'Borrower' => $borrowerName,
            'Asset Group' => $groupName,
            'Quantity' => $quantity,
            'Due Date' => $borrowing->due_date->format('d M Y'),
            'Handover By' => $request->handover_by,
        ],
        ['borrowing_id' => $borrowing->id]
    );

    return back()->with('success', 'Asset(s) handed over successfully!');
}

    /**
     * Send web notification + email to all admins.
     */
    protected function notifyAdmins(string $type, string $title, string $body, array $details = [], array $data = []): void
    {
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            Notification::send($admin->id, $type, $title, $body, $data);
            EmailHelper::sendBorrowEmail($admin->id, $type, $title, $body, $details);
        }
    }
}
