@extends('layouts.app')

@section('title', 'Monthly Borrowing')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Monthly Borrowing</h1>
        <p class="page-subtitle">Detailed borrowing analytics and reports</p>
    </div>

    {{-- Filter Section --}}
    <form method="GET" action="{{ route('admin.monthly-borrowing') }}" style="display:flex;gap:12px;align-items:center;margin-bottom:24px;flex-wrap:wrap;">
        <select name="month" class="form-control" style="width:auto;padding:8px 32px 8px 14px;font-size:13px;">
            <option value="">All Months</option>
            @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                <option value="{{ $i + 1 }}" {{ $month == $i + 1 ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
        <select name="year" class="form-control" style="width:auto;padding:8px 32px 8px 14px;font-size:13px;">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        <button type="submit" class="btn btn-primary btn-sm">
            <i data-lucide="filter"></i>
            Apply Filter
        </button>
    </form>

    {{-- Charts: Bar + Pie side by side --}}
    <div class="content-grid" style="margin-bottom:24px;">
        <div class="chart-card" style="margin-bottom:0;">
            <h3 class="chart-title">Borrowing by Month</h3>
            <div class="chart-container">
                <canvas id="borrowByDateChart"></canvas>
            </div>
        </div>
        <div class="chart-card" style="margin-bottom:0;">
            <h3 class="chart-title">Borrow by Equipment - {{ $monthLabel }} {{ $year }}</h3>
            <div class="chart-container">
                <canvas id="borrowByEquipmentChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Most Borrowed Items --}}
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Most Borrowed Items - {{ $monthLabel }} {{ $year }}</h3>
            <a href="{{ route('admin.monthly-borrowing.export-most-borrowed', ['year' => $year, 'month' => $month]) }}" class="btn btn-success btn-sm">
                <i data-lucide="download"></i>
                Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Asset Name</th>
                        <th>Total Borrow</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mostBorrowed as $index => $item)
                        <tr>
                            <td>
                                @if($index < 3)
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:11px;font-weight:700;
                                        @if($index === 0) background:#fef3c7;color:#b45309;
                                        @elseif($index === 1) background:#e2e8f0;color:#475569;
                                        @else background:#fed7aa;color:#c2410c;
                                        @endif
                                    ">{{ $index + 1 }}</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td style="font-weight:500;">{{ $item->asset->asset_name ?? 'Deleted Asset' }}</td>
                            <td><span class="badge badge-active">{{ $item->borrow_count }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3">
                            <div class="empty-state">
                                <i data-lucide="trending-up"></i>
                                <p class="empty-title">No Data Yet</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Returned Items --}}
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Returned Items - {{ $monthLabel }} {{ $year }}</h3>
            <a href="{{ route('admin.monthly-borrowing.export-returned', ['year' => $year, 'month' => $month]) }}" class="btn btn-success btn-sm">
                <i data-lucide="download"></i>
                Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Asset Name</th>
                        <th>Name</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Return PIC </th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnedItems as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight:500;">{{ $item->asset->asset_name ?? ($item->assetGroup->group_name ?? '-') }}</td>
                            <td>{{ $item->user->name }}</td>
                            <td style="font-size:12px;">{{ $item->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">{{ $item->return_date ? $item->return_date->format('d M Y') : '-' }}</td>
                            <td>{{ $item->return_pic ?? '-' }}</td>
                            <td><span class="badge badge-returned">Returned</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i data-lucide="undo-2"></i>
                                <p class="empty-title">No Data Yet</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Borrow Detail --}}
    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Borrow Details - {{ $monthLabel }} {{ $year }}</h3>
            <a href="{{ route('admin.monthly-borrowing.export-borrowings', ['year' => $year, 'month' => $month]) }}" class="btn btn-success btn-sm">
                <i data-lucide="download"></i>
                Export Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Asset</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight:500;">{{ $borrow->user->name }}</td>
                            <td>{{ $borrow->user->department ?? '-' }}</td>
                            <td>{{ $borrow->asset->asset_name ?? ($borrow->assetGroup->group_name ?? '-') }}</td>
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">{{ $borrow->return_date ? $borrow->return_date->format('d M Y') : '-' }}</td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i data-lucide="calendar"></i>
                                <p class="empty-title">Belum ada data</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bar Chart - Borrowing by Month
    const barCtx = document.getElementById('borrowByDateChart').getContext('2d');
    const monthlyData = @json($monthlyData);
    const months = ['1','2','3','4','5','6','7','8','9','10','11','12'];

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Borrowed',
                data: monthlyData,
                backgroundColor: 'rgba(14, 165, 233, 0.7)',
                borderColor: 'rgba(14, 165, 233, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1d2e',
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 }, color: '#94a3b8' },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                x: {
                    ticks: { font: { size: 11 }, color: '#94a3b8' },
                    grid: { display: false }
                }
            }
        }
    });

    // Pie Chart - Borrow by Equipment
    const pieCtx = document.getElementById('borrowByEquipmentChart').getContext('2d');
    const equipmentData = @json($equipmentData);

    const pieLabels = equipmentData.map(e => e.name);
    const pieCounts = equipmentData.map(e => e.count);
    const pieColors = [
        '#8b5cf6', '#06b6d4', '#f59e0b', '#ef4444', '#10b981',
        '#6366f1', '#ec4899', '#14b8a6', '#f97316', '#84cc16'
    ];

    if (pieLabels.length > 0) {
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieCounts,
                    backgroundColor: pieColors.slice(0, pieLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1a1d2e',
                        padding: 10,
                        cornerRadius: 8,
                    }
                }
            }
        });
    } else {
        pieCtx.canvas.parentElement.innerHTML = '<div class="empty-state" style="height:100%;"><i data-lucide="pie-chart"></i><p class="empty-title">No data</p></div>';
        lucide.createIcons();
    }
});
</script>
@endpush
