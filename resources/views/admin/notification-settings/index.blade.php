@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Notification Settings</h1>
        <p class="page-subtitle">Choose which notifications are emailed to admins and users. Web notifications are always shown.</p>
    </div>

    <form method="POST" action="{{ route('admin.notification-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="table-card">
            <div class="table-header">
                <h3 class="table-title">Email Notification Setting</h3>
                <div class="table-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="save"></i>
                        Save Changes
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>List Notif</th>
                            <th style="width:120px;text-align:center;">Admin</th>
                            <th style="width:120px;text-align:center;">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settings as $setting)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $setting->label }}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">{{ $setting->type }}</div>
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox"
                                           name="admin[{{ $setting->id }}]"
                                           value="1"
                                           {{ $setting->admin_email ? 'checked' : '' }}
                                           style="width:18px;height:18px;cursor:pointer;">
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox"
                                           name="user[{{ $setting->id }}]"
                                           value="1"
                                           {{ $setting->user_email ? 'checked' : '' }}
                                           style="width:18px;height:18px;cursor:pointer;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px 24px;border-top:1px solid var(--border-light);font-size:13px;color:var(--text-secondary);">
                <strong>How it works:</strong>
                Checked = email is sent to that recipient. Unchecked = email is not sent.
                Web notifications inside BorrowIT are not affected by this setting.
            </div>
        </div>
    </form>
@endsection
