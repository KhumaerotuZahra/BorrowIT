<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);padding:28px 36px;text-align:center;">
                            <h1 style="margin:0;font-size:24px;font-weight:700;color:#ffffff;letter-spacing:0.5px;">
                                Borrow<span style="color:#38bdf8;">IT</span>
                            </h1>
                            <p style="margin:6px 0 0;font-size:12px;color:#94a3b8;letter-spacing:1px;">ASSET MANAGEMENT SYSTEM</p>
                        </td>
                    </tr>

                    <!-- Icon -->
                    <tr>
                        <td align="center" style="padding:28px 0 0;">
                            <div style="display:inline-block;width:56px;height:56px;border-radius:50%;background:#eff6ff;line-height:56px;font-size:28px;text-align:center;">
                                🔐
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:20px 36px 10px;">
                            <h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#0f172a;text-align:center;">
                                Reset Your Password
                            </h2>
                            <p style="margin:0 0 20px;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
                                Hello <strong style="color:#0f172a;">{{ $name }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#475569;line-height:1.7;text-align:center;">
                                We received a request to reset your password for your BorrowIT account. Click the button below to set a new password:
                            </p>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding:0 36px 20px;">
                            <a href="{{ url('/reset-password/' . $token . '?email=' . urlencode($email)) }}" style="display:inline-block;background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#ffffff;text-decoration:none;padding:14px 40px;border-radius:8px;font-size:14px;font-weight:600;letter-spacing:0.3px;">
                                Reset Password
                            </a>
                        </td>
                    </tr>

                    <!-- Note -->
                    <tr>
                        <td style="padding:0 36px 28px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding:16px 20px;text-align:center;">
                                        <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6;">
                                            This link will expire in <strong>60 minutes</strong>.<br>
                                            If you did not request a password reset, no action is required.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc;padding:22px 36px;border-top:1px solid #e2e8f0;text-align:center;">
                            <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">
                                This is an automated email from <strong>BorrowIT</strong>
                            </p>
                            <p style="margin:0;font-size:11px;color:#cbd5e1;">
                                PT BPI &mdash; Asset Management System &copy; {{ date('Y') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
