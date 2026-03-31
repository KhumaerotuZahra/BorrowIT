@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}!</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card accent">
            <div class="stat-icon"><i data-lucide="package"></i></div>
            <div class="stat-value">{{ $totalAssets }}</div>
            <div class="stat-label">Total Assets</div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i data-lucide="check-circle"></i></div>
            <div class="stat-value">{{ $availableStock }}</div>
            <div class="stat-label">Available Stock</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i data-lucide="clock"></i></div>
            <div class="stat-value">{{ $pendingRequests }}</div>
            <div class="stat-label">Pending Requests</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i data-lucide="repeat"></i></div>
            <div class="stat-value">{{ $activeBorrows }}</div>
            <div class="stat-label">Active Borrows</div>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-header">
            <h3 class="chart-title">Monthly Borrowing Overview</h3>
            <a href="{{ route('admin.monthly-borrowing') }}" class="btn btn-outline btn-sm">
                <i data-lucide="bar-chart-3"></i>
                View Details
            </a>
        </div>
        <div class="chart-container" onclick="window.location='{{ route('admin.monthly-borrowing') }}'">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <div class="content-grid">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Recent Borrow Requests</h3>
                <a href="{{ route('admin.borrow-requests.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Asset</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($request->user->name, 0, 1)) }}</div>
                                        <span>{{ $request->user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $request->asset->asset_name }}</td>
                                <td>{{ $request->created_at->format('d M Y') }}</td>
                                <td><span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                                <td>
                                    <div class="action-btns">
                                        <form method="POST" action="{{ route('admin.borrow-requests.approve', $request) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" title="Approve"><i data-lucide="check"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.borrow-requests.reject', $request) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Reject"><i data-lucide="x"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <i data-lucide="inbox"></i>
                                    <p class="empty-title">No pending requests</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Active Borrows</h3>
                <a href="{{ route('admin.active-borrows.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Asset</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeBorrowings as $borrow)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($borrow->user->name, 0, 1)) }}</div>
                                        <span>{{ $borrow->user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $borrow->asset->asset_name }}</td>
                                <td>{{ $borrow->due_date->format('d M Y') }}</td>
                                <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                <div class="empty-state">
                                    <i data-lucide="inbox"></i>
                                    <p class="empty-title">No active borrows</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const data = @json($monthlyData);

    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(14, 165, 233, 0.2)');
    gradient.addColorStop(1, 'rgba(14, 165, 233, 0.01)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Borrowings',
                data: data,
                backgroundColor: 'rgba(14, 165, 233, 0.7)',
                borderColor: 'rgba(14, 165, 233, 1)',
                borderWidth: 1,
                borderRadius: 6,
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
