@extends('layouts.app')

@section('title', 'Monthly Borrowing')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Monthly Borrowing</h1>
        <p class="page-subtitle">Detailed borrowing analytics and reports</p>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Borrowing Trend - {{ $year }}</h3>
            <form method="GET" action="{{ route('admin.monthly-borrowing') }}" style="display:flex;gap:10px;align-items:center;">
                <select name="year" class="form-control" style="width:auto;padding:6px 32px 6px 12px;font-size:13px;" onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
        </div>
        <div class="chart-container">
            <canvas id="monthlyDetailChart"></canvas>
        </div>
    </div>

    <div class="content-grid">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Most Borrowed Items</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Asset</th>
                            <th>Times Borrowed</th>
                            <th>Total Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mostBorrowed as $index => $item)
                            <tr>
                                <td>
                                    @if($index < 3)
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;font-size:11px;font-weight:700;
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
                                <td>{{ $item->total_quantity }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                <div class="empty-state">
                                    <i data-lucide="trending-up"></i>
                                    <p class="empty-title">No data available</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Returned Items</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Asset</th>
                            <th>Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedItems->take(10) as $item)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar-circle" style="width:28px;height:28px;font-size:10px;">{{ strtoupper(substr($item->user->name, 0, 1)) }}</div>
                                        <span style="font-size:13px;">{{ $item->user->name }}</span>
                                    </div>
                                </td>
                                <td style="font-size:13px;">{{ $item->asset->asset_name }}</td>
                                <td style="font-size:12px;color:var(--text-muted);">{{ $item->return_date->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">
                                <div class="empty-state">
                                    <i data-lucide="undo-2"></i>
                                    <p class="empty-title">No returned items</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Borrowings - {{ $year }}</h3>
            <form method="GET" action="{{ route('admin.monthly-borrowing') }}" style="display:flex;gap:10px;align-items:center;">
                <input type="hidden" name="year" value="{{ $year }}">
                <select name="month" class="form-control" style="width:auto;padding:6px 32px 6px 12px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                        <option value="{{ $i + 1 }}" {{ $month == $i + 1 ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Qty</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td style="font-weight:500;">{{ $borrow->user->name }}</td>
                            <td>{{ $borrow->asset->asset_name }}</td>
                            <td>{{ $borrow->quantity }}</td>
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">{{ $borrow->due_date->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i data-lucide="calendar"></i>
                                <p class="empty-title">No borrowings found</p>
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
    const ctx = document.getElementById('monthlyDetailChart').getContext('2d');
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const data = @json($monthlyData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Borrowings',
                data: data,
                borderColor: 'rgba(14,165,233,1)',
                backgroundColor: 'rgba(14,165,233,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: '#fff',
                pointBorderColor: 'rgba(14,165,233,1)',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a1d2e',
                    titleFont: { size: 13, weight: '600' },
                    bodyFont: { size: 12 },
                    padding: 12,
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
});
</script>
@endpush
