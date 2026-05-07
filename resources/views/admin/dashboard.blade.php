@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}!</p>
    </div>

    <div class="stats-grid">
        <a href="{{ route('admin.assets.index') }}" style="text-decoration:none;color:inherit;">
            <div class="stat-card accent">
                <div class="stat-icon"><i data-lucide="package"></i></div>
                <div class="stat-value">{{ $totalAssets }}</div>
                <div class="stat-label">Total Assets</div>
            </div>
        </a>
        <a href="{{ route('admin.assets.index', ['status' => 'available']) }}" style="text-decoration:none;color:inherit;">
            <div class="stat-card success" style="cursor:pointer;">
                <div class="stat-icon"><i data-lucide="check-circle"></i></div>
                <div class="stat-value">{{ $availableStock }}</div>
                <div class="stat-label">Available Stock</div>
            </div>
        </a>
        <a href="{{ route('admin.borrow-requests.index')}}" style="text-decoration:none;color:inherit;">
            <div class="stat-card warning {{ $pendingRequests > 0 ? 'blink' : '' }}" style="cursor:pointer;">
                <div class="stat-icon"><i data-lucide="clock"></i></div>
                <div class="stat-value">{{ $pendingRequests }}</div>
                <div class="stat-label">Pending Requests</div>
            </div>
        </a>
        <a href="{{ route('admin.active-borrows.index', ['status' => 'active']) }}" style="text-decoration:none;color:inherit;">
            <div class="stat-card info" style="cursor:pointer;">
                <div class="stat-icon"><i data-lucide="repeat"></i></div>
                <div class="stat-value">{{ $activeBorrows }}</div>
                <div class="stat-label">Active Borrows</div>
            </div>
        </a>
        <a href="{{ route('admin.active-borrows.index', ['status' => 'overdue']) }}" style="text-decoration:none;color:inherit;">
            <div class="stat-card danger" style="cursor:pointer;">
                <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
                <div class="stat-value">{{ $overdueCount }}</div>
                <div class="stat-label">Overdue</div>
            </div>
        </a>
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
        {{-- List Borrow Request --}}
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">List Borrow Request</h3>
                <a href="{{ route('admin.borrow-requests.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Asset Group</th>
                            <th>Qty</th>
                            <th>Request Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $request)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($request->user->name, 0, 1)) }}</div>
                                        <div>
                                            <span style="font-weight:500;">{{ $request->user->name }}</span>
                                            <br><small style="color:var(--text-muted);font-size:11px;">{{ $request->user->department ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $request->assetGroup->group_name ?? '-' }}</td>
                                <td>{{ $request->quantity }}</td>
                                <td style="font-size:12px;">{{ $request->created_at->format('d M Y') }}</td>
                                <td style="font-size:12px;">
                                    @php $days = $request->borrow_date->diffInDays($request->due_date); @endphp
                                    {{ $days }} {{ $days == 1 ? 'day' : 'days' }}
                                </td>
                                <td>
                                    @if($request->status === 'approved')
                                        <a href="{{ route('admin.borrow-requests.index', ['status' => 'approved']) }}" style="text-decoration:none;">
                                            <span class="badge badge-approved" style="cursor:pointer;">Approved</span>
                                        </a>
                                    @else
                                        <span class="badge badge-{{ $request->status }}">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->status === 'pending')
                                        <div style="display:flex;gap:6px;">
                                            <form method="POST" action="{{ route('admin.borrow-requests.approve', $request) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" style="padding:4px 8px;min-width:auto;" title="Approve" onclick="return confirm('Approve this request?')">
                                                    <i data-lucide="check" style="width:14px;height:14px;"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" style="padding:4px 8px;min-width:auto;" title="Reject" onclick="openRejectModal({{ $request->id }})">
                                                <i data-lucide="x" style="width:14px;height:14px;"></i>
                                            </button>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8">
                                <div class="empty-state">
                                    <i data-lucide="inbox"></i>
                                    <p class="empty-title">No borrow requests</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Active Borrows --}}
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Active Borrows</h3>
                <a href="{{ route('admin.active-borrows.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Asset</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeBorrowings as $borrow)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($borrow->user->name, 0, 1)) }}</div>
                                        <span>{{ $borrow->user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $borrow->asset->asset_name ?? ($borrow->assetGroup->group_name ?? '-') }}</td>
                                <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                                <td style="font-size:12px;">{{ $borrow->due_date->format('d M Y') }}</td>
                                <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">
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

@push('modals')
<div class="modal" id="reject-modal" style="display:none;">
    <div class="modal-content" style="max-width:440px;">
        <div class="modal-header">
            <h3 class="modal-title">Reject Request</h3>
            <button class="modal-close" onclick="closeAllModals()">&times;</button>
        </div>
        <form id="reject-form" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Reason for Rejection</label>
                    <textarea class="form-control" name="reject_notes" rows="3" placeholder="Enter reason for rejecting this request..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Request</button>
            </div>
        </form>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const data = @json($monthlyData);

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

function openRejectModal(borrowingId) {
    const form = document.getElementById('reject-form');
    form.action = window.baseUrl + '/admin/borrow-requests/' + borrowingId + '/reject';
    openModal('reject-modal');
}
</script>
@endpush
