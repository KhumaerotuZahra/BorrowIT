@extends('layouts.app')

@section('title', 'My Borrowings')

@section('content')
    <div class="page-header">
        <h1 class="page-title">My Borrowings</h1>
        <p class="page-subtitle">View and manage your asset borrow requests</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Borrowing History</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('user.borrowings.index') }}" style="display:flex;gap:10px;align-items:center;">
                    <div class="search-box">
                        <i data-lucide="search"></i>
                        <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <select name="status" class="form-control" style="width:auto;padding:8px 36px 8px 14px;font-size:13px;height:40px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="cancelled" {{ request('status') === 'calcelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
                <button class="btn btn-primary" onclick="openModal('new-borrow-modal')">
                    <i data-lucide="plus"></i>
                    New Request
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Asset Group</th>
                        <th>Qty</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Handover Notes</th>
                        <th>Return Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td>{{ $borrowings->firstItem() + $index }}</td>
                            <td style="font-weight:500;">{{ $borrow->assetGroup->group_name ?? ($borrow->asset->asset_name ?? '-') }}</td>
                            <td>{{ $borrow->quantity }}</td>
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">
                                {{ $borrow->due_date->format('d M Y') }}
                                @if($borrow->status === 'overdue')
                                    <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">
                                        {{ $borrow->due_date->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $borrow->status }}">
                                    {{ ucfirst($borrow->status) }}
                                </span>
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    $handoverNotes = $borrow->childBorrowings?->pluck('handover_notes')->filter()->unique()->implode(', ');
                                @endphp
                                {{ $handoverNotes ?: '-' }}
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    $returnNotes = $borrow->childBorrowings?->pluck('return_notes')->filter()->unique()->implode(', ');
                                @endphp
                                {{ $returnNotes ?: '-' }}
                            </td>
                            <td>
                                @if($borrow->status === 'pending')
                                    <form method="POST" action="{{ route('user.borrowings.cancel', $borrow) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel this request?')" style="padding:4px 10px;">
                                            <i data-lucide="x" style="width:14px;height:14px;"></i> Cancel
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:12px;color:var(--text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <i data-lucide="book-open"></i>
                                <p class="empty-title">No borrowings yet</p>
                                <p class="empty-desc">Click "New Borrow" to request an asset.</p>
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
<div class="modal" id="new-borrow-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">New Borrow Request</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('user.borrowings.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Select Asset Group</label>
                <select class="form-control" name="asset_group_id" required>
                    <option value="">Choose asset group...</option>
                    @foreach($assetGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="quantity" value="1" min="1" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Borrow Date</label>
                    <input type="date" class="form-control" name="borrow_date" required value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Return Date</label>
                    <input type="date" class="form-control" name="due_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="notes" rows="2" placeholder="Purpose or additional notes..."></textarea>
            </div>
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
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'new') {
        openModal('new-borrow-modal');
    }
});
</script>
@endpush
