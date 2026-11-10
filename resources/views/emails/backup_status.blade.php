<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup</title>
</head>
<body style="margin:0;padding:0;background:#f4f7f7;font-family:Segoe UI,Tahoma,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7f7;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="520" cellspacing="0" cellpadding="0" style="background:#fff;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="background:#308e87;padding:24px 32px;">
                            <h1 style="color:#fff;margin:0;font-size:20px;">
                                {{ $success ? __('Backup completed') : __('Backup failed') }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px;color:#374151;font-size:15px;line-height:1.6;">
                            <p style="margin:0 0 12px;">{{ $company }}</p>
                            <p style="margin:0 0 12px;">{{ $run->message }}</p>
                            @if ($run->filename)
                                <p style="margin:0 0 12px;">File: {{ $run->filename }} ({{ $run->sizeLabel() }})</p>
                            @endif
                            <p style="margin:0;color:#6b7280;font-size:13px;">The backup file is not attached. Download it from Settings, Backup, while signed in.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
