@extends('layouts.app')

@section('title', 'Active Borrows')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Active Borrows</h1>
        <p class="page-subtitle">Track and manage currently borrowed assets</p>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3 class="table-title">Active Loans</h3>
            <div class="table-actions">
                <form method="GET" action="{{ route('admin.active-borrows.index') }}" class="search-box">
                    <i data-lucide="search"></i>
                    <input type="text" name="search" placeholder="Search..." value="{{ request('search') }}">
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Borrow ID</th>
                        <th>Request ID</th>
                        <th>User</th>
                        <th>Asset</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Handover PIC</th>
                        <th>Status</th>
                        <th>Return PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $index => $borrow)
                        <tr>
                            <td class="font-mono">BRW-{{ str_pad($borrow->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="font-mono">REQ-{{ str_pad($borrow->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="avatar-circle" style="width:30px;height:30px;font-size:11px;">{{ strtoupper(substr($borrow->user->name, 0, 1)) }}</div>
                                    <div>
                                        <div style="font-weight:500;">{{ $borrow->user->name }}</div>
                                        <div style="font-size:11px;color:var(--text-muted);">{{ $borrow->user->department }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $borrow->asset->asset_name }}</div>
                                <div class="font-mono" style="font-size:11px;color:var(--text-muted);">{{ $borrow->asset->asset_id }}</div>
                            </td>
                            <td style="font-size:12px;">{{ $borrow->borrow_date->format('d M Y') }}</td>
                            <td style="font-size:12px;">
                                {{ $borrow->due_date->format('d M Y') }}
                                @if($borrow->status === 'overdue')
                                    <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">
                                        {{ $borrow->due_date->diffForHumans() }}
                                    </div>
                                @endif
                            </td>
                            {{-- Handover PIC - Select Employee IT --}}
                            <td>
                                @if(in_array($borrow->status, ['active', 'overdue']))
                                    <form method="POST" action="{{ route('admin.active-borrows.update', $borrow) }}" class="inline-form">
                                        @csrf
                                        @method('PUT')
                                        <select name="handover_by" class="form-control form-control-sm" style="min-width:130px;padding:5px 8px;font-size:12px;" onchange="this.form.submit()">
                                            <option value="">Select PIC</option>
                                            @foreach($itEmployees as $emp)
                                                <option value="{{ $emp->name }}" {{ $borrow->handover_by == $emp->name ? 'selected' : '' }}>{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <span style="font-size:12px;">{{ $borrow->handover_by ?? '-' }}</span>
                                @endif
                            </td>
                            {{-- Status - Select with auto overdue --}}
                            <td>
                                @if($borrow->status === 'overdue')
                                    <span class="badge badge-overdue">Overdue</span>
                                    <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">Auto-detected</div>
                                @elseif(in_array($borrow->status, ['active']))
                                    <form method="POST" action="{{ route('admin.active-borrows.update', $borrow) }}" class="inline-form">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-control form-control-sm" style="min-width:110px;padding:5px 8px;font-size:12px;" onchange="this.form.submit()">
                                            <option value="active" {{ $borrow->status == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="returned">Returned</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="badge badge-{{ $borrow->status }}">{{ ucfirst($borrow->status) }}</span>
                                @endif
                            </td>
                            {{-- Return PIC - Select Employee IT --}}
                            <td>
                                @if(in_array($borrow->status, ['active', 'overdue']))
                                    <form method="POST" action="{{ route('admin.active-borrows.update', $borrow) }}" class="inline-form">
                                        @csrf
                                        @method('PUT')
                                        <select name="return_pic" class="form-control form-control-sm" style="min-width:130px;padding:5px 8px;font-size:12px;" onchange="this.form.submit()">
                                            <option value="">Select PIC</option>
                                            @foreach($itEmployees as $emp)
                                                <option value="{{ $emp->name }}" {{ $borrow->return_pic == $emp->name ? 'selected' : '' }}>{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                @elseif($borrow->status === 'returned')
                                    <span style="font-size:12px;">{{ $borrow->return_pic ?? '-' }}</span>
                                @else
                                    <span style="font-size:12px;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty-state">
                                <i data-lucide="inbox"></i>
                                <p class="empty-title">No active borrows</p>
                                <p class="empty-desc">Active borrows will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($borrowings->hasPages())
            <div class="pagination-wrapper">
                {{ $borrowings->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
