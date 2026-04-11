@extends('layouts.app')

@section('title', 'Active Borrows')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Active Borrows</h1>
        <p class="page-subtitle">Track and manage currently borrowed assets</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Active Borrows</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.active-borrows.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                </form>
            </div>
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
                        <th>Handover PIC</th>
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
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">
                                {{ $borrow->due_date->format('d M Y') }}
                                @if($borrow->status === 'overdue')
                                    <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">
                                        {{ $borrow->due_date->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:12px;">{{ $borrow->handover_by ?? '-' }}</td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            <td>
                                @if(in_array($borrow->status, ['active', 'overdue']))
                                    <button class="btn btn-primary btn-sm" onclick="openReturnModal({{ $borrow->id }}, '{{ addslashes($borrow->asset->asset_name) }}', '{{ $borrow->user->name }}')">
                                        <i data-lucide="undo-2"></i>
                                        Return
                                    </button>
                                @elseif($borrow->status === 'returned')
                                    <span style="font-size:12px;color:var(--text-muted);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">
                            <div class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p class="empty-title">No active borrows</p>
                                <p class="empty-desc">Active borrows will appear here.</p>
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
<div class="modal" id="return-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Return Asset</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="return-form">
        @csrf
        <div class="modal-body">
            <div style="padding:14px;background:var(--accent-light);border-radius:var(--radius);margin-bottom:18px;">
                <p style="font-size:13px;color:var(--accent);font-weight:600;" id="return-info"></p>
            </div>
            <div class="form-group">
                <label class="form-label">Return PIC</label>
                <input type="text" class="form-control" name="return_pic" value="{{ auth()->user()->name }}" required placeholder="Person receiving the return">
            </div>
            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea class="form-control" name="return_notes" placeholder="Any notes about the return..." rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="check-circle"></i>
                Confirm Return
            </button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openReturnModal(id, assetName, userName) {
    document.getElementById('return-form').action = '/admin/active-borrows/' + id + '/return';
    document.getElementById('return-info').textContent = 'Returning "' + assetName + '" from ' + userName;
    openModal('return-modal');
    lucide.createIcons();
}
</script>
@endpush
