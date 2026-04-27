document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    const notifBtn = document.getElementById('notification-btn');
    const notifDropdown = document.getElementById('notification-dropdown');
    const profileBtn = document.getElementById('user-profile-btn');
    const profileDropdown = document.getElementById('profile-dropdown');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = notifDropdown.style.display === 'block';
            closeAllDropdowns();
            if (!isOpen) {
                notifDropdown.style.display = 'block';
                loadNotifications();
            }
        });
    }

    if (profileBtn && profileDropdown) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = profileDropdown.style.display === 'block';
            closeAllDropdowns();
            if (!isOpen) {
                profileDropdown.style.display = 'block';
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (notifDropdown && !notifDropdown.contains(e.target) && !notifBtn.contains(e.target)) {
            notifDropdown.style.display = 'none';
        }
        if (profileDropdown && !profileDropdown.contains(e.target) && !profileBtn.contains(e.target)) {
            profileDropdown.style.display = 'none';
        }
    });

    function closeAllDropdowns() {
        if (notifDropdown) notifDropdown.style.display = 'none';
        if (profileDropdown) profileDropdown.style.display = 'none';
    }

    loadUnreadCount();
    setInterval(loadUnreadCount, 30000);

    function loadUnreadCount() {
        fetch(window.baseUrl + '/notifications/unread-count')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notification-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(() => {});
    }

    function loadNotifications() {
        fetch(window.baseUrl + '/notifications/latest')
            .then(r => r.json())
            .then(notifications => {
                const list = document.getElementById('notif-list');
                if (!list) return;

                if (notifications.length === 0) {
                    list.innerHTML = '<p class="notif-empty">No notifications yet</p>';
                    return;
                }

                list.innerHTML = notifications.map(n => {
                    const isUnread = !n.read_at;
                    const timeAgo = getTimeAgo(new Date(n.created_at));
                    return `
                        <div class="notif-item ${isUnread ? 'unread' : ''}">
                            <div class="notif-icon"><i data-lucide="bell"></i></div>
                            <div class="notif-body">
                                <div class="notif-title">${escapeHtml(n.title)}</div>
                                <div class="notif-msg">${escapeHtml(n.message)}</div>
                                <div class="notif-time">${timeAgo}</div>
                            </div>
                        </div>
                    `;
                }).join('');

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(() => {});
    }

    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 60) return 'Just now';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + 'm ago';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h ago';
        const days = Math.floor(hours / 24);
        return days + 'd ago';
    }

    function escapeHtml(text) {
        const d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }
});

function openModal(id) {
    document.getElementById('modal-overlay').style.display = 'block';
    document.getElementById(id).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeAllModals() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAllModals();
});
