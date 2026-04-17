@extends('layouts.app')

@section('title', 'Borrow Requests')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Borrow Requests</h1>
        <p class="page-subtitle">Manage asset borrowing requests</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Borrow Requests</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.borrow-requests.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                </form>
                <button class="btn btn-primary" onclick="openModal('add-request-modal')">
                    <i data-lucide="plus"></i>
                    + Request
                </button>
            </div>
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
                            <td style="font-weight:500;">{{ $borrow->assetGroup->group_name ?? '-' }}</td>
                            <td>{{ $borrow->quantity }}</td>
                            <td style="font-size:12px;">{{ $borrow->created_at->format('d M Y') }}</td>
                            <td style="font-size:12px;">
                                {{ $borrow->borrow_date->format('d/m') }} - {{ $borrow->due_date->format('d/m/Y') }}
                            </td>
                            <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            <td style="font-size:12px;">{{ $borrow->approved_date ? $borrow->approved_date->format('d M Y') : '-' }}</td>
                            <td>
                                <div class="action-btns">
                                    @if($borrow->status === 'pending')
                                        <form method="POST" action="{{ route('admin.borrow-requests.approve', $borrow) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--success);" title="Approve" onclick="return confirm('Approve this request?')">
                                                <i data-lucide="check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.borrow-requests.reject', $borrow) }}" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Reject" onclick="return confirm('Reject this request?')">
                                                <i data-lucide="x"></i>
                                            </button>
                                        </form>
                                    @elseif($borrow->status === 'approved')
                                        <button class="btn btn-primary btn-sm" onclick="openHandoverModal({{ $borrow->id }}, '{{ addslashes($borrow->assetGroup->group_name ?? '') }}', '{{ $borrow->user->name }}', {{ $borrow->quantity }}, {{ $borrow->asset_group_id }})">
                                            Hand Over
                                        </button>
                                    @else
                                        <span style="font-size:12px;color:var(--text-muted);">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p class="empty-title">No borrow requests</p>
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
{{-- Add Request Modal --}}
<div class="modal" id="add-request-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">New Borrow Request</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.borrow-requests.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">User (Borrower)</label>
                <select class="form-control" name="user_id" required>
                    <option value="">Search and select user...</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->department }}</option>
                    @endforeach
                </select>
            </div>
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
                    <input type="date" class="form-control" name="borrow_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date (Return By)</label>
                    <input type="date" class="form-control" name="due_date" required>
                </div>
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

{{-- Handover Modal --}}
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
                <label class="form-label">Asset Group Name</label>
                <input type="text" class="form-control" id="handover-group-name" readonly style="background:var(--surface-2);">
            </div>

            <div id="asset-select-container">
                {{-- Dynamic asset selects will be inserted here by JS --}}
            </div>

            <div class="form-group">
                <label class="form-label">Borrow Date</label>
                <input type="date" class="form-control" name="borrow_date" value="{{ date('Y-m-d') }}">
            </div>

            <div class="form-group">
                <label class="form-label">Handover PIC</label>
                <select class="form-control" name="handover_by" required>
                    <option value="">Select PIC...</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->name }}" {{ $admin->id === auth()->id() ? 'selected' : '' }}>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notes (Optional)</label>
                <textarea class="form-control" name="handover_notes" placeholder="Any notes..." rows="3"></textarea>
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
function openHandoverModal(id, groupName, userName, quantity, groupId) {
    document.getElementById('handover-form').action = '/admin/borrow-requests/' + id + '/handover';
    document.getElementById('handover-info').textContent = 'Handing over "' + groupName + '" (qty: ' + quantity + ') to ' + userName;
    document.getElementById('handover-group-name').value = groupName;

    // Fetch available assets for this group
    const container = document.getElementById('asset-select-container');
    container.innerHTML = '<p style="font-size:12px;color:var(--text-muted);">Loading assets...</p>';

    fetch('/admin/borrow-requests/group-assets/' + groupId)
        .then(r => r.json())
        .then(assets => {
            container.innerHTML = '';
            for (let i = 0; i < quantity; i++) {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `
                    <label class="form-label">Asset ${i + 1}</label>
                    <select class="form-control" name="asset_ids[]" required>
                        <option value="">Select asset...</option>
                        ${assets.map(a => `<option value="${a.id}">${a.asset_name} - ${a.asset_number} (stock: ${a.available_stock})</option>`).join('')}
                    </select>
                `;
                container.appendChild(div);
            }
        })
        .catch(() => {
            container.innerHTML = '<p style="color:var(--danger);font-size:12px;">Failed to load assets.</p>';
        });

    openModal('handover-modal');
    lucide.createIcons();
}
</script>
@endpush
