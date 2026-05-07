@php
    $brandName = 'CyberShield';
@endphp

<x-mail::layout>
<x-slot:head>
<style>
body, .wrapper, .body {
    background: #eef4f8 !important;
}

.inner-body {
    background: #ffffff !important;
    border: 1px solid #dbeafe !important;
    border-radius: 26px !important;
    box-shadow: 0 30px 90px rgba(15, 23, 42, 0.12) !important;
    overflow: hidden !important;
}

.content-cell {
    padding: 44px !important;
}

h1 {
    color: #0f172a !important;
    font-size: 28px !important;
    font-weight: 900 !important;
    line-height: 1.2 !important;
    margin-bottom: 18px !important;
}

p {
    color: #475569 !important;
    font-size: 15px !important;
    line-height: 1.75 !important;
}

a {
    color: #0891b2 !important;
}

.action {
    margin: 38px auto !important;
}

.button,
.button-primary,
.button-blue {
    background: linear-gradient(135deg, #06b6d4, #0891b2) !important;
    border-top: 14px solid #06b6d4 !important;
    border-right: 30px solid #0891b2 !important;
    border-bottom: 14px solid #0891b2 !important;
    border-left: 30px solid #06b6d4 !important;
    border-radius: 16px !important;
    color: #ffffff !important;
    font-weight: 900 !important;
    letter-spacing: 0.04em !important;
    text-transform: uppercase !important;
    box-shadow: 0 18px 38px rgba(8, 145, 178, 0.28) !important;
}

.subcopy {
    border-top: 1px solid rgba(148, 163, 184, 0.16) !important;
}

.subcopy p {
    color: #64748b !important;
    font-size: 12px !important;
}

.footer td {
    color: #64748b !important;
}
</style>
</x-slot:head>

<x-slot:header>
<tr>
<td style="padding: 42px 16px 26px; text-align: center;">
    <a href="{{ config('app.url') }}" style="display: inline-block; text-decoration: none;">
        <table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto;">
            <tr>
                <td>
                    <div style="height: 62px; width: 62px; border-radius: 20px 20px 28px 28px; background: linear-gradient(135deg,#ecfeff,#dbeafe); border: 1px solid #a5f3fc; color: #0891b2; font-size: 18px; font-weight: 900; line-height: 62px; text-align: center; box-shadow: 0 16px 36px rgba(8,145,178,.20);">
                        C/S
                    </div>
                </td>

                <td style="padding-left: 15px; text-align: left;">
                    <div style="color: #0f172a; font-size: 22px; font-weight: 900; line-height: 1.1;">
                        Cyber<span style="color:#0891b2;">Shield</span>
                    </div>
                    <div style="color: #0891b2; font-size: 10px; font-weight: 900; letter-spacing: 0.28em; margin-top: 8px; text-transform: uppercase;">
                        Enterprise SOC
                    </div>
                </td>
            </tr>
        </table>
    </a>
</td>
</tr>
</x-slot:header>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 30px;">
    <tr>
        <td style="padding: 20px 24px; border-radius: 20px; background: linear-gradient(135deg,#ecfeff,#f8fafc); border: 1px solid #bae6fd;">
            <div style="color:#0891b2; font-size:11px; font-weight:900; letter-spacing:.24em; text-transform:uppercase;">
                Secure Access Invitation
            </div>
            <div style="margin-top:9px; color:#0f172a; font-size:19px; font-weight:900; line-height:1.35;">
                Activate your CyberShield workspace account
            </div>
            <div style="margin-top:7px; color:#64748b; font-size:13px; line-height:1.6;">
                Define your password to complete your access securely.
            </div>
        </td>
    </tr>
</table>

{!! $slot !!}

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 32px;">
    <tr>
        <td style="padding: 17px 19px; border-radius: 18px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 13px; line-height: 1.65;">
            <strong>Security notice:</strong> this invitation link is personal and should not be shared. If you were not expecting this email, you can safely ignore it.
        </td>
    </tr>
</table>

@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

<x-slot:footer>
<tr>
<td style="padding: 30px 16px 44px;">
    <table align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; width: 570px;">
        <tr>
            <td align="center" style="color: #64748b; font-size: 12px; line-height: 1.7; text-align: center;">
                <div style="font-weight: 900; color: #334155;">{{ $brandName }}</div>
                <div style="margin-top: 5px;">Secure access notification from your SOC workspace.</div>
                <div style="margin-top: 10px;">&copy; {{ date('Y') }} {{ $brandName }}. {{ __('All rights reserved.') }}</div>
            </td>
        </tr>
    </table>
</td>
</tr>
</x-slot:footer>
</x-mail::layout>