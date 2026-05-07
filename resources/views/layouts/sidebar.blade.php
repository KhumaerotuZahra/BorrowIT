@php
    $isAdmin = auth()->user()->isAdmin();
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="{{ asset('images/logo.png') }}" alt="BorrowIT Logo" class="logo-img">
    </div>
    <nav class="sidebar-nav">
        @if($isAdmin)
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>

            <div class="nav-section-title">User Management</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
                <i data-lucide="users"></i>
                <span>User</span>
            </a>
            <a href="{{ route('admin.departments.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.departments') ? 'active' : '' }}">
                <i data-lucide="building-2"></i>
                <span>Department</span>
            </a>

            <div class="nav-section-title">Asset Management</div>
            <a href="{{ route('admin.asset-groups.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.asset-groups') ? 'active' : '' }}">
                <i data-lucide="folder"></i>
                <span>Asset Group</span>
            </a>
            <a href="{{ route('admin.assets.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.assets') ? 'active' : '' }}">
                <i data-lucide="package"></i>
                <span>Asset</span>
            </a>
            <a href="{{ route('admin.asset-logs.index') }}" 
                class="nav-item {{ str_starts_with($currentRoute, 'admin.asset-logs') ? 'active' : '' }}">
                <i data-lucide="history"></i>
                <span>Asset Logs</span>
            </a>

            <div class="nav-section-title">Borrowing</div>
            <a href="{{ route('admin.borrow-requests.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.borrow-requests') ? 'active' : '' }}">
                <i data-lucide="file-text"></i>
                <span>Borrow Request</span>
            </a>
            <a href="{{ route('admin.active-borrows.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.active-borrows') ? 'active' : '' }}">
                <i data-lucide="repeat"></i>
                <span>List Borrow</span>
            </a>

            <div class="nav-section-title">System</div>
            <a href="{{ route('admin.notifications.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'admin.notifications') ? 'active' : '' }}">
                <i data-lucide="bell"></i>
                <span>Notification</span>
            </a>
            
        @else
            <a href="{{ route('user.dashboard') }}" class="nav-item {{ str_starts_with($currentRoute, 'user.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('user.borrowings.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'user.borrowings') ? 'active' : '' }}">
                <i data-lucide="book-open"></i>
                <span>My Borrowings</span>
            </a>
            <a href="{{ route('user.notifications.index') }}" class="nav-item {{ str_starts_with($currentRoute, 'user.notifications') ? 'active' : '' }}">
                <i data-lucide="bell"></i>
                <span>Notifications</span>
            </a>
        @endif
    </nav>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item logout-btn">
                <i data-lucide="log-out"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
