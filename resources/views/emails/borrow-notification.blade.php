<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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

                    <!-- Icon Badge -->
                    <tr>
                        <td align="center" style="padding:28px 0 0;">
                            @php
                                $iconColor = match($type) {
                                    'approved', 'returned', 'handover' => '#10b981',
                                    'rejected' => '#ef4444',
                                    'overdue' => '#f59e0b',
                                    default => '#3b82f6',
                                };
                                $iconBg = match($type) {
                                    'approved', 'returned', 'handover' => '#ecfdf5',
                                    'rejected' => '#fef2f2',
                                    'overdue' => '#fffbeb',
                                    default => '#eff6ff',
                                };
                                $icon = match($type) {
                                    'approved' => '✅',
                                    'rejected' => '❌',
                                    'handover' => '📦',
                                    'returned' => '✔️',
                                    'overdue' => '⚠️',
                                    default => '📋',
                                };
                            @endphp
                            <div style="display:inline-block;width:56px;height:56px;border-radius:50%;background:{{ $iconBg }};line-height:56px;font-size:28px;text-align:center;">
                                {{ $icon }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:20px 36px 10px;">
                            <h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#0f172a;text-align:center;">
                                {{ $title }}
                            </h2>
                            <p style="margin:0 0 20px;font-size:14px;color:#64748b;text-align:center;line-height:1.6;">
                                Hello <strong style="color:#0f172a;">{{ $userName }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#475569;line-height:1.7;text-align:center;">
                                {{ $body }}
                            </p>
                        </td>
                    </tr>

                    <!-- Details Card -->
                    @if(!empty($details))
                    <tr>
                        <td style="padding:0 36px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <p style="margin:0 0 12px;font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;">Details</p>
                                        @foreach($details as $label => $value)
                                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:8px;">
                                                <tr>
                                                    <td style="font-size:13px;color:#64748b;padding:4px 0;width:40%;">{{ $label }}</td>
                                                    <td style="font-size:13px;color:#0f172a;font-weight:600;padding:4px 0;">{{ $value }}</td>
                                                </tr>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding:0 36px 28px;">
                            <a href="{{ url('/') }}" style="display:inline-block;background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#ffffff;text-decoration:none;padding:12px 36px;border-radius:8px;font-size:14px;font-weight:600;letter-spacing:0.3px;">
                                Open BorrowIT
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc;padding:22px 36px;border-top:1px solid #e2e8f0;text-align:center;">
                            <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">
                                This is an automated notification from <strong>BorrowIT</strong>
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
