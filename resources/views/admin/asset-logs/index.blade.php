@extends('layouts.app')

@section('title', 'Asset Logs')

@section('content')
<div class="page-header">
    <h1 class="page-title">Asset Logs</h1>
    <p class="page-subtitle">Track all asset activities</p>
</div>

<div class="table-card">

    <div class="table-header">
        <h3 class="table-title">Asset Activity Logs</h3>

        <div class="table-actions">
            <form method="GET"
                    action="{{route('admin.asset-logs.index')}}"
                    style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">

                    <div class="search-box">
                        <i data-lucide="search"></i>
                        <input type="text"
                                name="search"
                                placeholder="Search user or asset..."
                                value="{{request('search')}}">
                    </div>
                    <select name="action" class="form-control" onchange="this.form.submit()" style="width:auto;">
                        <option value="">All Action</option>
                        <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                        <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                        <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                        <option value="import" {{ request('action') == 'import' ? 'selected' : '' }}>Import</option>
                    </select>
            </form>
        </div>
    </div>
                
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
                            <span style="font-weight:500;">
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
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection