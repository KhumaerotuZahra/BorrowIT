<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Borrowing;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        Borrowing::checkAndUpdateOverdue();

        $totalAssets = Asset::count();
        $availableStock = Asset::sum('available_stock');
        $pendingRequests = Borrowing::where('status', 'pending')->count();
        $activeBorrows = Borrowing::whereIn('status', ['active', 'overdue'])->count();

        $recentRequests = Borrowing::with(['user', 'asset'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeBorrowings = Borrowing::with(['user', 'asset'])
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $monthlyData = $this->getMonthlyBorrowData();

        $notifications = Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalAssets',
            'availableStock',
            'pendingRequests',
            'activeBorrows',
            'recentRequests',
            'activeBorrowings',
            'monthlyData',
            'notifications'
        ));
    }

    public function monthlyBorrowing(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $filter = $request->get('filter', 'monthly');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        $query = Borrowing::with(['user', 'asset']);

        if ($filter === 'monthly') {
            $query->whereYear('borrow_date', $year)->whereMonth('borrow_date', $month);
        } elseif ($filter === 'yearly') {
            $query->whereYear('borrow_date', $year);
        }

        $borrowings = $query->orderBy('borrow_date', 'desc')->get();

        $mostBorrowed = Borrowing::select('asset_id')
            ->selectRaw('COUNT(*) as borrow_count')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->whereYear('borrow_date', $year)
            ->groupBy('asset_id')
            ->orderByDesc('borrow_count')
            ->with('asset')
            ->take(10)
            ->get();

        $returnedItems = Borrowing::with(['user', 'asset'])
            ->where('status', 'returned')
            ->whereYear('return_date', $year)
            ->orderBy('return_date', 'desc')
            ->get();

        $monthlyData = $this->getMonthlyBorrowData($year);

        return view('admin.monthly-borrowing', compact(
            'borrowings',
            'mostBorrowed',
            'returnedItems',
            'monthlyData',
            'filter',
            'year',
            'month'
        ));
    }

    private function getMonthlyBorrowData($year = null)
    {
        $year = $year ?? date('Y');
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $count = Borrowing::whereYear('borrow_date', $year)
                ->whereMonth('borrow_date', $i)
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
