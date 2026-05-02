@extends('layouts.app')

@section('title', 'Department')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Department</h1>
        <p class="page-subtitle">Manage company departments</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Departments</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.departments.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search departments..." value="{{ request('search') }}">
                </form>
                <a href="{{ route('admin.departments.export-template') }}" class="btn btn-outline">
                    <i data-lucide="download"></i>
                    Export Template
                </a>
                <button class="btn btn-outline" onclick="openModal('import-dept-modal')">
                    <i data-lucide="upload"></i>
                    Import Excel
                </button>
                <button class="btn btn-primary" onclick="openModal('add-dept-modal')">
                    <i data-lucide="plus"></i>
                    Add Department
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Users</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $index => $dept)
                        <tr>
                            <td>{{ $departments->firstItem() + $index }}</td>
                            <td style="font-weight:500;">{{ $dept->name }}</td>
                            <td>{{ $dept->users()->count() }}</td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditDept({{ $dept->id }}, '{{ addslashes($dept->name) }}')">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $dept) }}" class="inline-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete" onclick="return confirm('Delete this department?')">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">
                            <div class="empty-state">
                                <i data-lucide="building-2"></i>
                                <p class="empty-title">No departments yet</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($departments->hasPages())
            <div class="pagination-wrapper">
                {{ $departments->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
{{-- Add Department Modal --}}
<div class="modal" id="add-dept-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Add Department</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.departments.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Department Name</label>
                <input type="text" class="form-control" name="name" placeholder="e.g. Information Technology" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Department</button>
        </div>
    </form>
</div>

{{-- Edit Department Modal --}}
<div class="modal" id="edit-dept-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Edit Department</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form id="edit-dept-form" method="POST">
        @csrf @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Department Name</label>
                <input type="text" class="form-control" id="edit-dept-name" name="name" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

{{-- Import Modal --}}
<div class="modal" id="import-dept-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Import Departments</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.departments.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Upload File (.xlsx, .xls, .csv)</label>
                <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin-top:8px;">
                Download the <a href="{{ route('admin.departments.export-template') }}">template</a> first to see the required format.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Import</button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openEditDept(id, name) {
    document.getElementById('edit-dept-form').action = window.baseUrl + '/admin/departments/' + id;
    document.getElementById('edit-dept-name').value = name;
    openModal('edit-dept-modal');
}
</script>
@endpush
