<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verification code</title>
</head>
<body style="margin:0;padding:0;background:#f4f7fa;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7fa;padding:40px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="480" cellspacing="0" cellpadding="0" style="background:#fff;border-radius:12px;overflow:hidden;">
                <tr>
                    <td style="background:#308e87;padding:24px 32px;text-align:center;">
                        <h1 style="color:#fff;margin:0;font-size:20px;">{{ __('Verification code') }}</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        <p style="color:#374151;font-size:15px;margin:0 0 16px;">Hi {{ $user->name }},</p>
                        <p style="color:#374151;font-size:15px;margin:0 0 16px;">
                            Use this code to confirm your
                            @if($purpose === 'password')
                                password change
                            @elseif($purpose === 'email')
                                new email address
                            @else
                                request
                            @endif
                            .
                            It expires in 10 minutes.
                        </p>
                        <p style="text-align:center;letter-spacing:8px;font-size:28px;font-weight:700;color:#308e87;margin:24px 0;">
                            {{ $code }}
                        </p>
                        <p style="color:#6b7280;font-size:13px;margin:0;">{{ __('If you did not request this, ignore this email.') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
