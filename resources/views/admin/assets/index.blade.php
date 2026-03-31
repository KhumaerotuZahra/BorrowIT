@extends('layouts.app')

@section('title', 'Asset Management')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Asset Management</h1>
        <p class="page-subtitle">Manage your organization's assets and inventory</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Assets</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.assets.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search assets..." value="{{ request('search') }}">
                </form>
                <button class="btn btn-primary" onclick="openModal('add-asset-modal')">
                    <i data-lucide="plus"></i>
                    Add Asset
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Asset ID</th>
                        <th>Asset Number</th>
                        <th>Asset Name</th>
                        <th>Total Stock</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $index => $asset)
                        <tr>
                            <td>{{ $assets->firstItem() + $index }}</td>
                            <td><span class="font-mono" style="color:var(--accent);font-weight:600;">{{ $asset->asset_id }}</span></td>
                            <td><span class="font-mono">{{ $asset->asset_number }}</span></td>
                            <td style="font-weight:500;">{{ $asset->asset_name }}</td>
                            <td>{{ $asset->total_stock }}</td>
                            <td>
                                <span class="badge {{ $asset->available_stock > 0 ? 'badge-active' : 'badge-overdue' }}">
                                    {{ $asset->available_stock }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-ghost btn-sm btn-icon" onclick="openEditAsset({{ $asset->id }}, '{{ addslashes($asset->asset_number) }}', '{{ addslashes($asset->asset_name) }}', {{ $asset->available_stock }})" title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" onsubmit="return confirm('Are you sure you want to delete this asset?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i data-lucide="package"></i>
                                <p class="empty-title">No assets found</p>
                                <p class="empty-desc">Start by adding your first asset.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
            <div class="pagination-wrapper">
                {{ $assets->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
<div class="modal" id="add-asset-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Add New Asset</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.assets.store') }}">
        @csrf
        <div class="modal-body">
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;padding:10px 14px;background:var(--surface-2);border-radius:var(--radius-sm);">
                <strong>Asset ID</strong> will be auto-generated in format: BPI-YY-NNNN
            </p>
            <div class="form-group">
                <label class="form-label">Asset Number</label>
                <input type="text" class="form-control" name="asset_number" required placeholder="e.g. LAP-001" maxlength="30">
            </div>
            <div class="form-group">
                <label class="form-label">Asset Name</label>
                <input type="text" class="form-control" name="asset_name" required placeholder="e.g. Laptop Dell XPS 15">
            </div>
            <div class="form-group">
                <label class="form-label">Available Stock</label>
                <input type="number" class="form-control" name="available_stock" required min="0" placeholder="Enter stock quantity">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Asset</button>
        </div>
    </form>
</div>

<div class="modal" id="edit-asset-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Edit Asset</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="edit-asset-form">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Asset Number</label>
                <input type="text" class="form-control" name="asset_number" id="edit-asset-number" required maxlength="30">
            </div>
            <div class="form-group">
                <label class="form-label">Asset Name</label>
                <input type="text" class="form-control" name="asset_name" id="edit-asset-name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Available Stock</label>
                <input type="number" class="form-control" name="available_stock" id="edit-asset-stock" required min="0">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Asset</button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openEditAsset(id, number, name, stock) {
    document.getElementById('edit-asset-form').action = '/admin/assets/' + id;
    document.getElementById('edit-asset-number').value = number;
    document.getElementById('edit-asset-name').value = name;
    document.getElementById('edit-asset-stock').value = stock;
    openModal('edit-asset-modal');
    lucide.createIcons();
}
</script>
@endpush
