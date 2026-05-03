@php
    $brandName = 'CyberShield';
@endphp

<x-mail::layout>
<x-slot:head>
<style>
body, .wrapper, .body {
    background: #f1f5f9 !important;
}

.inner-body {
    background: #ffffff !important;
    border: 1px solid #dbeafe !important;
    border-radius: 22px !important;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10) !important;
    overflow: hidden !important;
}

.content-cell {
    padding: 42px !important;
}

h1 {
    color: #0f172a !important;
    font-size: 26px !important;
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
    color: #22d3ee !important;
}

.action {
    margin: 36px auto !important;
}

.button,
.button-primary,
.button-blue {
    background: #0891b2 !important;
    border-top: 13px solid #0891b2 !important;
    border-right: 26px solid #0891b2 !important;
    border-bottom: 13px solid #0891b2 !important;
    border-left: 26px solid #0891b2 !important;
    border-radius: 14px !important;
    color: #ffffff !important;
    font-weight: 900 !important;
    letter-spacing: 0.02em !important;
    box-shadow: 0 14px 34px rgba(8, 145, 178, 0.22) !important;
}

.subcopy {
    border-top: 1px solid rgba(148, 163, 184, 0.14) !important;
}

.subcopy p {
    color: #64748b !important;
    font-size: 12px !important;
}
</style>
</x-slot:head>

<x-slot:header>
<tr>
<td style="padding: 38px 16px 24px; text-align: center;">
    <a href="{{ config('app.url') }}" style="display: inline-block; text-decoration: none;">
        <table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto;">
            <tr>
                <td>
                    <div style="height: 58px; width: 58px; border-radius: 18px 18px 24px 24px; background: #ecfeff; border: 1px solid #a5f3fc; color: #0891b2; font-size: 17px; font-weight: 900; line-height: 58px; text-align: center; box-shadow: 0 14px 34px rgba(8,145,178,.18);">
                        C/S
                    </div>
                </td>
                <td style="padding-left: 14px; text-align: left;">
                    <div style="color: #0f172a; font-size: 21px; font-weight: 900; line-height: 1.1;">
                        Cyber<span style="color:#0891b2;">Shield</span>
                    </div>
                    <div style="color: #0891b2; font-size: 10px; font-weight: 800; letter-spacing: 0.26em; margin-top: 7px; text-transform: uppercase;">
                        Enterprise SOC
                    </div>
                </td>
            </tr>
        </table>
    </a>
</td>
</tr>
</x-slot:header>

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 28px;">
    <tr>
        <td style="padding: 18px 22px; border-radius: 18px; background: #ecfeff; border: 1px solid #bae6fd;">
            <div style="color:#0891b2; font-size:11px; font-weight:900; letter-spacing:.22em; text-transform:uppercase;">
                Secure Access
            </div>
            <div style="margin-top:8px; color:#0f172a; font-size:18px; font-weight:900;">
                Professional invitation from your SOC workspace
            </div>
        </td>
    </tr>
</table>

{!! $slot !!}

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-top: 30px;">
    <tr>
        <td style="padding: 16px 18px; border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 13px; line-height: 1.6;">
            This link is intended only for the invited recipient. Ignore this message if you were not expecting access.
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
<td style="padding: 28px 16px 42px;">
    <table align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; width: 570px;">
        <tr>
            <td align="center" style="color: #64748b; font-size: 12px; line-height: 1.7; text-align: center;">
                <div style="font-weight: 900; color: #64748b;">{{ $brandName }}</div>
                <div style="margin-top: 4px;">Secure access notification from your SOC workspace.</div>
                <div style="margin-top: 10px;">&copy; {{ date('Y') }} {{ $brandName }}. {{ __('All rights reserved.') }}</div>
            </td>
        </tr>
    </table>
</td>
</tr>
</x-slot:footer>
</x-mail::layout>
