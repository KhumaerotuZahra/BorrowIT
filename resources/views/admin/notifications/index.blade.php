@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 class="page-title">Notifications</h1>
            <p class="page-subtitle">Stay updated with the latest activities</p>
        </div>
        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">
                <i data-lucide="check-check"></i>
                Mark All as Read
            </button>
        </form>
    </div>

    <div class="table-card">
        @forelse($notifications as $notif)
            <a href="{{ route('notifications.open', $notif) }}" style="text-decoration:none;color:inherit;display:flex;align-items:flex-start;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border-light);transition:background var(--transition);{{ $notif->isRead() ? '' : 'background:var(--accent-light);' }}">
                <div style="width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    @if($notif->type === 'new_request') background:var(--warning-light);color:var(--warning);
                    @elseif($notif->type === 'borrow_approved') background:var(--success-light);color:var(--success);
                    @elseif($notif->type === 'borrow_rejected') background:var(--danger-light);color:var(--danger);
                    @elseif($notif->type === 'borrow_returned') background:var(--info-light);color:var(--info);
                    @else background:var(--accent-light);color:var(--accent);
                    @endif
                ">
                    @if($notif->type === 'new_request')
                        <i data-lucide="file-plus"></i>
                    @elseif($notif->type === 'borrow_approved')
                        <i data-lucide="check-circle"></i>
                    @elseif($notif->type === 'borrow_rejected')
                        <i data-lucide="x-circle"></i>
                    @elseif($notif->type === 'borrow_returned')
                        <i data-lucide="undo-2"></i>
                    @elseif($notif->type === 'borrow_handover')
                        <i data-lucide="hand-metal"></i>
                    @else
                        <i data-lucide="bell"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:4px;">
                        <h4 style="font-size:14px;font-weight:600;">{{ $notif->title }}</h4>
                        <span style="font-size:11px;color:var(--text-muted);white-space:nowrap;">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p style="font-size:13px;color:var(--text-secondary);line-height:1.5;">{{ $notif->message }}</p>
                </div>
                @unless($notif->isRead())
                    <span style="font-size:11px;color:var(--accent);font-weight:600;align-self:center;">New</span>
                @endunless
            </a>
        @empty
            <div class="empty-state">
                <i data-lucide="bell-off"></i>
                <p class="empty-title">No notifications</p>
                <p class="empty-desc">You're all caught up!</p>
            </div>
        @endforelse
        @if($notifications->hasPages())
            <div class="pagination-wrapper">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
