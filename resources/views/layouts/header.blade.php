<header class="top-header">
    <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle sidebar">
        <i data-lucide="menu"></i>
    </button>
    <div class="header-right">
        <div class="notification-wrapper" id="notification-wrapper">
            <button class="header-icon-btn" id="notification-btn" aria-label="Notifications">
                <i data-lucide="bell"></i>
                <span class="notification-badge" id="notification-badge" style="display:none;">0</span>
            </button>
            <div class="notification-dropdown" id="notification-dropdown" style="display:none;">
                <div class="notif-header">
                    <h4>Notifications</h4>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.notifications.index') : '#' }}">View All</a>
                </div>
                <div class="notif-list" id="notif-list">
                    <p class="notif-empty">No notifications</p>
                </div>
            </div>
        </div>
        <div class="user-profile-wrapper" id="user-profile-wrapper">
            <button class="user-profile" id="user-profile-btn">
                <div class="avatar-circle">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="user-name">{{ auth()->user()->name }}</span>
                <i data-lucide="chevron-down" class="chevron"></i>
            </button>
            <div class="profile-dropdown" id="profile-dropdown" style="display:none;">
                <a href="{{ route('change-password') }}" class="dropdown-item">
                    <i data-lucide="key"></i>
                    <span>Change Password</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
