@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Manage Users</h1>
        <p class="page-subtitle">Add, edit, and manage user accounts</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Users</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.users.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search users..." value="{{ request('search') }}">
                </form>
                <form method="GET" action="{{ route('admin.users.index') }}">
                    <select name="role" class="form-control" onchange="this.form.submit()" style="width:auto;">
                        <option value="">All</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                </form>
                <a href="{{ route('admin.users.export-template') }}" class="btn btn-outline">
                    <i data-lucide="download"></i>
                    Export Template
                </a>
                <button class="btn btn-outline" onclick="openModal('import-user-modal')">
                    <i data-lucide="upload"></i>
                    Import Excel
                </button>
                <button class="btn btn-primary" onclick="openModal('add-user-modal')">
                    <i data-lucide="plus"></i>
                    Add User
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>
                                {{ $user->employee_id ?? '-' }}
                            </td>
                            <td>
                                    <span style="font-weight:500;">{{ $user->name }}</span>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department ?? '-' }}</td>
                            <td><span class="badge {{ $user->role === 'admin' ? 'badge-active' : 'badge-approved' }}">{{ ucfirst($user->role) }}</span></td>
                            <td><span class="badge {{ $user->status == 'active' ? 'badge-active' : 'badge-inactive' }}">
                                {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn btn-ghost btn-sm btn-icon" onclick="openEditUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->department }}', '{{ $user->role }}', '{{ $user->status }}', '{{ $user->employee_id }}' )" title="Edit">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm btn-icon" style="color:var(--danger);" title="Delete">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <i data-lucide="users"></i>
                                <p class="empty-title">No users found</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="pagination-wrapper">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection

@push('modals')
<div class="modal" id="import-user-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Import Users from Excel</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div style="padding:14px;background:var(--surface-2);border-radius:var(--radius);margin-bottom:18px;">
                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:8px;font-weight:600;">Excel format (header row):</p>
                <p style="font-size:11px;color:var(--text-muted);line-height:1.6;">
                    <code>employee_id</code>, <code>name</code>, <code>email</code>, <code>department</code>, <code>role</code>, <code>status</code><br>
                    <span style="color:var(--text-muted);">Extra columns (e.g. position) will be ignored automatically.</span><br>
                    <span style="color:var(--text-muted);">Default password: <strong>password123</strong></span>
                </p>
            </div>
            <div class="form-group">
                <label class="form-label">Choose Excel File</label>
                <input type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required style="padding:10px;">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="upload"></i>
                Import
            </button>
        </div>
    </form>
</div>

<div class="modal" id="add-user-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Add New User</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Employee ID</label>
                <input type="text" class="form-control" name="employee_id" required placeholder="Enter Employee ID">
            </div>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" required placeholder="Enter full name">
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" required placeholder="name@ptbpi.co.id">
                <small style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block;">Must use @ptbpi.co.id domain</small>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required placeholder="Minimum 8 characters" minlength="8">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control" name="department" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Add User</button>
        </div>
    </form>
</div>

<div class="modal" id="edit-user-modal" style="display:none;">
    <div class="modal-header">
        <h3 class="modal-title">Edit User</h3>
        <button class="modal-close" onclick="closeAllModals()"><i data-lucide="x"></i></button>
    </div>
    <form method="POST" id="edit-user-form">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Employee ID</label>
                <input type="text" class="form-control" name="employee_id" id="edit-employee-id" required>
            </div>
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" id="edit-name" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control" name="email" id="edit-email" required>
            </div>
            <div class="form-group">
                <label class="form-label">Reset Password</label>
                <div style="display:flex;gap:20px;align-items:center;margin-top:4px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="reset_password" value="no" checked onclick="toggleResetPw(false)"> No
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="reset_password" value="yes" onclick="toggleResetPw(true)"> Yes
                    </label>
                </div>
            </div>
            <div id="reset-pw-fields" style="display:none;">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password" id="edit-password" placeholder="Enter new password" minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="password_confirmation" id="edit-password-confirm" placeholder="Confirm new password">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select class="form-control" name="department" id="edit-department" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role" id="edit-role" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div style="display:flex; gap:15px;">
                        <label>
                            <input type="radio" name="status" value="active"
                                {{  $user->status == 'active' ? 'checked' : '' }}>
                            Active
                        </label>
                        <label>
                            <input type="radio" name="status" value="inactive"
                                {{ $user->status == 'inactive' ? 'checked' : '' }}>
                            Inactive
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeAllModals()">Cancel</button>
            <button type="submit" class="btn btn-primary">Update User</button>
        </div>
    </form>
</div>
@endpush

@push('scripts')
<script>
function openEditUser(id, name, email, department, role, status, employee_id) {
    document.getElementById('edit-user-form').action = '{{ url('admin/users') }}/' + id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-department').value = department;
    document.getElementById('edit-role').value = role;
    document.getElementById('edit-employee-id').value = employee_id;
    // Reset radio to No
    document.querySelector('input[name="reset_password"][value="no"]').checked = true;
    toggleResetPw(false);
    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.checked = radio.value === status;
    });
    openModal('edit-user-modal');
    lucide.createIcons();
}

function toggleResetPw(show) {
    const fields = document.getElementById('reset-pw-fields');
    const pwInput = document.getElementById('edit-password');
    const pwConfirm = document.getElementById('edit-password-confirm');
    if (show) {
        fields.style.display = 'block';
        pwInput.required = true;
        pwConfirm.required = true;
    } else {
        fields.style.display = 'none';
        pwInput.required = false;
        pwConfirm.required = false;
        pwInput.value = '';
        pwConfirm.value = '';
    }
}
</script>
@endpush
