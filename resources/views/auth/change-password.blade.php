@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <div class="page-header">
        <h1 class="page-title">Change Password</h1>
        <p class="page-subtitle">Request a password reset link via email</p>
    </div>

    <div class="change-password-card">
        <div style="text-align:center;margin-bottom:24px;">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--accent-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <i data-lucide="mail" style="width:28px;height:28px;color:var(--accent);"></i>
            </div>
            <h3 style="font-size:18px;font-weight:600;margin-bottom:6px;">Password Reset via Email</h3>
            <p style="font-size:13px;color:var(--text-secondary);max-width:400px;margin:0 auto;">
                Click the button below to send a password reset link to your registered email address: <strong>{{ auth()->user()->email }}</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('change-password') }}">
            @csrf
            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
            <div style="display:flex; gap:10px; margin-top:24px; justify-content:center;">
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="send"></i>
                    Send Reset Link to Email
                </button>
                <a href="{{ url()->previous() }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>

        <div style="margin-top:24px;padding:16px;background:var(--surface-2);border-radius:var(--radius);font-size:12px;color:var(--text-muted);text-align:center;">
            <i data-lucide="info" style="width:14px;height:14px;display:inline;vertical-align:middle;"></i>
            A password reset link will be sent to your email. Check your inbox and follow the instructions.
        </div>
    </div>
@endsection
