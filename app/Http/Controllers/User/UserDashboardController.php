<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Notification;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index()
    {
        Borrowing::checkAndUpdateOverdue();

        $userId = auth()->id();

        $activeBorrows = Borrowing::where('user_id', $userId)
            ->whereIn('status', ['active'])
            ->count();

        $overdueCount = Borrowing::where('user_id', $userId)
            ->where('status', 'overdue')
            ->count();

        $pendingRequests = Borrowing::where('user_id', $userId)
            ->where('status', 'pending')
            ->count();

        $myBorrowedAssets = Borrowing::with(['asset', 'assetGroup'])
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'overdue', 'approved'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('user.dashboard', compact(
            'activeBorrows',
            'overdueCount',
            'pendingRequests',
            'myBorrowedAssets',
            'notifications'
        ));
    }
}
