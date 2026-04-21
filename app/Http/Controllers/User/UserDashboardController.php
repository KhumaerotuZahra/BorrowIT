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
            ->where('status', 'active')
            ->whereNotNull('parent_borrowing_id')
            ->count();

        $overdueCount = Borrowing::where('user_id', $userId)
            ->where('status', 'overdue')
            ->whereNotNull('parent_borrowing_id')
            ->count();

        $pendingRequests = Borrowing::where('user_id', $userId)
            ->where('status', 'pending')
            ->whereNull('parent_borrowing_id')
            ->count();

        $myBorrowedAssets = Borrowing::with(['asset', 'assetGroup'])
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('parent_borrowing_id')
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
