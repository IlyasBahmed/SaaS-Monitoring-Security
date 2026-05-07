<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your project API key</title>
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
                                Project Integration
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:34px;">
                            <h1 style="margin:0 0 16px; font-size:24px; line-height:1.25; color:#0f172a;">
                                Your project API key is ready
                            </h1>

                            <p style="margin:0 0 18px; color:#475569; font-size:15px; line-height:1.7;">
                                Hello {{ $client->company_name ?? 'Client' }},
                            </p>

                            <p style="margin:0 0 22px; color:#475569; font-size:15px; line-height:1.7;">
                                A new project was created for your account. Use this API key to connect the project agent.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:0 0 22px; border:1px solid #e2e8f0; border-radius:14px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="color:#64748b; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.12em;">
                                            Project
                                        </div>
                                        <div style="margin-top:6px; color:#0f172a; font-size:16px; font-weight:800;">
                                            {{ $project->name }}
                                        </div>
                                        <div style="margin-top:4px; color:#64748b; font-size:13px;">
                                            {{ $project->domain ?: 'No domain provided' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin:0 0 22px; padding:18px; border-radius:14px; background:#020617; color:#67e8f9; font-size:14px; line-height:1.6; font-family:Consolas, Monaco, monospace; word-break:break-all;">
                                {{ $apiKey }}
                            </div>

                            <p style="margin:0; padding:14px 16px; border-radius:14px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; font-size:13px; line-height:1.6;">
                                Keep this key private. Do not share it publicly or commit it into your source code.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 34px; background:#f8fafc; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; line-height:1.6;">
                            This message was sent by CyberShield for a project connected to your client account.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
