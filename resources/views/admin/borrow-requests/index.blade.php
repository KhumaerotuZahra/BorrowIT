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
                <button class="btn btn-primary" onclick="openModal('admin-borrow-modal')">
                    <i data-lucide="plus"></i>
                    Add Request
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Request Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Approve Date</th>
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
                                <div class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $borrow->asset->asset_number }}</div>
                            </td>
                            <td style="font-size:12px;">{{ $borrow->created_at->format('d M Y') }}</td>
                            <td style="font-size:12px;">
                                @php
                                    $days = $borrow->borrow_date->diffInDays($borrow->due_date);
                                @endphp
                                {{ $days }} {{ $days == 1 ? 'day' : 'days' }}
                            </td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            <td style="font-size:12px;">
                                {{ $borrow->approved_date ? \Carbon\Carbon::parse($borrow->approved_date)->format('d M Y') : '-' }}
                            </td>
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
                                        <button class="btn btn-primary btn-sm" onclick="openHandoverModal({{ $borrow->id }}, '{{ addslashes($borrow->asset->asset_name) }}', '{{ $borrow->user->name }}', '{{ $borrow->borrow_date->format('Y-m-d') }}')">
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
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p class="empty-title">No borrow requests found</p>
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
{{-- Admin Create Borrow Request Modal --}}
<div class="modal" id="admin-borrow-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">New Borrow Request</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.borrow-requests.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">User (Borrower)</label>
                <select class="form-control" name="user_id" required id="user-search-select">
                    <option value="">Search and select user...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Select Asset</label>
                <select class="form-control" name="asset_id" required id="admin-asset-select" onchange="updateAdminStock()">
                    <option value="">Choose an asset to borrow</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}" data-stock="{{ $asset->available_stock }}">
                            {{ $asset->asset_name }} - {{ $asset->asset_number }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity</label>
                 <input type="number" class="form-control" name="quantity" value="1" min="1">
                <small style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block;" id="admin-stock-info"></small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Borrow Date</label>
                    <input type="date" class="form-control" name="borrow_date" required value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date (Return by)</label>
                    <input type="date" class="form-control" name="due_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
            </div>
            <!-- <div class="form-group">
                <label class="form-label">Purpose (Optional)</label>
                <textarea class="form-control" name="purpose" placeholder="Purpose of borrowing..." rows="3" maxlength="500"></textarea>
            </div> -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="send"></i>
                Submit Request
            </button>
        </div>
    </form>
</div>

{{-- Hand Over Modal --}}
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
                <label class="form-label">Borrow Date</label>
                <input type="date" class="form-control" name="borrow_date" id="handover-borrow-date" required>
            </div>
            <div class="form-group">
                <label class="form-label">Handover PIC</label>
                <input type="text" class="form-control" name="handover_by" required placeholder="Person handing over the asset" value="{{ auth()->user()->name }}">
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
function openHandoverModal(id, assetName, userName, borrowDate) {
    document.getElementById('handover-form').action = '/admin/borrow-requests/' + id + '/handover';
    document.getElementById('handover-info').textContent = 'Handing over "' + assetName + '" to ' + userName;
    document.getElementById('handover-borrow-date').value = borrowDate;
    openModal('handover-modal');
    lucide.createIcons();
}

function updateAdminStock() {
    const sel = document.getElementById('admin-asset-select');
    const info = document.getElementById('admin-stock-info');
    const qtyInput = document.getElementById('admin-qty-input');
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        const stock = parseInt(opt.dataset.stock);
        info.textContent = 'Available stock: ' + stock;
        qtyInput.max = stock;
    } else {
        info.textContent = '';
        qtyInput.removeAttribute('max');
    }
}
</script>
@endpush
