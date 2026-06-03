<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>SOC Intelligence Report | Armious Protect</title>
  <!-- Premium fonts: Syne for headings, DM Mono for data -->
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    :root {
      --crit: #f25c5c;
      --high: #f5923a;
      --med: #f5c842;
      --low: #4ecb8d;
      --accent: #4fa3e3;
      --text: #d8e8f5;
      --text2: #8cabc8;
      --border: rgba(255,255,255,0.07);
      --border2: rgba(255,255,255,0.12);
      --surface: rgba(255,255,255,0.03);
      --surface2: rgba(255,255,255,0.055);
      --bg-deep: #060a10;
    }

    body {
      background: var(--bg-deep);
      font-family: 'Syne', sans-serif;
      color: var(--text);
      min-height: 100vh;
      padding: 32px 24px 60px;
    }

    /* main container – exactly like the reference design */
    .soc-wrap {
      max-width: 1200px;
      margin: 0 auto;
      background: #080d14;
      border-radius: 24px;
      padding: 36px 40px 48px;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.06);
      box-shadow: 0 30px 60px rgba(0,0,0,0.6);
    }

    /* subtle background glow + dots */
    .soc-wrap::before {
      content: '';
      position: absolute;
      top: -140px; right: -100px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(79,163,227,0.07) 0%, transparent 70%);
      pointer-events: none;
    }

    .soc-wrap::after {
      content: '';
      position: absolute;
      bottom: -120px; left: -80px;
      width: 360px; height: 360px;
      background: radial-gradient(circle, rgba(78,203,141,0.04) 0%, transparent 70%);
      pointer-events: none;
    }

    .dot-grid {
      position: absolute;
      inset: 0;
      background-image: radial-gradient(rgba(255,255,255,0.025) 1px, transparent 1px);
      background-size: 24px 24px;
      pointer-events: none;
      border-radius: 24px;
    }

    .scan-line {
      position: absolute;
      left: 0; right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(79,163,227,0.12), transparent);
      animation: scan 8s linear infinite;
      pointer-events: none;
      z-index: 1;
    }

    @keyframes scan {
      0% { top: 0; }
      100% { top: 100%; }
    }

    /* ----- header (clean, strong) ----- */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 32px;
      padding-bottom: 28px;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      position: relative;
    }

    .brand-name {
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 3.5px;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 8px;
    }

    .report-title {
      font-size: 1.8rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.03em;
      line-height: 1.15;
    }

    .report-sub {
      font-size: 0.72rem;
      color: var(--text2);
      margin-top: 8px;
    }

    .meta-right {
      text-align: right;
      font-size: 0.65rem;
      font-family: 'DM Mono', monospace;
      color: var(--text2);
      line-height: 2;
    }

    .meta-right strong { color: var(--text); font-weight: 500; }

    .tlp-badge {
      display: inline-block;
      margin-top: 8px;
      background: rgba(79,163,227,0.1);
      border: 1px solid rgba(79,163,227,0.25);
      color: var(--accent);
      font-size: 0.58rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      padding: 3px 10px;
      border-radius: 4px;
    }

    /* ----- score row (index + risk distribution) ----- */
    .score-row {
      display: grid;
      grid-template-columns: 1fr 1.2fr;
      gap: 22px;
      margin-bottom: 28px;
    }

    .score-block, .risk-block {
      background: var(--surface);
      border: 1px solid var(--border2);
      border-radius: 20px;
      padding: 26px 30px;
    }

    .score-eyebrow {
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: 12px;
    }

    .score-num {
      font-size: 5.5rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
      letter-spacing: -0.04em;
    }

    .score-badge {
      display: inline-block;
      background: rgba(79,163,227,0.12);
      border: 1px solid rgba(79,163,227,0.25);
      color: var(--accent);
      font-size: 0.7rem;
      font-weight: 600;
      padding: 4px 14px;
      border-radius: 6px;
      margin-top: 12px;
    }

    .score-desc {
      font-size: 0.76rem;
      color: var(--text2);
      margin-top: 16px;
      line-height: 1.65;
    }

    .block-label {
      font-size: 0.58rem;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text2);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .block-label::before {
      content: '';
      display: inline-block;
      width: 3px; height: 12px;
      background: var(--accent);
      border-radius: 2px;
    }

    .sev-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }

    .sev-name {
      font-size: 0.72rem;
      font-weight: 600;
      width: 65px;
    }

    .sev-bar-bg {
      flex: 1;
      height: 5px;
      background: rgba(255,255,255,0.07);
      border-radius: 10px;
      overflow: hidden;
    }

    .sev-bar-fill {
      height: 100%;
      border-radius: 10px;
    }

    .sev-cnt {
      width: 32px;
      text-align: right;
      font-size: 0.7rem;
      font-weight: 700;
      font-family: 'DM Mono', monospace;
    }

    /* ----- KPI strip (5 columns) ----- */
    .kpi-row {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 14px;
      margin-bottom: 28px;
    }

    .kpi-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 18px 12px;
      text-align: center;
    }

    .kpi-val {
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      line-height: 1.1;
      letter-spacing: -0.02em;
    }

    .kpi-lbl {
      font-size: 0.56rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1.6px;
      color: var(--text2);
      margin-top: 8px;
    }

    /* ----- two column panels (chart + findings) ----- */
    .two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 28px;
    }

    .panel {
      background: var(--surface);
      border: 1px solid var(--border2);
      border-radius: 20px;
      padding: 24px 26px;
    }

    .section-header {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .section-title {
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 2.5px;
      text-transform: uppercase;
      color: var(--text2);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .section-title::before {
      content: '';
      display: inline-block;
      width: 3px; height: 12px;
      background: var(--accent);
      border-radius: 2px;
    }

    .section-hint {
      font-size: 0.6rem;
      color: rgba(140,171,200,0.45);
      font-family: 'DM Mono', monospace;
    }

    /* findings rows */
    .finding-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      gap: 12px;
    }

    .finding-row:last-child { border-bottom: none; }

    .finding-name {
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .finding-meta {
      font-size: 0.6rem;
      font-family: 'DM Mono', monospace;
      color: var(--text2);
    }

    .pill {
      font-size: 0.56rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 4px;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .pill-critical { background: rgba(242,92,92,0.15); color: #f87171; border: 1px solid rgba(242,92,92,0.3); }
    .pill-high     { background: rgba(245,146,58,0.15); color: #fb923c; border: 1px solid rgba(245,146,58,0.3); }
    .pill-medium   { background: rgba(245,200,66,0.12); color: #fbbf24; border: 1px solid rgba(245,200,66,0.2); }
    .pill-low      { background: rgba(78,203,141,0.12); color: #34d399; border: 1px solid rgba(78,203,141,0.2); }

    /* vuln telemetry strip */
    .vuln-strip {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .vuln-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 16px 14px;
      text-align: center;
    }

    .vuln-val {
      font-size: 1.8rem;
      font-weight: 800;
      line-height: 1.1;
    }

    .vuln-lbl {
      font-size: 0.56rem;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--text2);
      margin-top: 6px;
      font-weight: 600;
    }

    /* incident timeline */
    .timeline-item {
      padding: 0 0 20px 18px;
      border-left: 1px solid rgba(79,163,227,0.2);
      position: relative;
      margin-top: 4px;
    }

    .timeline-item::before {
      content: '';
      position: absolute;
      left: -4px; top: 3px;
      width: 7px; height: 7px;
      background: var(--accent);
      border-radius: 50%;
      box-shadow: 0 0 8px rgba(79,163,227,0.55);
    }

    .timeline-item:last-child { padding-bottom: 0; }

    .tl-title {
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 6px;
    }

    .tl-meta {
      font-size: 0.62rem;
      font-family: 'DM Mono', monospace;
      color: var(--text2);
    }

    /* operational metrics */
    .metric-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }

    .metric-name {
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text2);
      width: 110px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .metric-bar-bg {
      flex: 1;
      height: 4px;
      background: rgba(255,255,255,0.07);
      border-radius: 10px;
      overflow: hidden;
    }

    .metric-bar-fill {
      height: 100%;
      background: var(--accent);
      border-radius: 10px;
    }

    .metric-val {
      font-size: 0.7rem;
      font-weight: 700;
      font-family: 'DM Mono', monospace;
      color: var(--accent);
      width: 42px;
      text-align: right;
    }

    /* recommendations grid */
    .rec-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
      margin-top: 16px;
    }

    .rec-card {
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 18px;
      padding: 20px;
    }

    .rec-icon {
      font-size: 0.6rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .rec-title {
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .rec-body {
      font-size: 0.7rem;
      color: var(--text2);
      line-height: 1.6;
    }

    /* footer */
    .footer-line {
      display: flex;
      justify-content: space-between;
      font-size: 0.6rem;
      color: rgba(140,171,200,0.4);
      font-family: 'DM Mono', monospace;
      margin-top: 36px;
      padding-top: 24px;
      border-top: 1px solid rgba(255,255,255,0.05);
    }

    /* responsive */
    @media (max-width: 860px) {
      .soc-wrap { padding: 24px 20px 36px; }
      .score-row { grid-template-columns: 1fr; }
      .kpi-row { grid-template-columns: repeat(3, 1fr); }
      .two-col { grid-template-columns: 1fr; }
      .vuln-strip { grid-template-columns: repeat(2, 1fr); }
      .rec-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 540px) {
      .kpi-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media print {
      body { background: #080d14; padding: 0; }
      .soc-wrap { box-shadow: none; border-radius: 0; max-width: 100%; margin: 0; }
      .scan-line { display: none; }
      * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
  </style>
</head>
<body>
<div class="soc-wrap">
  <div class="dot-grid"></div>
  <div class="scan-line"></div>

  <!-- header -->
  <div class="header">
    <div>
      <div class="brand-name">Armious Protect</div>
      <div class="report-title">SOC Intelligence<br>Report</div>
      <div class="report-sub">Threat intelligence &amp; posture summary</div>
    </div>
    <div class="meta-right">
      <div><strong>Report ID</strong> &nbsp;SOC-INT-20250603</div>
      <div><strong>Period</strong> &nbsp;Last 30 days</div>
      <div><strong>Generated</strong> &nbsp;2025-06-03 14:22 UTC</div>
      <div class="tlp-badge">TLP:CLEAR · AUTHORIZED USE ONLY</div>
    </div>
  </div>

  <!-- score + risk distribution -->
  <div class="score-row">
    <div class="score-block">
      <div class="score-eyebrow">Security posture index</div>
      <div class="score-num">72</div>
      <div class="score-badge">Moderate</div>
      <div class="score-desc">18 active projects &nbsp;·&nbsp; 67 agents reporting &nbsp;·&nbsp; 11 incidents this period.</div>
    </div>
    <div class="risk-block">
      <div class="block-label">Risk distribution</div>
      <div class="sev-row">
        <span class="sev-name" style="color:var(--crit)">Critical</span>
        <div class="sev-bar-bg"><div class="sev-bar-fill" style="width:18%;background:var(--crit)"></div></div>
        <span class="sev-cnt" style="color:var(--crit)">12</span>
      </div>
      <div class="sev-row">
        <span class="sev-name" style="color:var(--high)">High</span>
        <div class="sev-bar-bg"><div class="sev-bar-fill" style="width:42%;background:var(--high)"></div></div>
        <span class="sev-cnt" style="color:var(--high)">28</span>
      </div>
      <div class="sev-row">
        <span class="sev-name" style="color:var(--med)">Medium</span>
        <div class="sev-bar-bg"><div class="sev-bar-fill" style="width:64%;background:var(--med)"></div></div>
        <span class="sev-cnt" style="color:var(--med)">43</span>
      </div>
      <div class="sev-row">
        <span class="sev-name" style="color:var(--low)">Low</span>
        <div class="sev-bar-bg"><div class="sev-bar-fill" style="width:30%;background:var(--low)"></div></div>
        <span class="sev-cnt" style="color:var(--low)">20</span>
      </div>
    </div>
  </div>

  <!-- KPI row -->
  <div class="kpi-row">
    <div class="kpi-card"><div class="kpi-val">18</div><div class="kpi-lbl">Projects</div></div>
    <div class="kpi-card"><div class="kpi-val">542</div><div class="kpi-lbl">Signals</div></div>
    <div class="kpi-card"><div class="kpi-val">11</div><div class="kpi-lbl">Incidents</div></div>
    <div class="kpi-card"><div class="kpi-val">47</div><div class="kpi-lbl">Vulnerabilities</div></div>
    <div class="kpi-card"><div class="kpi-val">67</div><div class="kpi-lbl">Sensors</div></div>
  </div>

  <!-- chart + findings (two columns) -->
  <div class="two-col">
    <div class="panel">
      <div class="section-header">
        <span class="section-title">Weekly signal trend</span>
        <span class="section-hint">6-week window</span>
      </div>
      <div style="position:relative;width:100%;height:190px;">
        <canvas id="trendChart" role="img" aria-label="Line chart showing weekly signals and incidents">Security signals peaked at week -3 and declined, incidents remained stable.</canvas>
      </div>
      <div class="section-hint" style="margin-top: 10px; display: flex; gap: 20px;">
        <span><span style="display:inline-block;width:12px;height:2px;background:#4fa3e3;vertical-align:middle;margin-right:4px;"></span> Signals</span>
        <span><span style="display:inline-block;width:12px;height:2px;background:#f5923a;vertical-align:middle;margin-right:4px;border-top:1px dashed #f5923a;"></span> Incidents</span>
      </div>
    </div>
    <div class="panel">
      <div class="section-header">
        <span class="section-title">Active findings</span>
        <span class="section-hint">Top 5</span>
      </div>
      <div>
        <div class="finding-row">
          <div><div class="finding-name">CVE-2025-22134: Apache Log4j2 JNDI</div><div class="finding-meta">app.armious.com/api · CVE-2025-22134</div></div>
          <span class="pill pill-critical">Critical</span>
        </div>
        <div class="finding-row">
          <div><div class="finding-name">WordPress Plugin IDOR</div><div class="finding-meta">client-portal.armious · CVE-2024-48793</div></div>
          <span class="pill pill-high">High</span>
        </div>
        <div class="finding-row">
          <div><div class="finding-name">Redis Unauthenticated Access</div><div class="finding-meta">cache.internal · —</div></div>
          <span class="pill pill-high">High</span>
        </div>
        <div class="finding-row">
          <div><div class="finding-name">SMB Signing Disabled</div><div class="finding-meta">files.armious · CVE-2023-36934</div></div>
          <span class="pill pill-medium">Medium</span>
        </div>
        <div class="finding-row">
          <div><div class="finding-name">nginx version disclosure</div><div class="finding-meta">edge.armious.com · INFO</div></div>
          <span class="pill pill-low">Low</span>
        </div>
      </div>
    </div>
  </div>

  <!-- vulnerability telemetry strip -->
  <div class="vuln-strip">
    <div class="vuln-card"><div class="vuln-val" style="color:var(--crit)">3</div><div class="vuln-lbl">Critical vulns</div></div>
    <div class="vuln-card"><div class="vuln-val" style="color:var(--high)">8</div><div class="vuln-lbl">High vulns</div></div>
    <div class="vuln-card"><div class="vuln-val" style="color:#fff">47</div><div class="vuln-lbl">Open vulns</div></div>
    <div class="vuln-card"><div class="vuln-val" style="color:var(--accent)">54</div><div class="vuln-lbl">Assets tracked</div></div>
  </div>

  <!-- incident timeline + ops metrics (two columns) -->
  <div class="two-col">
    <div class="panel">
      <div class="section-header">
        <span class="section-title">Incident timeline</span>
      </div>
      <div class="timeline-item">
        <div class="tl-title">Critical memory scan — malware behavior</div>
        <div class="tl-meta">endpoint-lax &nbsp;·&nbsp; CRITICAL &nbsp;·&nbsp; <span style="color:var(--low)">resolved</span></div>
      </div>
      <div class="timeline-item">
        <div class="tl-title">Brute-force on admin portal</div>
        <div class="tl-meta">auth.armious.com &nbsp;·&nbsp; HIGH &nbsp;·&nbsp; <span style="color:var(--high)">investigating</span></div>
      </div>
      <div class="timeline-item">
        <div class="tl-title">Suspicious outbound traffic to TOR exit node</div>
        <div class="tl-meta">workload-01 &nbsp;·&nbsp; MEDIUM &nbsp;·&nbsp; <span style="color:var(--med)">contained</span></div>
      </div>
    </div>
    <div class="panel">
      <div class="section-header">
        <span class="section-title">SOC operational metrics</span>
      </div>
      <div class="metric-row">
        <span class="metric-name">MTTD</span>
        <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:72%"></div></div>
        <span class="metric-val">4.2m</span>
      </div>
      <div class="metric-row">
        <span class="metric-name">MTTR</span>
        <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:58%"></div></div>
        <span class="metric-val">38m</span>
      </div>
      <div class="metric-row">
        <span class="metric-name">SOAR coverage</span>
        <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:76%"></div></div>
        <span class="metric-val">76%</span>
      </div>
      <div class="metric-row">
        <span class="metric-name">Agent coverage</span>
        <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:94%"></div></div>
        <span class="metric-val">94%</span>
      </div>
      <div class="metric-row">
        <span class="metric-name">False positive rate</span>
        <div class="metric-bar-bg"><div class="metric-bar-fill" style="width:12%;background:var(--low)"></div></div>
        <span class="metric-val" style="color:var(--low)">12%</span>
      </div>
    </div>
  </div>

  <!-- Recommendations -->
  <div class="section-header" style="margin-top: 4px;">
    <span class="section-title">Recommended actions</span>
    <span class="section-hint">Prioritized by SOC</span>
  </div>
  <div class="rec-grid">
    <div class="rec-card">
      <div class="rec-icon" style="color:var(--crit)">· P1 Critical</div>
      <div class="rec-title">Remediate critical findings</div>
      <div class="rec-body">Patch CVE-2025-22134 and enforce WAF rules on all exposed admin interfaces immediately.</div>
    </div>
    <div class="rec-card">
      <div class="rec-icon" style="color:var(--high)">· P2 High</div>
      <div class="rec-title">Harden identity layer</div>
      <div class="rec-body">Enable MFA for all admin users. Rotate exposed API keys identified in this period.</div>
    </div>
    <div class="rec-card">
      <div class="rec-icon" style="color:var(--accent)">· P3 Ongoing</div>
      <div class="rec-title">Continuous validation</div>
      <div class="rec-body">Run automated vulnerability scans weekly and track delta reports against baseline.</div>
    </div>
    <div class="rec-card">
      <div class="rec-icon" style="color:var(--low)">· P4 Health</div>
      <div class="rec-title">Sensor health check</div>
      <div class="rec-body">Reconcile offline sensors and validate log forwarding pipelines across all agents.</div>
    </div>
  </div>

  <div class="footer-line">
    <span>Armious Protect SOC — Intelligence summary for leadership review.</span>
    <span>Page 1 of 1 &nbsp;·&nbsp; CONFIDENTIAL</span>
  </div>
</div>

<script>
  (function() {
    const canvas = document.getElementById('trendChart');
    if (!canvas) return;
    new Chart(canvas, {
      type: 'line',
      data: {
        labels: ['Wk -5', 'Wk -4', 'Wk -3', 'Wk -2', 'Wk -1', 'Now'],
        datasets: [
          {
            label: 'Signals',
            data: [65, 98, 124, 113, 82, 60],
            borderColor: '#4fa3e3',
            backgroundColor: 'rgba(79,163,227,0.05)',
            fill: true,
            tension: 0.35,
            borderWidth: 1.8,
            pointRadius: 3,
            pointBackgroundColor: '#4fa3e3',
            pointBorderColor: '#0a1018',
            pointBorderWidth: 1,
            yAxisID: 'y'
          },
          {
            label: 'Incidents',
            data: [1, 2, 3, 2, 2, 1],
            borderColor: '#f5923a',
            backgroundColor: 'rgba(245,146,58,0.03)',
            fill: true,
            tension: 0.35,
            borderWidth: 1.8,
            borderDash: [5, 3],
            pointRadius: 3,
            pointBackgroundColor: '#f5923a',
            pointBorderColor: '#0a1018',
            pointBorderWidth: 1,
            yAxisID: 'y2'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(8,13,20,0.96)',
            titleColor: '#d8e8f5',
            bodyColor: '#8cabc8',
            borderColor: 'rgba(79,163,227,0.3)',
            borderWidth: 1,
            padding: 10,
            titleFont: { family: 'Syne', size: 11, weight: '600' },
            bodyFont: { family: 'DM Mono', size: 10 }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#4a6480', font: { family: 'DM Mono', size: 10, weight: '500' } },
            border: { display: false }
          },
          y: {
            position: 'left',
            grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
            ticks: { color: '#4a6480', font: { family: 'DM Mono', size: 9 } },
            border: { display: false }
          },
          y2: {
            position: 'right',
            grid: { display: false },
            ticks: { color: '#4a6480', font: { family: 'DM Mono', size: 9 } },
            border: { display: false }
          }
        }
      }
    });
  })();
</script>
</body>
</html>