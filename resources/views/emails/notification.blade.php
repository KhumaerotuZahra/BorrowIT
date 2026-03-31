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
        .badge { display: inline-block; padding: 4px 12px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .badge-info { background: #e0f2fe; color: #0284c7; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .footer { padding: 24px 32px; border-top: 1px solid #e2e6ed; text-align: center; }
        .footer p { color: #94a3b8; font-size: 12px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BorrowIT</h1>
        </div>
        <div class="body">
            <p>Hello <strong>{{ $notifData['name'] }}</strong>,</p>
            <p>{!! $notifData['message'] !!}</p>

            @if(isset($notifData['details']))
                <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                    @foreach($notifData['details'] as $key => $value)
                        <tr>
                            <td style="padding:8px 12px;font-size:13px;color:#64748b;border-bottom:1px solid #f1f5f9;width:40%;">{{ $key }}</td>
                            <td style="padding:8px 12px;font-size:13px;color:#1a1d2e;font-weight:500;border-bottom:1px solid #f1f5f9;">{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if(isset($notifData['action_url']))
                <p style="text-align:center;margin-top:24px;">
                    <a href="{{ $notifData['action_url'] }}" style="display:inline-block;background:#0ea5e9;color:#ffffff !important;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600;">
                        {{ $notifData['action_text'] ?? 'View Details' }}
                    </a>
                </p>
            @endif
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} BorrowIT - PT BPI. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
