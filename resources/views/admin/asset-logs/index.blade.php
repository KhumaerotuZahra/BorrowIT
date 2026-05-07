@extends('layouts.app')

@section('title', 'Asset Logs')

@section('content')
<div class="page-header">
    <h1 class="page-title">Asset Logs</h1>
    <p class="page-subtitle">Track all asset activities</p>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>User</th>
                    <th>Asset</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $index => $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $index }}</td>

                        <!-- USER -->
                        <td>
                            <span style="font-weight:500;">
                                {{ $log->user->name ?? '-' }}
                            </span>
                        </td>

                        <!-- ASSET -->
                        <td>
                            <span class="font-mono">
                                {{ $log->asset_name ?? '-' }}
                            </span>
                        </td>

                        <!-- ACTION -->
                        <td>
                            @if($log->action === 'create')
                                <span class="badge badge-create">
                                    <i data-lucide="plus-circle"></i> Create
                                </span>
                            @elseif($log->action === 'update')
                                <span class="badge badge-update">
                                    <i data-lucide="refresh-cw"></i> Update
                                </span>
                            @elseif($log->action === 'delete')
                                <span class="badge badge-delete">
                                    <i data-lucide="trash-2"></i> Delete
                                </span>
                            @elseif($log->action === 'import')
                                <span class="badge badge-import">
                                    <i data-lucide="upload"></i> Import
                                </span>
                            @else
                                <span class="badge">
                                    {{ ucfirst($log->action) }}
                                </span>
                            @endif
                        </td>

                        <!-- DESCRIPTION -->
                        <td style="max-width:300px;">
                            <span style="color:var(--text-secondary); font-size:13px;">
                                {{ $log->description }}
                            </span>
                        </td>

                        <!-- DATE -->
                        <td>
                            <span style="font-size:13px;">
                                {{ $log->created_at->format('d M Y') }}<br>
                                <small style="color:var(--text-muted);">
                                    {{ $log->created_at->format('H:i') }}
                                </small>
                            </span>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i data-lucide="history"></i>
                                <p class="empty-title">No logs yet</p>
                                <p class="empty-desc">All asset activities will appear here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    @if($logs->hasPages())
        <div class="pagination-wrapper">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection