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
                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter your current password">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter new password (min 8 characters)">
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your new password">
            </div>
            <div style="display:flex; justify-content:center; gap:10px; margin-top:24px;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i>
                    Update Password
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
