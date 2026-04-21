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
        $pendingRequests = Borrowing::where('status', 'pending')->whereNull('parent_borrowing_id')->count();
        $activeBorrows = Borrowing::where('status', 'active')->whereNotNull('parent_borrowing_id')->count();
        $overdueCount = Borrowing::where('status', 'overdue')->whereNotNull('parent_borrowing_id')->count();

        $recentRequests = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->where('status', 'pending')
            ->whereNull('parent_borrowing_id')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $activeBorrowings = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('parent_borrowing_id')
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
            'overdueCount',
            'recentRequests',
            'activeBorrowings',
            'monthlyData',
            'notifications'
        ));
    }

    public function monthlyBorrowing(Request $request)
    {
        Borrowing::checkAndUpdateOverdue();

        $year = $request->get('year', date('Y'));
        $month = $request->get('month', '');

        $query = Borrowing::with(['user', 'asset', 'assetGroup']);

        if ($month) {
            $query->whereYear('borrow_date', $year)->whereMonth('borrow_date', $month);
        } else {
            $query->whereYear('borrow_date', $year);
        }

        $borrowings = $query->whereNotNull('parent_borrowing_id')->orderBy('borrow_date', 'desc')->get();

        $mostBorrowedQuery = Borrowing::select('asset_id')
            ->selectRaw('COUNT(*) as borrow_count')
            ->whereNotNull('asset_id')
            ->whereYear('borrow_date', $year);
        if ($month) {
            $mostBorrowedQuery->whereMonth('borrow_date', $month);
        }
        $mostBorrowed = $mostBorrowedQuery
            ->groupBy('asset_id')
            ->orderByDesc('borrow_count')
            ->with(['asset', 'assetGroup'])
            ->take(10)
            ->get();

        $returnedQuery = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->where('status', 'returned')
            ->whereYear('return_date', $year);
        if ($month) {
            $returnedQuery->whereMonth('return_date', $month);
        }
        $returnedItems = $returnedQuery->orderBy('return_date', 'desc')->get();

        $monthlyData = $this->getMonthlyBorrowData($year);

        $equipmentData = $this->getEquipmentBorrowData($year, $month);

        $monthLabel = $month ? date('F', mktime(0, 0, 0, $month, 1)) : 'All Months';

        return view('admin.monthly-borrowing', compact(
            'borrowings',
            'mostBorrowed',
            'returnedItems',
            'monthlyData',
            'equipmentData',
            'year',
            'month',
            'monthLabel'
        ));
    }

    public function exportBorrowings(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', '');

        $query = Borrowing::with(['user', 'asset', 'assetGroup'])->whereYear('borrow_date', $year);
        if ($month) {
            $query->whereMonth('borrow_date', $month);
        }
        $borrowings = $query->orderBy('borrow_date', 'desc')->get();

        $filename = "borrow_details_{$year}" . ($month ? "_{$month}" : "") . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($borrowings) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No', 'User', 'Department', 'Asset', 'Borrow Date', 'Return Date', 'Status']);

            foreach ($borrowings as $i => $b) {
                fputcsv($file, [
                    $i + 1,
                    $b->user->name ?? '-',
                    $b->user->department ?? '-',
                    $b->asset->asset_name ?? ($b->assetGroup->group_name ?? '-'),
                    $b->borrow_date ? $b->borrow_date->format('Y-m-d') : '-',
                    $b->return_date ? $b->return_date->format('Y-m-d') : '-',
                    ucfirst($b->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportMostBorrowed(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', '');

        $query = Borrowing::select('asset_id')
            ->selectRaw('COUNT(*) as borrow_count')
            ->whereYear('borrow_date', $year);
        if ($month) {
            $query->whereMonth('borrow_date', $month);
        }
        $items = $query->groupBy('asset_id')->orderByDesc('borrow_count')->with(['asset', 'assetGroup'])->get();

        $filename = "most_borrowed_{$year}" . ($month ? "_{$month}" : "") . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['Rank', 'Asset Name', 'Total Borrow']);

            foreach ($items as $i => $item) {
                fputcsv($file, [
                    $i + 1,
                    $item->asset->asset_name ?? '-',
                    $item->borrow_count,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportReturnedItems(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', '');

        $query = Borrowing::with(['user', 'asset', 'assetGroup'])
            ->where('status', 'returned')
            ->whereYear('return_date', $year);
        if ($month) {
            $query->whereMonth('return_date', $month);
        }
        $items = $query->orderBy('return_date', 'desc')->get();

        $filename = "returned_items_{$year}" . ($month ? "_{$month}" : "") . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['No', 'Name', 'Asset Name', 'Return Date', 'Return PIC']);

            foreach ($items as $i => $item) {
                fputcsv($file, [
                    $i + 1,
                    $item->user->name ?? '-',
                    $item->asset->asset_name ?? ($item->assetGroup->group_name ?? '-'),
                    $item->return_date ? $item->return_date->format('Y-m-d') : '-',
                    $item->return_pic ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getMonthlyBorrowData($year = null)
    {
        $year = $year ?? date('Y');
        $data = [];

        for ($i = 1; $i <= 12; $i++) {
            $count = Borrowing::whereYear('borrow_date', $year)
                ->whereMonth('borrow_date', $i)
                ->whereNotNull('parent_borrowing_id')
                ->count();
            $data[] = $count;
        }

        return $data;
    }

    private function getEquipmentBorrowData($year, $month = null)
    {
        $query = Borrowing::select('asset_id')
            ->selectRaw('COUNT(*) as borrow_count')
            ->whereNotNull('asset_id')
            ->whereNotNull('parent_borrowing_id')
            ->whereYear('borrow_date', $year);

        if ($month) {
            $query->whereMonth('borrow_date', $month);
        }

        return $query->whereHas('asset')
            ->groupBy('asset_id')
            ->with('asset')
            ->orderByDesc('borrow_count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->asset->asset_name,
                    'count' => $item->borrow_count,
                ];
            });
    }
}
