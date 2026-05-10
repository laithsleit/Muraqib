<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset your Muraqib password</title>
</head>
<body style="margin:0; padding:0; background:#f8fafc; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1e293b; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td align="center" style="padding:32px 32px 8px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 auto;">
                                <tr>
                                    <td style="vertical-align:middle; padding-right:12px;">
                                        <img src="{{ asset('assets/img/logo.png') }}" alt="" width="40" height="40" style="display:block; width:40px; height:40px; border:0;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span style="font-size:1.4rem; font-weight:700; color:#1e293b; letter-spacing:-0.02em;">Muraqib</span>
                                    </td>
                                </tr>
                            </table>
                            <h1 style="margin:24px 0 0 0; font-size:1.5rem; font-weight:700; color:#1e293b; letter-spacing:-0.02em; text-align:center;">Reset your password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 8px 32px;">
                            <p style="margin:0 0 16px 0; font-size:1rem; line-height:1.6; color:#1e293b;">Hi {{ $name }},</p>
                            <p style="margin:0 0 16px 0; font-size:0.95rem; line-height:1.6; color:#475569;">
                                We received a request to reset the password for your Muraqib account. Click the button below to choose a new one. This link will expire in {{ $expireMinutes }} minutes.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:8px 32px 24px 32px;">
                            <a href="{{ $url }}" style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none; font-weight:600; font-size:0.95rem; padding:12px 28px; border-radius:8px; line-height:1.2;">Reset password</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 24px 32px;">
                            <p style="margin:0 0 8px 0; font-size:0.85rem; line-height:1.6; color:#64748b;">
                                If the button doesn't work, copy this link into your browser:
                            </p>
                            <p style="margin:0; font-size:0.8rem; line-height:1.5; color:#4f46e5; word-break:break-all;">
                                <a href="{{ $url }}" style="color:#4f46e5; text-decoration:none;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 32px 32px; border-top:1px solid #e2e8f0;">
                            <p style="margin:20px 0 0 0; font-size:0.8rem; line-height:1.6; color:#64748b;">
                                If you didn't request a password reset, you can safely ignore this email — your password won't change.
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="margin:20px 0 0 0; font-size:0.75rem; color:#94a3b8;">
                    &copy; {{ date('Y') }} Muraqib · Smart Quiz Monitoring
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
