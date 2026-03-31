<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        .container { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #0a0f2e, #1a2a5e); padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 24px; margin: 0; font-weight: 700; }
        .body { padding: 32px; }
        .body p { color: #475569; font-size: 14px; line-height: 1.7; margin: 0 0 16px; }
        .btn { display: inline-block; background: #0ea5e9; color: #ffffff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 14px; font-weight: 600; margin: 16px 0; }
        .footer { padding: 24px 32px; border-top: 1px solid #e2e6ed; text-align: center; }
        .footer p { color: #94a3b8; font-size: 12px; margin: 0; }
        .note { background: #f8f9fc; border-radius: 8px; padding: 14px 18px; margin-top: 16px; }
        .note p { font-size: 12px; color: #64748b; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BorrowIT</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>We received a request to reset your password for your BorrowIT account. Click the button below to set a new password:</p>
            <p style="text-align: center;">
                <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($email)) }}" class="btn">Reset Password</a>
            </p>
            <div class="note">
                <p>This link will expire in <strong>60 minutes</strong>. If you did not request a password reset, no action is required.</p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} BorrowIT - PT BPI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
