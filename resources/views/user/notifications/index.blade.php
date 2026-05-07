@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Your recent notifications</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">All Notifications</h3>
        </div>
        @forelse($notifications as $notif)
            <a href="{{ route('notifications.open', $notif) }}" style="text-decoration:none;color:inherit;display:flex;align-items:flex-start;gap:12px;padding:16px 24px;border-bottom:1px solid var(--border-light);{{ $notif->isRead() ? '' : 'background:var(--accent-light);' }}">
                <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    @if($notif->type === 'borrow_approved') background:var(--success-light);color:var(--success);
                    @elseif($notif->type === 'borrow_rejected') background:var(--danger-light);color:var(--danger);
                    @elseif($notif->type === 'borrow_handover') background:var(--accent-light);color:var(--accent);
                    @elseif($notif->type === 'borrow_returned') background:var(--info-light);color:var(--info);
                    @elseif($notif->type === 'borrow_overdue') background:var(--danger-light);color:var(--danger);
                    @else background:var(--warning-light);color:var(--warning);
                    @endif
                ">
                    @if($notif->type === 'borrow_approved')
                        <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                    @elseif($notif->type === 'borrow_rejected')
                        <i data-lucide="x-circle" style="width:18px;height:18px;"></i>
                    @elseif($notif->type === 'borrow_handover')
                        <i data-lucide="hand-metal" style="width:18px;height:18px;"></i>
                    @elseif($notif->type === 'borrow_returned')
                        <i data-lucide="undo-2" style="width:18px;height:18px;"></i>
                    @elseif($notif->type === 'borrow_overdue')
                        <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
                    @else
                        <i data-lucide="bell" style="width:18px;height:18px;"></i>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;margin-bottom:2px;">{{ $notif->title }}</div>
                    <div style="font-size:13px;color:var(--text-secondary);line-height:1.5;">{{ $notif->message }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
                </div>
                @if(!$notif->isRead())
                    <span style="font-size:11px;color:var(--accent);font-weight:600;align-self:center;">New</span>
                @else
                    <span style="font-size:11px;color:var(--text-muted);align-self:center;">Read</span>
                @endif
            </a>
        @empty
            <div class="empty-state" style="padding:48px 24px;">
                <i data-lucide="bell-off"></i>
                <p class="empty-title">No notifications</p>
                <p class="empty-desc">You're all caught up!</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="pagination-wrapper">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
