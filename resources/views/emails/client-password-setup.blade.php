<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Define your password</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#f1f5f9; padding:34px 14px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:620px; background:#ffffff; border:1px solid #dbeafe; border-radius:20px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 34px; background:#ecfeff; border-bottom:1px solid #bae6fd;">
                            <div style="font-size:22px; font-weight:900; color:#0f172a;">
                                Cyber<span style="color:#0891b2;">Shield</span>
                            </div>
                            <div style="margin-top:8px; color:#0891b2; font-size:11px; font-weight:800; letter-spacing:.18em; text-transform:uppercase;">
                                Client Access
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:34px;">
                            <h1 style="margin:0 0 16px; font-size:24px; line-height:1.25; color:#0f172a;">
                                Define your password
                            </h1>

                            <p style="margin:0 0 18px; color:#475569; font-size:15px; line-height:1.7;">
                                Hello {{ $clientName }},
                            </p>

                            <p style="margin:0 0 24px; color:#475569; font-size:15px; line-height:1.7;">
                                Your CyberShield client account has been created. Use the button below to define your password and activate your access.
                            </p>

                            <p style="margin:0 0 28px;">
                                <a href="{{ $setupUrl }}" style="display:inline-block; border-radius:14px; background:#0891b2; padding:14px 24px; color:#ffffff; font-size:14px; font-weight:900; text-decoration:none;">
                                    Define Password
                                </a>
                            </p>

                            <p style="margin:0; padding:14px 16px; border-radius:14px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:13px; line-height:1.6;">
                                If you were not expecting this account, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 34px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; line-height:1.6;">
                            This message was sent by CyberShield for your client account setup.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
