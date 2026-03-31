@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ auth()->user()->name }}!</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card accent">
            <div class="stat-icon"><i data-lucide="repeat"></i></div>
            <div class="stat-value">{{ $activeBorrows }}</div>
            <div class="stat-label">My Active Borrows</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-icon"><i data-lucide="alert-triangle"></i></div>
            <div class="stat-value">{{ $overdueCount }}</div>
            <div class="stat-label">Overdue</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i data-lucide="clock"></i></div>
            <div class="stat-value">{{ $pendingRequests }}</div>
            <div class="stat-label">Pending Requests</div>
        </div>
    </div>

    <div class="content-grid">
        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">My Borrowed Assets</h3>
                <a href="{{ route('user.borrowings.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Qty</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myBorrowedAssets as $borrow)
                            <tr>
                                <td>
                                    <div style="font-weight:500;">{{ $borrow->asset->asset_name }}</div>
                                    <div class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $borrow->asset->asset_id }}</div>
                                </td>
                                <td>{{ $borrow->quantity }}</td>
                                <td style="font-size:12px;">
                                    {{ $borrow->due_date->format('d M Y') }}
                                    @if($borrow->status === 'overdue')
                                        <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">
                                            {{ $borrow->due_date->diffForHumans() }}
                                        </div>
                                    @endif
                                </td>
                                <td><span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                <div class="empty-state">
                                    <i data-lucide="package-open"></i>
                                    <p class="empty-title">No borrowed assets</p>
                                    <p class="empty-desc">Your active borrowings will appear here.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Recent Notifications</h3>
            </div>
            @forelse($notifications as $notif)
                <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 24px;border-bottom:1px solid var(--border-light);{{ $notif->isRead() ? '' : 'background:var(--accent-light);' }}">
                    <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                        @if($notif->type === 'borrow_approved') background:var(--success-light);color:var(--success);
                        @elseif($notif->type === 'borrow_rejected') background:var(--danger-light);color:var(--danger);
                        @elseif($notif->type === 'borrow_handover') background:var(--accent-light);color:var(--accent);
                        @elseif($notif->type === 'borrow_returned') background:var(--info-light);color:var(--info);
                        @else background:var(--warning-light);color:var(--warning);
                        @endif
                    ">
                        @if($notif->type === 'borrow_approved')
                            <i data-lucide="check-circle" style="width:16px;height:16px;"></i>
                        @elseif($notif->type === 'borrow_rejected')
                            <i data-lucide="x-circle" style="width:16px;height:16px;"></i>
                        @elseif($notif->type === 'borrow_handover')
                            <i data-lucide="hand-metal" style="width:16px;height:16px;"></i>
                        @elseif($notif->type === 'borrow_returned')
                            <i data-lucide="undo-2" style="width:16px;height:16px;"></i>
                        @else
                            <i data-lucide="bell" style="width:16px;height:16px;"></i>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:2px;">{{ $notif->title }}</div>
                        <div style="font-size:12px;color:var(--text-secondary);line-height:1.4;">{{ $notif->message }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i data-lucide="bell-off"></i>
                    <p class="empty-title">No notifications</p>
                    <p class="empty-desc">You're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
