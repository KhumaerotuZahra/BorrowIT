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
                        <input type="text" name="search" placeholder="Search by asset..." value="{{ request('search') }}">
                    </div>
                    <select name="status" class="form-control" style="width:auto;padding:8px 36px 8px 14px;font-size:13px;height:40px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                    </select>
                </form>
                <button class="btn btn-primary" onclick="openModal('new-borrow-modal')">
                    <i data-lucide="plus"></i>
                    New Borrow
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Asset Name</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td>{{ $borrowings->firstItem() + $index }}</td>
                            <td style="font-weight:500;">{{ $borrow->asset->asset_name }}</td>
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
                        </tr>
                    @empty
                        <tr><td colspan="5">
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
                <label class="form-label">Select Asset</label>
                <select class="form-control" name="asset_id" id="asset-select" required onchange="updateStock()">
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
                <input type="number" class="form-control" value="1" readonly>
                <input type="hidden" name="quantity" value="1">
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
                <textarea class="form-control" name="purpose" placeholder="Why do you need this asset?" rows="3" maxlength="500"></textarea>
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
@endpush

@push('scripts')
<script>
function updateStock() {
    const sel = document.getElementById('asset-select');
    const info = document.getElementById('stock-info');
    const qtyInput = document.getElementById('quantity-input');
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
