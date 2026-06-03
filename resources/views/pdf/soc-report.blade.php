<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SOC Executive Report | Armious Protect</title>
<style>
    @page {
        size: A4;
        margin: 15mm 14mm 12mm 14mm; /* top right bottom left */
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 12px;
        line-height: 1.45;
        background: #eef2f7;
        color: #172033;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #ffffff;
        padding: 15mm 14mm 12mm;
        box-sizing: border-box;
        overflow: visible;
        page-break-after: always;
    }

    .top-rule {
        height: 5px;
        background: #0f766e;
        margin: -15mm -14mm 12px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        padding-bottom: 12px;
        border-bottom: 1px solid #d8e0ea;
        flex-wrap: wrap;
    }

    .header-left .brand {
        color: #0f766e;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .header-left h1 {
        margin: 0;
        font-size: 28px;
        color: #101827;
        line-height: 1.05;
        letter-spacing: -0.5px;
    }

    .header-left .subtitle {
        margin-top: 8px;
        font-size: 12px;
        color: #5d6b7b;
    }

    .header-right {
        text-align: right;
        color: #526174;
        font-size: 10px;
    }

    .header-right .classification {
        display: inline-block;
        margin-top: 8px;
        padding: 5px 9px;
        border: 1px solid #b6d8d2;
        background: #ecfdf5;
        color: #0f766e;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.8px;
    }

    h2 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #172033;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    table.data th,
    table.data td {
        border: 1px solid #d8e0ea;
        padding: 7px 8px;
        font-size: 10px;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    table.data th {
        background: #f1f5f9;
        color: #334155;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .section {
        margin-top: 16px;
        page-break-inside: avoid;
    }

    .kpis, .recommendations, .executive, .grid-2 {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .kpi, .rec, .panel, .score-card, .summary-card {
        flex: 1 1 calc(50% - 8px);
        min-width: 150px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .footer {
        margin-top: 14px;
        padding-top: 10px;
        border-top: 1px solid #d8e0ea;
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        color: #64748b;
    }

    @media print {
        body { background: #ffffff; }
        .page { box-shadow: none; overflow: visible; }
        .section, .panel, .rec, .kpi, .score-card, .summary-card { break-inside: avoid; page-break-inside: avoid; }
    }
</style>
</head>
<body>
<div class="page">
    <div class="top-rule"></div>
    <div class="header">
        <div class="header-left">
            <div class="brand">Armious Protect</div>
            <h1>SOC Executive Report</h1>
            <div class="subtitle">Security posture, threat activity, exposure, and response summary.</div>
        </div>
        <div class="header-right">
            <div><strong>Report ID:</strong> SOC-20260603</div>
            <div><strong>Period:</strong> Last 30 Days</div>
            <div><strong>Range:</strong> 2026-05-03 to 2026-06-03</div>
            <div><strong>Generated:</strong> 2026-06-03 12:00</div>
            <div class="classification">TLP:CLEAR - CONFIDENTIAL</div>
        </div>
    </div>

    <!-- Place your content here -->
    <p>Content will go here, including KPI cards, tables, charts, etc.</p>

    <div class="footer">
        <span>Armious Protect SOC - Executive security report</span>
        <span>Confidential - Page 1</span>
    </div>
</div>
</body>
</html>