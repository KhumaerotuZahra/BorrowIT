@extends('layouts.app')

@section('title', 'Borrow Requests')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Borrow Requests</h1>
        <p class="page-subtitle">Review and manage asset borrow requests</p>
    </div>

    <div class="filter-cards">
        <a href="{{ route('admin.borrow-requests.index') }}" class="filter-card {{ !request('status') ? 'active' : '' }}">
            <div class="filter-count">{{ $pendingCount + $approvedCount + $activeCount }}</div>
            <div class="filter-label">All</div>
        </a>
        <a href="{{ route('admin.borrow-requests.index', ['status' => 'pending']) }}" class="filter-card {{ request('status') === 'pending' ? 'active' : '' }}">
            <div class="filter-count" style="color:var(--warning);">{{ $pendingCount }}</div>
            <div class="filter-label">Pending</div>
        </a>
        <a href="{{ route('admin.borrow-requests.index', ['status' => 'approved']) }}" class="filter-card {{ request('status') === 'approved' ? 'active' : '' }}">
            <div class="filter-count" style="color:var(--accent);">{{ $approvedCount }}</div>
            <div class="filter-label">Approved</div>
        </a>
        <a href="{{ route('admin.borrow-requests.index', ['status' => 'active']) }}" class="filter-card {{ request('status') === 'active' ? 'active' : '' }}">
            <div class="filter-count" style="color:var(--success);">{{ $activeCount }}</div>
            <div class="filter-label">Active</div>
        </a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Requests</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.borrow-requests.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                </form>
            </div>
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
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td>{{ $borrowings->firstItem() + $index }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($borrow->user->name, 0, 1)) }}</div>
                                    <div>
                                        <div style="font-weight:500;">{{ $borrow->user->name }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $borrow->user->department }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $borrow->asset->asset_name }}</div>
                                <div class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $borrow->asset->asset_id }}</div>
                            </td>
                            <td>{{ $borrow->quantity }}</td>
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">{{ $borrow->due_date->format('d M Y') }}</td>
                            <td style="max-width:150px;" class="text-truncate">{{ $borrow->purpose ?? '-' }}</td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            <td>
                                <div class="action-btns">
                                    @if($borrow->status === 'pending')
                                        <form method="POST" action="{{ route('admin.borrow-requests.approve', $borrow) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm btn-icon" title="Approve">
                                                <i data-lucide="check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.borrow-requests.reject', $borrow) }}" onsubmit="return confirm('Reject this request?')">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Reject">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </form>
                                    @elseif($borrow->status === 'approved')
                                        <button class="btn btn-primary btn-sm" onclick="openHandoverModal({{ $borrow->id }}, '{{ addslashes($borrow->asset->asset_name) }}', '{{ $borrow->user->name }}')">
                                            <i data-lucide="hand-metal"></i>
                                            Hand Over
                                        </button>
                                    @elseif($borrow->status === 'active')
                                        <span class="badge badge-active">In Progress</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p class="empty-title">No borrow requests found</p>
                                <p class="empty-desc">Requests will appear here when users submit them.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($borrowings->hasPages())
            <div class="pagination-wrapper">
                {{ $borrowings->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
<div class="modal" id="handover-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Hand Over Asset</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="handover-form">
        @csrf
        <div class="modal-body">
            <div style="padding:14px;background:var(--accent-light);border-radius:var(--radius);margin-bottom:18px;">
                <p style="font-size:13px;color:var(--accent);font-weight:600;" id="handover-info"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Handed Over By</label>
                <input type="text" class="form-control" name="handover_by" required placeholder="Name of person handing over the asset" value="{{ auth()->user()->name }}">
            </div>
            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea class="form-control" name="handover_notes" placeholder="Any notes about the handover..." rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="check-circle"></i>
                Confirm Hand Over
            </button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openHandoverModal(id, assetName, userName) {
    document.getElementById('handover-form').action = '/admin/borrow-requests/' + id + '/handover';
    document.getElementById('handover-info').textContent = 'Handing over "' + assetName + '" to ' + userName;
    openModal('handover-modal');
    lucide.createIcons();
}
</script>
@endpush
