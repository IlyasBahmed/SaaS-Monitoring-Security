<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>SOC Intelligence Report</title>

<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    @page { margin: 0; size: A4; }

    body {
        background: #0a0f1e;
        color: #e8edf5;
        font-family: 'IBM Plex Mono', monospace;
    }

    .page {
        width: 210mm;
        height: 297mm;
        page-break-after: always;
        position: relative;
        padding: 40px;
        background:
            radial-gradient(circle at 90% 5%, rgba(0,255,200,.08), transparent 32%),
            radial-gradient(circle at 10% 92%, rgba(88,120,255,.08), transparent 34%),
            #0a0f1e;
        overflow: hidden;
        border-radius: 8px;
    }

    .page::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(0,255,200,.02) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,255,200,.02) 1px, transparent 1px);
        background-size: 34px 34px;
        pointer-events: none;
    }

    .content {
        position: relative;
        z-index: 2;
        height: 100%;
    }

    .header {
        display: flex;
        justify-content: space-between;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        align-items: center;
    }

    .brand {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .brand-mark {
        width: 38px;
        height: 38px;
        border: 1px solid #00ffc8;
        clip-path: polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
        background: rgba(0,255,200,.08);
        box-shadow: 0 0 12px rgba(0,255,200,0.25);
    }

    .brand-title {
        font-family: 'Syne', sans-serif;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: .28em;
        color: #00ffc8;
    }

    .brand-sub {
        margin-top: 4px;
        font-size: 10px;
        color: #94a3b8;
        letter-spacing: .18em;
    }

    .meta {
        text-align: right;
        color: #64748b;
        font-size: 10px;
        line-height: 1.9;
    }

    .meta b { color: #94a3b8; }

    .classification {
        display: inline-block;
        margin-top: 4px;
        padding: 3px 8px;
        border: 1px solid rgba(251,146,60,.35);
        color: #fb923c;
        background: rgba(251,146,60,.12);
        border-radius: 4px;
        letter-spacing: .16em;
        font-size: 8px;
    }

    .hero {
        padding-top: 36px;
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 32px;
    }

    .eyebrow {
        color: #00ffc8;
        letter-spacing: .35em;
        text-transform: uppercase;
        font-size: 10px;
        margin-bottom: 14px;
    }

    .title {
        font-family: 'Syne', sans-serif;
        font-size: 52px;
        line-height: 1.05;
        letter-spacing: -.04em;
        color: #f8fafc;
    }

    .title span { color: #00ffc8; }

    .summary {
        margin-top: 20px;
        max-width: 560px;
        padding-left: 16px;
        border-left: 2px solid rgba(0,255,200,.55);
        color: #8b98ac;
        line-height: 1.8;
        font-size: 14px;
    }

    .score-card {
        padding: 22px;
        display: flex;
        gap: 18px;
        align-items: center;
        background: rgba(255,255,255,.02);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 14px;
        box-shadow: 0 4px 15px rgba(0,255,200,0.08);
    }

    .score-title {
        color: #94a3b8;
        font-size: 10px;
        letter-spacing: .25em;
        text-transform: uppercase;
    }

    .score-text {
        margin-top: 10px;
        color: #e2e8f0;
        font-size: 13px;
        line-height: 1.65;
    }

    .kpi-row {
        margin-top: 30px;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        border-top: 1px solid rgba(255,255,255,.06);
        border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .kpi {
        padding: 22px 16px;
        border-right: 1px solid rgba(255,255,255,.06);
        border-radius: 0;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: default;
    }

    .kpi:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,255,200,0.12);
    }

    .kpi::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent);
        border-radius: 4px;
    }

    .kpi-label {
        font-size: 9px;
        color: #64748b;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .kpi-value {
        margin-top: 10px;
        font-family: 'Syne', sans-serif;
        font-size: 36px;
        font-weight: 800;
        color: var(--accent);
    }

    .grid-2 {
        margin-top: 30px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
    }

    .panel {
        padding: 24px;
        min-height: 250px;
        background: rgba(255,255,255,.015);
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 14px;
        backdrop-filter: blur(4px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(0,255,200,0.15);
    }

    .panel-title {
        font-size: 10px;
        letter-spacing: .28em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .panel-title::after {
        content: "";
        height: 1px;
        flex: 1;
        background: rgba(255,255,255,.07);
    }

    .chart-wrap {
        height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .severity-bars {
        display: grid;
        gap: 14px;
    }

    .bar-row {
        display: grid;
        grid-template-columns: 70px 1fr 28px;
        align-items: center;
        gap: 10px;
        font-size: 10px;
        color: #64748b;
    }

    .bar-track {
        height: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,.06);
        overflow: hidden;
    }

    .bar-fill {
        height: 100%;
        width: var(--w);
        background: var(--c);
        border-radius: 999px;
        transition: width 0.6s ease-in-out;
    }

    .section-title {
        font-family: 'Syne', sans-serif;
        font-size: 36px;
        color: #f8fafc;
        letter-spacing: -.03em;
        margin: 32px 0 24px;
    }

    .section-title span { color: #00ffc8; }

    .vuln-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .finding {
        padding: 16px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 14px;
        background: rgba(255,255,255,.018);
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .finding:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,255,200,0.12);
    }

    .finding-name {
        font-size: 13px;
        color: #e2e8f0;
        line-height: 1.35;
    }

    .finding-meta {
        margin-top: 6px;
        color: #64748b;
        font-size: 10px;
        line-height: 1.4;
    }

    .pill {
        height: max-content;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        border: 1px solid;
    }

    .critical { color: #f43f5e; background: rgba(244,63,94,.15); border-color: rgba(244,63,94,.35); }
    .high { color: #fb923c; background: rgba(249,115,22,.15); border-color: rgba(249,115,22,.3); }
    .medium { color: #fbbf24; background: rgba(245,158,11,.12); border-color: rgba(245,158,11,.3); }
    .low { color: #34d399; background: rgba(16,185,129,.12); border-color: rgba(16,185,129,.3); }

    .rec-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .rec {
        padding: 18px;
        border-top: 2px solid var(--accent);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .rec:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,255,200,0.12);
    }

    .rec-title {
        color: var(--accent);
        font-size: 10px;
        letter-spacing: .18em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .rec-body {
        color: #64748b;
        font-size: 12px;
        line-height: 1.7;
    }

    .footer {
        position: absolute;
        left: 40px;
        right: 40px;
        bottom: 24px;
        display: flex;
        justify-content: space-between;
        color: #374151;
        font-size: 9px;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    /* Print friendly */
    @media print {
        body { background: #fff; color: #000; }
        .page { background: #fff; border-radius: 0; }
        .brand-title, .score-value { color: #000; }
        .pill { color: #000 !important; background: #eee !important; border-color: #ccc !important; }
    }
</style>
</head>
<body>
<!-- Your existing Blade/Laravel PHP content goes here -->
</body>
</html>