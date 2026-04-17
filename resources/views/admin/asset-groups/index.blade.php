@extends('layouts.app')

@section('title', 'Asset Groups')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Asset Groups</h1>
        <p class="page-subtitle">Manage asset categories/groups</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Groups</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.asset-groups.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search groups..." value="{{ request('search') }}">
                </form>
                <button class="btn btn-primary" onclick="openModal('add-group-modal')">
                    <i data-lucide="plus"></i>
                    Add Group
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Group Code</th>
                        <th>Group Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $index => $group)
                        <tr>
                            <td>{{ $groups->firstItem() + $index }}</td>
                            <td><span class="font-mono" style="color:var(--accent);font-weight:600;">{{ $group->group_code }}</span></td>
                            <td style="font-weight:500;">{{ $group->group_name }}</td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-ghost btn-sm btn-icon" onclick="openEditGroup({{ $group->id }}, '{{ addslashes($group->group_code) }}', '{{ addslashes($group->group_name) }}')" title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.asset-groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?')">
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
                        <tr><td colspan="4">
                            <div class="empty-state">
                                <i data-lucide="folder"></i>
                                <p class="empty-title">No asset groups found</p>
                                <p class="empty-desc">Start by adding your first group.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($groups->hasPages())
            <div class="pagination-wrapper">
                {{ $groups->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
<div class="modal" id="add-group-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Add New Group</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.asset-groups.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Group Code</label>
                <input type="text" class="form-control" name="group_code" required placeholder="e.g. LPT, MSE, PRJ" maxlength="30">
            </div>
            <div class="form-group">
                <label class="form-label">Group Name</label>
                <input type="text" class="form-control" name="group_name" required placeholder="e.g. Laptop, Mouse, Projector">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Group</button>
        </div>
    </form>
</div>

<div class="modal" id="edit-group-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Edit Group</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="edit-group-form">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Group Code</label>
                <input type="text" class="form-control" name="group_code" id="edit-group-code" required maxlength="30">
            </div>
            <div class="form-group">
                <label class="form-label">Group Name</label>
                <input type="text" class="form-control" name="group_name" id="edit-group-name" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Group</button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openEditGroup(id, code, name) {
    document.getElementById('edit-group-form').action = '/admin/asset-groups/' + id;
    document.getElementById('edit-group-code').value = code;
    document.getElementById('edit-group-name').value = name;
    openModal('edit-group-modal');
    lucide.createIcons();
}
</script>
@endpush
