@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Change Password</h1>
        <p class="page-subtitle">Update your account password</p>
    </div>

    <div class="change-password-card">
        <form method="POST" action="{{ route('change-password') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>

                <div style="position: relative;">
                    <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter your current password" style="padding-right:45px;">

                    <button type="button" onclick="togglePassword('current_password', 'eye1')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted);">
                        <i data-lucide="eye" id="eye1"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>

                <div style="position: relative;">
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter new password (min 8 characters)" style="padding-right:45px;">

                    <button type="button" onclick="togglePassword('password', 'eye2')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted);">
                        <i data-lucide="eye" id="eye2"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>

                <div style="position: relative;">
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your new password" style="padding-right:45px;">

                    <button type="button" onclick="togglePassword('password_confirmation', 'eye3')" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted);">
                        <i data-lucide="eye" id="eye3"></i>
                    </button>
                </div>
            </div>

            <div style="display:flex; justify-content:center; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i>
                    Update Password
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }

            lucide.createIcons();
        }
    </script>
@endsection