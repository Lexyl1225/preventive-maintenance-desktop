<?php
require_once __DIR__ . '/db-config.php';

// ── Server-side: pre-fetch aggregated data for initial render ──────────────
$groupData   = [];
$indivData   = [];

$defined_groups = [
    ['key' => 'johny austria|joseph cristal jr',   'label' => 'Austria & Cristal',  'members' => ['Johny Austria', 'Joseph Cristal Jr']],
    ['key' => 'jerico simon|petemar solano',    'label' => 'Simon & Solano',      'members' => ['Jerico Simon', 'Petemar Solano']],
    ['key' => 'chris neri|ronil jardio',        'label' => 'Jardio & Neri',       'members' => ['Ronil Jardio', 'Chris Neri']],
];
$defined_individuals = [
    'Joseph Cristal Jr', 'Jerico Simon', 'Johny Austria',
    'Petemar Solano', 'Ronil Jardio', 'Chris Neri',
];

try {
    $stmt = $pdo->query('SELECT performedBy, COUNT(*) as cnt FROM maintenance_records GROUP BY performedBy');
    $rawRows = $stmt->fetchAll();

    // aggregate into groups
    $groupCounts = [];
    foreach ($defined_groups as $g) {
        $groupCounts[$g['key']] = 0;
    }
    $indivCounts = [];
    foreach ($defined_individuals as $n) {
        $indivCounts[$n] = 0;
    }

    // Alias map: old name variants (lowercase) → canonical lowercase name
    $name_aliases = [
        'joseph cristal'    => 'joseph cristal jr',
        'joseph christal'   => 'joseph cristal jr',
        'joseph christal jr'=> 'joseph cristal jr',
    ];
    // Individual alias map: canonical name → all recognised spelling variants
    $indiv_aliases = [
        'Joseph Cristal Jr' => ['Joseph Cristal Jr', 'Joseph Cristal', 'Joseph Christal', 'Joseph Christal Jr'],
    ];

    foreach ($rawRows as $row) {
        $raw = trim($row['performedBy'] ?? '');
        $cnt = (int)$row['cnt'];

        // Normalise group key — apply aliases before sorting parts
        if (strpos($raw, ' / ') !== false) {
            $parts = array_map('trim', explode(' / ', $raw));
            $parts_lc = array_map(function($p) use ($name_aliases) {
                $lc = strtolower($p);
                return $name_aliases[$lc] ?? $lc;
            }, $parts);
            sort($parts_lc);
            $nk = implode('|', $parts_lc);
            if (isset($groupCounts[$nk])) {
                $groupCounts[$nk] += $cnt;
            }
        }

        // Individual match — use alias variants to avoid missing old spellings
        foreach ($defined_individuals as $name) {
            $patterns = $indiv_aliases[$name] ?? [$name];
            $matched  = false;
            foreach ($patterns as $pattern) {
                if (!$matched && stripos($raw, $pattern) !== false) {
                    $indivCounts[$name] += $cnt;
                    $matched = true; // count once even if multiple aliases match
                }
            }
        }
    }

    foreach ($defined_groups as $g) {
        $groupData[] = ['label' => $g['label'], 'value' => $groupCounts[$g['key']]];
    }
    foreach ($defined_individuals as $n) {
        $indivData[] = ['label' => $n, 'value' => $indivCounts[$n]];
    }

} catch (Exception $e) {
    // If DB unavailable, chart will use API fallback
    $groupData   = [];
    $indivData   = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <title>Top Performer Charts — EPM</title>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <!-- Chart.js Data Labels plugin (optional, graceful fallback) -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
        /* ── Reset / Base ───────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:      #0a0e17;
            --card:    rgba(12, 22, 45, 0.88);
            --accent:  #00e5ff;
            --accent2: #2979ff;
            --muted:   #5a7a99;
            --text:    #d0dce8;
            --danger:  #ff2d95;
            --success: #00e676;
            --warn:    #ffca28;
            --border:  rgba(0,229,255,0.14);
            --shadow:  0 4px 24px rgba(0,0,0,0.55);
            --radius:  12px;
        }
        html, body { min-height: 100vh; background: var(--bg); color: var(--text); font-family: 'Segoe UI', system-ui, -apple-system, Arial, sans-serif; }
        body { background-image: url('images/tw_bg.png'); background-repeat: no-repeat; background-position: center; background-size: cover; background-attachment: fixed; }

        /* ── Scrollbar ──────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: #111; } ::-webkit-scrollbar-thumb { background: #1e3a5f; border-radius: 3px; }

        /* ── Header ─────────────────────────────────────────────── */
        .page-header {
            background: linear-gradient(90deg, rgba(8,59,102,0.72) 0%, rgba(0,229,255,0.18) 100%);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
        }
        .page-header h1 { font-size: 20px; letter-spacing: .5px; color: var(--accent); text-shadow: 0 0 12px rgba(0,229,255,0.45); }
        .page-header p { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .back-btn {
            background: rgba(0,229,255,0.08); border: 1px solid var(--border);
            color: var(--accent); padding: 7px 14px; border-radius: 6px;
            cursor: pointer; font-size: 13px; transition: background .2s;
            text-decoration: none; white-space: nowrap;
        }
        .back-btn:hover { background: rgba(0,229,255,0.18); }

        /* ── Main Layout ─────────────────────────────────────────── */
        main { max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px; }

        /* ── Section title ──────────────────────────────────────── */
        .section-title {
            font-size: 11px; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase;
            color: var(--accent); margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* ── Card ───────────────────────────────────────────────── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(4px);
        }
        .card-title { font-size: 15px; font-weight: 600; color: var(--accent); margin-bottom: 4px; }
        .card-sub   { font-size: 12px; color: var(--muted); margin-bottom: 16px; }

        /* ── Filter Bar ─────────────────────────────────────────── */
        .filter-bar {
            display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;
            margin-bottom: 24px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 4px; }
        .filter-group label { font-size: 11px; color: var(--muted); letter-spacing: .5px; }
        .filter-group input[type=date],
        .filter-group select {
            background: rgba(5,12,30,0.65);
            border: 1px solid rgba(0,229,255,0.2);
            border-radius: 6px;
            color: var(--text);
            padding: 7px 10px;
            font-size: 13px;
            outline: none;
            transition: border-color .2s;
        }
        .filter-group input[type=date]:focus,
        .filter-group select:focus { border-color: var(--accent); }
        .filter-group input[type=date]::-webkit-calendar-picker-indicator { filter: invert(.7) sepia(1) saturate(2) hue-rotate(160deg); cursor: pointer; }
        .btn { background: var(--accent2); color: #fff; border: 0; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: opacity .2s, transform .15s; }
        .btn:hover { opacity: .88; transform: translateY(-1px); }
        .btn.secondary { background: rgba(0,229,255,0.1); border: 1px solid var(--border); color: var(--accent); }
        .btn.secondary:hover { background: rgba(0,229,255,0.2); }
        .btn.danger { background: rgba(255,45,149,0.15); border: 1px solid rgba(255,45,149,0.3); color: var(--danger); }
        .btn.danger:hover { background: rgba(255,45,149,0.25); }

        /* ── Switcher ───────────────────────────────────────────── */
        .switcher { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 14px; }
        .switcher button { background: rgba(0,229,255,0.06); border: 1px solid var(--border); color: var(--muted); padding: 6px 14px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 500; transition: all .2s; }
        .switcher button.active { background: rgba(0,229,255,0.18); border-color: var(--accent); color: var(--accent); }

        /* ── Charts Grid ─────────────────────────────────────────── */
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }

        /* ── Canvas wrapper ─────────────────────────────────────── */
        .chart-wrap { position: relative; width: 100%; max-height: 380px; display: flex; justify-content: center; }
        .chart-wrap canvas { max-height: 360px; }
        /* Print images hidden on screen — only shown inside @media print */
        .chart-print-img { display: none; }

        /* ── Top Performer Box ──────────────────────────────────── */
        .top-performer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px; }
        .top-box {
            background: linear-gradient(135deg, rgba(0,229,255,0.08) 0%, rgba(41,121,255,0.1) 100%);
            border: 1px solid rgba(0,229,255,0.22);
            border-radius: 10px; padding: 16px 14px;
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            text-align: center;
        }
        .top-box .crown { font-size: 26px; margin-bottom: 2px; }
        .top-box .name  { font-size: 14px; font-weight: 700; color: var(--accent); }
        .top-box .sub   { font-size: 11px; color: var(--muted); }
        .top-box .val   { font-size: 22px; font-weight: 800; color: var(--warn); line-height: 1; margin-top: 4px; }
        .top-box .rank-badge { font-size: 10px; background: rgba(255,202,40,.15); color: var(--warn); border: 1px solid rgba(255,202,40,.3); border-radius: 20px; padding: 2px 8px; margin-top: 4px; }

        /* ── Custom Name Input ──────────────────────────────────── */
        .custom-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 16px; }
        .custom-row input[type=text], .custom-row input[type=number] {
            background: rgba(5,12,30,0.65);
            border: 1px solid rgba(0,229,255,0.2);
            border-radius: 6px; color: var(--text); padding: 8px 10px; font-size: 13px; outline: none;
            transition: border-color .2s; min-width: 140px;
        }
        .custom-row input:focus { border-color: var(--accent); }
        #custom-name-error { font-size: 11px; color: var(--danger); display: none; width: 100%; }

        /* ── Summary Table ──────────────────────────────────────── */
        .summary-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .summary-table th { background: rgba(0,100,140,0.35); color: var(--accent); padding: 9px 10px; text-align: left; font-size: 11px; letter-spacing: .8px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .summary-table td { padding: 8px 10px; border-bottom: 1px solid rgba(0,229,255,0.05); color: var(--text); }
        .summary-table tr:hover td { background: rgba(0,229,255,0.04); }
        .rank-1 { color: var(--warn); font-weight: 700; }
        .rank-2 { color: #ccc; }
        .rank-3 { color: #cd7f32; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 600; }
        .badge-gold   { background: rgba(255,202,40,.14); color: var(--warn);    border: 1px solid rgba(255,202,40,.3); }
        .badge-silver { background: rgba(200,200,200,.1);  color: #ccc;           border: 1px solid rgba(200,200,200,.25); }
        .badge-bronze { background: rgba(205,127,50,.12);  color: #cd7f32;        border: 1px solid rgba(205,127,50,.25); }
        .badge-norm   { background: rgba(0,229,255,.07);  color: var(--muted);    border: 1px solid var(--border); }

        /* ── Progress Bar ───────────────────────────────────────── */
        .pbar-wrap { background: rgba(255,255,255,0.05); border-radius: 4px; height: 6px; min-width: 80px; overflow: hidden; }
        .pbar-fill  { height: 100%; border-radius: 4px; transition: width .6s ease; }

        /* ── Export buttons ─────────────────────────────────────── */
        .export-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px; }

        /* ── Loading / Error ─────────────────────────────────────── */
        .loading-msg { color: var(--muted); font-size: 13px; padding: 24px; text-align: center; }
        .error-msg  { color: var(--danger); background: rgba(255,45,149,0.08); border: 1px solid rgba(255,45,149,0.2); border-radius: 6px; padding: 10px 14px; font-size: 13px; }

        /* ── Responsive tweaks ──────────────────────────────────── */
        @media (max-width: 600px) {
            main { padding: 14px 10px 60px; }
            .page-header { padding: 12px 14px; }
            .page-header h1 { font-size: 16px; }
            .card { padding: 14px 12px; }
        }

        /* ── Scanline overlay (matches hangar theme) ────────────── */
        body::before { content:''; position:fixed; inset:0; pointer-events:none; background: repeating-linear-gradient(to bottom, transparent 0px, transparent 2px, rgba(0,0,0,0.04) 2px, rgba(0,0,0,0.04) 4px); z-index:0; }

        /* ════════════════════════════════════════════════════════
           PRINT STYLES — clean white output
        ═══════════════════════════════════════════════════════════ */
        @media print {
            @page { size: A4 portrait; margin: 12mm 14mm; }

            /* ── Kill dark theme backgrounds — targeted, NOT on * ── */
            html, body,
            .card, .page-header,
            .top-box, .top-performer-grid,
            .filter-bar, .switcher,
            .export-bar, .custom-row,
            main, .section-title,
            #diagnostic-panel {
                background: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
                backdrop-filter: none !important;
                -webkit-backdrop-filter: none !important;
            }
            /* Force white page */
            html, body { background: #fff !important; background-image: none !important; color: #111 !important; font-size: 12px; }
            /* Ensure color accuracy for coloured elements */
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            /* ── Scanline overlay: gone ── */
            body::before { display: none !important; }

            /* ── Hide interactive / chrome elements ── */
            .page-header .back-btn,
            .filter-bar,
            .filter-group,
            .switcher,
            .export-bar,
            .custom-row,
            .card-sub,
            #custom-name-error,
            #remove-custom-btn,
            #diagnostic-panel,
            #data-source-tag,
            #filter-info,
            [onclick*="diagnostic"],
            button { display: none !important; }

            /* ── Header — slim print banner ── */
            .page-header {
                position: static !important;
                background: #fff !important;
                border-bottom: 2px solid #0b78d1 !important;
                padding: 8px 0 6px !important;
                display: block !important;
            }
            .page-header h1 {
                color: #0b3b66 !important;
                font-size: 18px !important;
                text-shadow: none !important;
            }
            .page-header p { color: #555 !important; font-size: 11px !important; }

            /* ── Main ── */
            main { padding: 12px 0 !important; max-width: 100% !important; }

            /* ── Section titles ── */
            .section-title {
                color: #0b3b66 !important;
                font-size: 10px !important;
                letter-spacing: 1.5px;
                border-bottom: 1px solid #ccc !important;
                margin-bottom: 10px;
            }
            .section-title::after { background: #ccc !important; }

            /* ── Cards ── */
            .card {
                background: #fff !important;
                border: 1px solid #dde3ea !important;
                border-radius: 6px !important;
                padding: 14px !important;
                margin-bottom: 16px !important;
                page-break-inside: avoid;
            }
            .card-title { color: #0b3b66 !important; font-size: 13px !important; margin-bottom: 6px !important; }

            /* ── Top performer boxes ── */
            .top-performer-grid {
                grid-template-columns: repeat(4, 1fr) !important;
                gap: 8px !important;
                margin-bottom: 14px !important;
            }
            .top-box {
                background: #f4f7fb !important;
                border: 1px solid #c8d6e8 !important;
                border-radius: 6px !important;
                padding: 10px 8px !important;
            }
            .top-box .crown { font-size: 18px !important; }
            .top-box .name  { color: #0b3b66 !important; font-size: 12px !important; }
            .top-box .val   { color: #b8860b !important; font-size: 16px !important; }
            .top-box .sub,
            .top-box .rank-badge { color: #555 !important; font-size: 10px !important; }

            /* ── Chart images (canvas replaced by <img> via JS) ── */
            .chart-print-img {
                display: block !important;
                max-width: 100% !important;
                width: 100% !important;
                height: auto !important;
                max-height: 260px !important;
                margin: 0 auto 10px !important;
                object-fit: contain !important;
                border: 1px solid #e0e6ed;
                border-radius: 4px;
            }
            .chart-wrap canvas { display: none !important; }
            .chart-wrap {
                max-height: none !important;
                display: block !important;
                width: 100% !important;
                overflow: hidden !important;
            }
            .card { overflow: hidden !important; }

            /* ── Summary tables ── */
            .summary-table { font-size: 11px !important; }
            .summary-table th {
                background: #e8eef6 !important;
                color: #0b3b66 !important;
                border-bottom: 1px solid #c8d6e8 !important;
                padding: 6px 8px !important;
            }
            .summary-table td {
                color: #111 !important;
                border-bottom: 1px solid #eef1f4 !important;
                padding: 5px 8px !important;
            }
            .summary-table tr:nth-child(even) td { background: #f8fafc !important; }
            .rank-1 { color: #b8860b !important; }
            .rank-2 { color: #555 !important; }
            .rank-3 { color: #8b4513 !important; }

            /* ── Badges ── */
            .badge { border: 1px solid #ccc !important; padding: 1px 5px !important; font-size: 9px !important; }
            .badge-gold   { color: #b8860b !important; }
            .badge-silver { color: #555 !important; }
            .badge-bronze { color: #8b4513 !important; }
            .badge-norm   { color: #333 !important; }

            /* ── Progress bars ── */
            .pbar-wrap { background: #e8eef6 !important; }
            /* Re-apply inline fill colours stripped by the * rule above */
            .pbar-fill[style*="background"] { -webkit-print-color-adjust: exact; print-color-adjust: exact; opacity: 1 !important; }

            /* ── Print footer ── */
            #print-footer { display: block !important; }

            /* ── Avoid page breaks mid-card ── */
            .card, .top-performer-grid { page-break-inside: avoid; }
        }
    </style>

    <!-- Firebase + Auth -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database-compat.js"></script>
    <script src="firebase-config.js"></script>
    <script src="auth-guard.js"></script>
    <script src="db.js"></script>
</head>
<body>
    <!-- ═══════════════  HEADER  ═══════════════════════════════════ -->
    <header class="page-header">
        <div>
            <h1>⚡ Top Performer Charts</h1>
            <p>Electrical Preventive Maintenance — Performance Overview</p>
        </div>
        <a class="back-btn" href="index.php">← Back to App</a>
    </header>

    <!-- ═══════════════  MAIN  ═════════════════════════════════════ -->
    <main id="main-content">

        <!-- ── Filter Bar ─────────────────────────────────────── -->
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                <span class="card-title">Filters &amp; Options</span>
                <div id="data-source-tag" style="font-size:11px; color:var(--muted);"></div>
            </div>
            <div class="filter-bar">
                <div class="filter-group">
                    <label>Date From</label>
                    <input type="date" id="filter-from" />
                </div>
                <div class="filter-group">
                    <label>Date To</label>
                    <input type="date" id="filter-to" />
                </div>
                <div class="filter-group">
                    <label>Quick Range</label>
                    <select id="quick-range">
                        <option value="">All Time</option>
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="180">Last 6 months</option>
                        <option value="365">Last year</option>
                        <option value="thismonth">This Month</option>
                        <option value="lastmonth">Last Month</option>
                    </select>
                </div>
                <div class="filter-group" style="justify-content:flex-end;">
                    <label>&nbsp;</label>
                    <div style="display:flex; gap:8px;">
                        <button class="btn" onclick="applyFilters()">Apply</button>
                        <button class="btn secondary" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>
            <div id="filter-info" style="font-size:12px; color:var(--muted);"></div>

            <!-- Diagnostic Collapsible -->
            <div style="margin-top:14px;">
                <button class="btn secondary" style="font-size:12px;" onclick="
                    const p = document.getElementById('diagnostic-panel');
                    const isOpen = p.getAttribute('data-open') === '1';
                    p.setAttribute('data-open', isOpen ? '0' : '1');
                    p.style.display = isOpen ? 'none' : 'block';
                    this.textContent = isOpen ? '🔍 Show Raw Data Inspector' : '🔍 Hide Raw Data Inspector';
                    if (!isOpen) renderDiagnostic(filteredRecs.length ? filteredRecs : allRecords);
                ">🔍 Show Raw Data Inspector</button>
                <div id="diagnostic-panel" data-open="0" style="display:none; margin-top:12px; background:rgba(0,0,0,0.3); border:1px solid var(--border); border-radius:8px; padding:14px;"></div>
            </div>
        </div>

        <!-- ── Top Performer Highlight ────────────────────────── -->
        <div class="section-title">🏆 Top Performers</div>
        <div id="top-performer-boxes" class="top-performer-grid">
            <div class="loading-msg">Loading performance data…</div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             ROW 1 — Group Performance Chart
        ═══════════════════════════════════════════════════════════ -->
        <div class="section-title">Group Performance Overview</div>
        <div class="card">
            <div class="card-title">Group Performance</div>
            <div class="card-sub">Tasks completed per maintenance team pair (all name-order variants combined)</div>

            <div class="switcher" id="group-type-switcher">
                <button class="active" data-type="pie"   onclick="switchChartType('group','pie',this)">Pie</button>
                <button              data-type="doughnut" onclick="switchChartType('group','doughnut',this)">Donut</button>
                <button              data-type="bar"      onclick="switchChartType('group','bar',this)">Bar</button>
                <button              data-type="polarArea" onclick="switchChartType('group','polarArea',this)">Polar</button>
            </div>

            <div class="chart-wrap" id="group-chart-wrap">
                <canvas id="groupChart"></canvas>
            </div>

            <div class="export-bar">
                <button class="btn secondary" onclick="exportChartPNG('groupChart','group_performance')">⬇ Save PNG</button>
                <button class="btn secondary" onclick="printPage()">🖨 Print</button>
            </div>

            <br>
            <div id="group-summary-wrap"></div>
        </div>

        <!-- ══════════════════════════════════════════════════════
             ROW 2 — Individual Performance Chart
        ═══════════════════════════════════════════════════════════ -->
        <div class="section-title">Individual Performance Breakdown</div>
        <div class="card">
            <div class="card-title">Individual Performance</div>
            <div class="card-sub">Tasks completed per technician (includes records where the name appears, solo or in a pair)</div>

            <!-- Custom Name Input -->
            <div style="margin-bottom:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:8px;">Add a custom performer to the chart:</div>
                <div class="custom-row">
                    <div class="filter-group">
                        <label>Custom Name</label>
                        <input type="text" id="custom-name-input" placeholder="e.g. Juan dela Cruz" maxlength="80" />
                    </div>
                    <div class="filter-group">
                        <label>Override Value (optional)</label>
                        <input type="number" id="custom-value-input" placeholder="Leave blank = auto-count" min="0" max="9999" />
                    </div>
                    <div class="filter-group" style="justify-content:flex-end;">
                        <label>&nbsp;</label>
                        <div style="display:flex; gap:8px;">
                            <button class="btn" onclick="addCustomName()">+ Add</button>
                            <button class="btn danger" onclick="removeCustomName()" id="remove-custom-btn" style="display:none;">✕ Remove</button>
                        </div>
                    </div>
                </div>
                <div id="custom-name-error">⚠ Please enter a valid name (letters, spaces, hyphens and dots only).</div>
            </div>

            <div class="switcher" id="indiv-type-switcher">
                <button class="active" data-type="pie"    onclick="switchChartType('indiv','pie',this)">Pie</button>
                <button              data-type="doughnut" onclick="switchChartType('indiv','doughnut',this)">Donut</button>
                <button              data-type="bar"      onclick="switchChartType('indiv','bar',this)">Bar</button>
                <button              data-type="polarArea" onclick="switchChartType('indiv','polarArea',this)">Polar</button>
            </div>

            <div class="chart-wrap" id="indiv-chart-wrap">
                <canvas id="indivChart"></canvas>
            </div>

            <div class="export-bar">
                <button class="btn secondary" onclick="exportChartPNG('indivChart','individual_performance')">⬇ Save PNG</button>
                <button class="btn secondary" onclick="printPage()">🖨 Print</button>
            </div>

            <br>
            <div id="indiv-summary-wrap"></div>
        </div>

    </main>

    <!-- Print-only footer: hidden on screen, visible when printing -->
    <div id="print-footer" style="display:none;">
        <div style="border-top:1px solid #c8d6e8;margin-top:20px;padding-top:8px;font-size:10px;color:#555;display:flex;justify-content:space-between;">
            <span>Electrical Preventive Maintenance — Top Performer Charts</span>
            <span id="print-footer-date"></span>
        </div>
    </div>

    <!-- Hangar Theme (visual overlay only — no functional changes; screen only to avoid print bleed) -->
    <link rel="stylesheet" href="hangar-theme.css" media="screen">

    <script>
    // ════════════════════════════════════════════════════════════════
    //  CONFIGURATION
    // ════════════════════════════════════════════════════════════════

    // Group definitions — treat both orders as one group
    const GROUP_DEFS = [
        {
            key:     'johny austria|joseph cristal jr',
            label:   'Austria & Cristal',
            members: ['Johny Austria', 'Joseph Cristal Jr'],
            color:   'rgba(0, 229, 255, 0.82)',
            hover:   'rgba(0, 229, 255, 1)',
        },
        {
            key:     'jerico simon|petemar solano',
            label:   'Simon & Solano',
            members: ['Jerico Simon', 'Petemar Solano'],
            color:   'rgba(41, 121, 255, 0.82)',
            hover:   'rgba(41, 121, 255, 1)',
        },
        {
            key:     'chris neri|ronil jardio',
            label:   'Jardio & Neri',
            members: ['Ronil Jardio', 'Chris Neri'],
            color:   'rgba(255, 45, 149, 0.82)',
            hover:   'rgba(255, 45, 149, 1)',
        },
    ];

    // Individual definitions
    const INDIV_DEFS = [
        { label: 'Joseph Cristal Jr', color: 'rgba(0, 229, 255, 0.82)',   hover: 'rgba(0, 229, 255, 1)' },
        { label: 'Jerico Simon',   color: 'rgba(41, 121, 255, 0.82)',  hover: 'rgba(41, 121, 255, 1)' },
        { label: 'Johny Austria',  color: 'rgba(0, 230, 118, 0.82)',   hover: 'rgba(0, 230, 118, 1)' },
        { label: 'Petemar Solano', color: 'rgba(255, 202, 40, 0.82)',  hover: 'rgba(255, 202, 40, 1)' },
        { label: 'Ronil Jardio',   color: 'rgba(255, 145, 0, 0.82)',   hover: 'rgba(255, 145, 0, 1)' },
        { label: 'Chris Neri',     color: 'rgba(255, 45, 149, 0.82)',  hover: 'rgba(255, 45, 149, 1)' },
    ];

    // ── PHP-injected seed data (used for initial render before API resolves) ──
    const PHP_GROUP_DATA = <?= json_encode($groupData) ?>;
    const PHP_INDIV_DATA = <?= json_encode($indivData) ?>;

    // ════════════════════════════════════════════════════════════════
    //  STATE
    // ════════════════════════════════════════════════════════════════
    let allRecords    = [];        // raw records from API
    let filteredRecs  = [];        // after date filter
    let groupChartObj = null;
    let indivChartObj = null;
    let groupChartType = 'pie';
    let indivChartType = 'pie';
    let customEntry   = null;      // { label, value }
    let apiLoaded     = false;     // did API data arrive?

    // ════════════════════════════════════════════════════════════════
    //  NAME ALIAS MAP  (old DB spelling → canonical label key)
    // ════════════════════════════════════════════════════════════════
    const NAME_ALIASES = {
        'joseph cristal':     'joseph cristal jr',
        'joseph christal':    'joseph cristal jr',
        'joseph christal jr': 'joseph cristal jr',
    };

    // Aliases used when counting individuals (canonical label → all variant spellings)
    const INDIV_ALIASES = {
        'joseph cristal jr': ['joseph cristal jr', 'joseph cristal', 'joseph christal', 'joseph christal jr'],
    };

    // ════════════════════════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════════════════════════
    function normaliseGroupKey(performedBy) {
        if (!performedBy || typeof performedBy !== 'string') return null;
        if (performedBy.indexOf(' / ') === -1) return null;
        const names = performedBy.split(' / ').map(n => {
            const lc = n.trim().toLowerCase();
            return NAME_ALIASES[lc] || lc;   // apply alias before sorting
        });
        names.sort();
        return names.join('|');
    }

    function escapeHTML(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function rankBadge(rank) {
        if (rank === 1) return '<span class="badge badge-gold">🥇 #1</span>';
        if (rank === 2) return '<span class="badge badge-silver">🥈 #2</span>';
        if (rank === 3) return '<span class="badge badge-bronze">🥉 #3</span>';
        return `<span class="badge badge-norm">#${rank}</span>`;
    }

    function rankClass(rank) {
        if (rank === 1) return 'rank-1';
        if (rank === 2) return 'rank-2';
        if (rank === 3) return 'rank-3';
        return '';
    }

    // ════════════════════════════════════════════════════════════════
    //  SAFE FIELD READ — tolerate both camelCase and lowercase column names
    // ════════════════════════════════════════════════════════════════
    function getField(rec, ...keys) {
        for (const k of keys) {
            if (rec[k] !== undefined && rec[k] !== null) return rec[k];
        }
        return '';
    }

    // ════════════════════════════════════════════════════════════════
    //  DATE FILTER HELPERS  (pure string comparison — timezone-safe)
    // ════════════════════════════════════════════════════════════════
    function applyDateFilter(records) {
        const fromStr = (document.getElementById('filter-from').value || '').trim();
        const toStr   = (document.getElementById('filter-to').value   || '').trim();
        if (!fromStr && !toStr) return records;
        return records.filter(rec => {
            // Normalise date field: take first 10 chars (YYYY-MM-DD), strip time
            const dStr = (getField(rec, 'date') + '').substr(0, 10).trim();
            if (!dStr) return true;  // keep records with no date
            if (fromStr && dStr < fromStr) return false;
            if (toStr   && dStr > toStr)   return false;
            return true;
        });
    }

    function applyFilters() {
        filteredRecs = applyDateFilter(allRecords);
        const info = document.getElementById('filter-info');
        info.textContent = `Showing ${filteredRecs.length} of ${allRecords.length} record(s) in selected range.`;
        buildCharts(filteredRecs);
    }

    function resetFilters() {
        document.getElementById('filter-from').value  = '';
        document.getElementById('filter-to').value    = '';
        document.getElementById('quick-range').value  = '';
        filteredRecs = [...allRecords];
        document.getElementById('filter-info').textContent = `Showing all ${allRecords.length} record(s).`;
        buildCharts(filteredRecs);
    }

    // Quick-range selector
    document.getElementById('quick-range').addEventListener('change', function () {
        const v = this.value;
        const fromEl = document.getElementById('filter-from');
        const toEl   = document.getElementById('filter-to');
        const now = new Date();
        const fmt  = d => d.toISOString().split('T')[0];

        if (!v) { fromEl.value = ''; toEl.value = ''; return; }
        toEl.value = fmt(now);
        if (v === 'thismonth') {
            fromEl.value = fmt(new Date(now.getFullYear(), now.getMonth(), 1));
        } else if (v === 'lastmonth') {
            const first = new Date(now.getFullYear(), now.getMonth() - 1, 1);
            const last  = new Date(now.getFullYear(), now.getMonth(), 0);
            fromEl.value = fmt(first); toEl.value = fmt(last);
        } else {
            const d = new Date();
            d.setDate(d.getDate() - parseInt(v));
            fromEl.value = fmt(d);
        }
    });

    // ════════════════════════════════════════════════════════════════
    //  AGGREGATE FROM RECORDS
    // ════════════════════════════════════════════════════════════════
    function computeGroupData(records) {
        const counts = {};
        GROUP_DEFS.forEach(g => { counts[g.key] = 0; });

        records.forEach(rec => {
            // tolerate performedBy / performedby column name casing
            const pb = getField(rec, 'performedBy', 'performedby', 'performed_by');
            const nk = normaliseGroupKey(pb);
            if (nk && counts.hasOwnProperty(nk)) {
                counts[nk]++;
            }
        });

        return GROUP_DEFS.map(g => ({ label: g.label, value: counts[g.key], color: g.color, hover: g.hover }));
    }

    function computeIndivData(records) {
        const counts = {};
        INDIV_DEFS.forEach(i => { counts[i.label] = 0; });

        records.forEach(rec => {
            const pb = (getField(rec, 'performedBy', 'performedby', 'performed_by') || '').toLowerCase();
            INDIV_DEFS.forEach(i => {
                // Use alias list so old spellings (no "Jr") still count
                const patterns = INDIV_ALIASES[i.label.toLowerCase()] || [i.label.toLowerCase()];
                for (const p of patterns) {
                    if (pb.indexOf(p) !== -1) {
                        counts[i.label]++;
                        break; // count once per record, even if multiple aliases match
                    }
                }
            });
        });

        const result = INDIV_DEFS.map(i => ({ label: i.label, value: counts[i.label], color: i.color, hover: i.hover }));

        // Append custom entry if present
        if (customEntry) {
            const cv = (customEntry.overrideValue !== null)
                ? customEntry.overrideValue
                : countCustomInRecords(records, customEntry.label);
            result.push({
                label: customEntry.label,
                value: cv,
                color: 'rgba(156, 39, 176, 0.82)',
                hover: 'rgba(156, 39, 176, 1)',
            });
        }
        return result;
    }

    function countCustomInRecords(records, name) {
        const lc = name.toLowerCase();
        return records.filter(r => {
            const pb = getField(r, 'performedBy', 'performedby', 'performed_by');
            return pb && pb.toLowerCase().indexOf(lc) !== -1;
        }).length;
    }

    // ════════════════════════════════════════════════════════════════
    //  DIAGNOSTIC — shows raw performedBy breakdown for date range
    // ════════════════════════════════════════════════════════════════
    function renderDiagnostic(records) {
        const panel = document.getElementById('diagnostic-panel');
        if (!panel || panel.getAttribute('data-open') !== '1') return;

        const fromStr = (document.getElementById('filter-from').value || '').trim();
        const toStr   = (document.getElementById('filter-to').value   || '').trim();
        const rangeLabel = (fromStr || toStr)
            ? `${fromStr || '(any)'} → ${toStr || '(any)'}`
            : 'All Time';

        // Tally raw values
        const tally = {};
        records.forEach(rec => {
            const pb  = (getField(rec, 'performedBy', 'performedby', 'performed_by') || '(empty)').trim();
            const key = normaliseGroupKey(pb) || '__solo__';
            if (!tally[pb]) tally[pb] = { raw: pb, normKey: key, count: 0, dates: [] };
            tally[pb].count++;
            const d = (getField(rec, 'date') + '').substr(0, 10);
            if (d && !tally[pb].dates.includes(d)) tally[pb].dates.push(d);
        });

        const rows = Object.values(tally).sort((a, b) => b.count - a.count);

        let html = `<div style="font-size:12px;color:var(--muted);margin-bottom:8px;">Range: <strong style="color:var(--accent)">${escapeHTML(rangeLabel)}</strong> — ${records.length} records total</div>`;
        html += '<div style="overflow-x:auto;"><table class="summary-table"><thead><tr>';
        html += '<th>performedBy (raw from DB)</th><th>Normalised Key</th><th>Matched Group</th><th>Count</th></tr></thead><tbody>';
        rows.forEach(r => {
            const matchedGroup = GROUP_DEFS.find(g => g.key === r.normKey);
            const groupLabel   = matchedGroup ? `<span style="color:var(--success);">${escapeHTML(matchedGroup.label)}</span>`
                                              : `<span style="color:var(--muted);">— (solo / unmatched)</span>`;
            html += `<tr>
                <td style="font-family:monospace;font-size:12px;">${escapeHTML(r.raw)}</td>
                <td style="font-family:monospace;font-size:11px;color:var(--warn);">${escapeHTML(r.normKey)}</td>
                <td>${groupLabel}</td>
                <td style="font-weight:700;">${r.count}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        panel.innerHTML = html;
    }

    // ════════════════════════════════════════════════════════════════
    //  CHART.JS CONFIG FACTORY
    // ════════════════════════════════════════════════════════════════
    const DL_PLUGIN = typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels] : [];

    function buildChartConfig(type, labels, values, colors, hovers, title) {
        const total = values.reduce((a, b) => a + b, 0);
        const isBar = (type === 'bar');

        const datasets = [{
            data:                 values,
            backgroundColor:      colors,
            hoverBackgroundColor: hovers,
            borderColor:          'rgba(10,14,23,0.6)',
            borderWidth:          isBar ? 0 : 2,
            hoverOffset:          isBar ? 0 : 10,
        }];

        const commonTooltip = {
            backgroundColor: 'rgba(6,10,18,0.92)',
            titleColor:       '#00e5ff',
            bodyColor:        '#d0dce8',
            borderColor:      'rgba(0,229,255,0.25)',
            borderWidth:      1,
            padding:          12,
            callbacks: {
                label(ctx) {
                    const v   = ctx.raw;
                    const pct = total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';
                    const sorted = [...ctx.dataset.data].sort((a, b) => b - a);
                    const rank  = sorted.indexOf(v) + 1;
                    return [
                        ` Tasks: ${v}`,
                        ` Percentage: ${pct}%`,
                        ` Rank: #${rank}`,
                    ];
                },
            },
        };

        if (isBar) {
            return {
                type: 'bar',
                plugins: DL_PLUGIN,
                data: { labels, datasets: [{ ...datasets[0], backgroundColor: colors, borderRadius: 6, borderSkipped: false }] },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: { ...commonTooltip, callbacks: { ...commonTooltip.callbacks, title(ctx) { return ctx[0].label; } } },
                        datalabels: DL_PLUGIN.length ? {
                            color: '#fff', anchor: 'end', align: 'top', font: { size: 11, weight: 'bold' },
                            formatter: (v) => total > 0 ? ((v/total)*100).toFixed(1)+'%' : '0%',
                        } : undefined,
                        title: { display: !!title, text: title, color: '#00e5ff', font: { size: 14 }, padding: { bottom: 10 } },
                    },
                    scales: {
                        x: { ticks: { color: '#5a7a99' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                        y: { ticks: { color: '#5a7a99' }, grid: { color: 'rgba(255,255,255,0.06)' }, beginAtZero: true },
                    },
                },
            };
        }

        return {
            type,
            plugins: DL_PLUGIN,
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#d0dce8', padding: 16, font: { size: 12 },
                            generateLabels(chart) {
                                const data = chart.data;
                                if (!data.labels.length) return [];
                                return data.labels.map((lbl, i) => {
                                    const val = data.datasets[0].data[i];
                                    const pct = total > 0 ? ((val/total)*100).toFixed(1) : '0.0';
                                    return {
                                        text: `${lbl}  (${val} tasks · ${pct}%)`,
                                        fillStyle:   data.datasets[0].backgroundColor[i],
                                        strokeStyle: 'transparent',
                                        hidden:      false,
                                        index:       i,
                                    };
                                });
                            },
                        },
                    },
                    tooltip: commonTooltip,
                    datalabels: DL_PLUGIN.length ? {
                        color: '#fff',
                        font: { size: 11, weight: 'bold' },
                        formatter: (v, ctx) => {
                            if (!total) return '';
                            const pct = ((v / total) * 100).toFixed(1);
                            return parseFloat(pct) >= 5 ? pct + '%' : '';
                        },
                    } : undefined,
                    title: { display: !!title, text: title, color: '#00e5ff', font: { size: 14 }, padding: { bottom: 10 } },
                },
            },
        };
    }

    // ════════════════════════════════════════════════════════════════
    //  RENDER CHARTS
    // ════════════════════════════════════════════════════════════════
    function renderGroupChart(data) {
        const labels = data.map(d => d.label);
        const values = data.map(d => d.value);
        const colors = data.map(d => d.color);
        const hovers = data.map(d => d.hover);

        const cfg = buildChartConfig(groupChartType, labels, values, colors, hovers, '');

        if (groupChartObj) { groupChartObj.destroy(); }
        const canvas = document.getElementById('groupChart');
        groupChartObj = new Chart(canvas, cfg);
    }

    function renderIndivChart(data) {
        const labels = data.map(d => d.label);
        const values = data.map(d => d.value);
        const colors = data.map(d => d.color);
        const hovers = data.map(d => d.hover);

        const cfg = buildChartConfig(indivChartType, labels, values, colors, hovers, '');

        if (indivChartObj) { indivChartObj.destroy(); }
        const canvas = document.getElementById('indivChart');
        indivChartObj = new Chart(canvas, cfg);
    }

    // ════════════════════════════════════════════════════════════════
    //  SUMMARY TABLE
    // ════════════════════════════════════════════════════════════════
    function renderSummaryTable(containerId, data, caption) {
        const total = data.reduce((s, d) => s + d.value, 0);
        const sorted = [...data].sort((a, b) => b.value - a.value);

        let html = `<div style="font-size:13px;color:var(--accent);font-weight:600;margin-bottom:8px;">${escapeHTML(caption)}</div>`;
        html += `<div style="overflow-x:auto;"><table class="summary-table">`;
        html += `<thead><tr><th>Rank</th><th>Name</th><th>Tasks Completed</th><th>Share</th><th>Progress</th></tr></thead><tbody>`;
        sorted.forEach((d, i) => {
            const rank  = i + 1;
            const pct   = total > 0 ? ((d.value / total) * 100).toFixed(1) : '0.0';
            const cls   = rankClass(rank);
            html += `<tr>
                <td class="${cls}">${rankBadge(rank)}</td>
                <td class="${cls}" style="font-weight:${rank<=3?'700':'400'}">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${escapeHTML(d.color||'#888')};margin-right:6px;"></span>
                    ${escapeHTML(d.label)}
                </td>
                <td class="${cls}">${d.value}</td>
                <td class="${cls}">${pct}%</td>
                <td>
                    <div class="pbar-wrap" style="width:100px;">
                        <div class="pbar-fill" style="width:${pct}%;background:${escapeHTML(d.color||'#888')};"></div>
                    </div>
                </td>
            </tr>`;
        });
        html += `</tbody></table></div>`;
        html += `<div style="font-size:11px;color:var(--muted);margin-top:6px;">Total tasks: ${total}</div>`;
        document.getElementById(containerId).innerHTML = html;
    }

    // ════════════════════════════════════════════════════════════════
    //  TOP PERFORMER BOXES
    // ════════════════════════════════════════════════════════════════
    function renderTopPerformerBoxes(groupData, indivData) {
        const container = document.getElementById('top-performer-boxes');

        const topGroup = [...groupData].sort((a, b) => b.value - a.value)[0];
        const topIndiv = [...indivData].sort((a, b) => b.value - a.value)[0];
        const totalTasks = groupData.reduce((s, d) => s + d.value, 0);

        // find the most active month
        let topMonth = { label: 'N/A', value: 0 };
        if (filteredRecs.length > 0) {
            const mc = {};
            filteredRecs.forEach(r => {
                const rDate = (getField(r, 'date') + '').substr(0, 10);
                if (!rDate) return;
                const my = rDate.substr(0, 7);
                mc[my] = (mc[my] || 0) + 1;
            });
            const topMK = Object.keys(mc).sort((a, b) => mc[b] - mc[a])[0];
            if (topMK) {
                const [y, m] = topMK.split('-');
                const d = new Date(parseInt(y), parseInt(m)-1, 1);
                topMonth = { label: d.toLocaleDateString('en-US',{month:'short',year:'numeric'}), value: mc[topMK] };
            }
        }

        let html = '';

        if (topGroup && topGroup.value > 0) {
            html += `<div class="top-box">
                <div class="crown">🏆</div>
                <div class="name">${escapeHTML(topGroup.label)}</div>
                <div class="sub">Top Group</div>
                <div class="val">${topGroup.value}</div>
                <div class="rank-badge">tasks completed</div>
            </div>`;
        }

        if (topIndiv && topIndiv.value > 0) {
            html += `<div class="top-box">
                <div class="crown">🥇</div>
                <div class="name">${escapeHTML(topIndiv.label)}</div>
                <div class="sub">Top Individual</div>
                <div class="val">${topIndiv.value}</div>
                <div class="rank-badge">tasks performed</div>
            </div>`;
        }

        html += `<div class="top-box">
            <div class="crown">📋</div>
            <div class="name">${totalTasks}</div>
            <div class="sub">Total Group Tasks</div>
            <div class="val" style="font-size:14px;">in range</div>
        </div>`;

        if (topMonth.value > 0) {
            html += `<div class="top-box">
                <div class="crown">📅</div>
                <div class="name">${escapeHTML(topMonth.label)}</div>
                <div class="sub">Most Active Month</div>
                <div class="val">${topMonth.value}</div>
                <div class="rank-badge">tasks</div>
            </div>`;
        }

        container.innerHTML = html || '<div style="color:var(--muted);font-size:13px;padding:12px;">No performance data available for the selected range.</div>';
    }

    // ════════════════════════════════════════════════════════════════
    //  BUILD ALL CHARTS
    // ════════════════════════════════════════════════════════════════
    function buildCharts(records) {
        const gd = computeGroupData(records);
        const id = computeIndivData(records);

        renderGroupChart(gd);
        renderIndivChart(id);
        renderSummaryTable('group-summary-wrap', gd, 'Group Performance Summary');
        renderSummaryTable('indiv-summary-wrap', id, 'Individual Performance Summary');
        renderTopPerformerBoxes(gd, id);
        renderDiagnostic(records);
    }

    // ════════════════════════════════════════════════════════════════
    //  CHART TYPE SWITCHER
    // ════════════════════════════════════════════════════════════════
    function switchChartType(which, type, btn) {
        const switcherId = which === 'group' ? 'group-type-switcher' : 'indiv-type-switcher';
        document.querySelectorAll('#' + switcherId + ' button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (which === 'group') {
            groupChartType = type;
            renderGroupChart(computeGroupData(filteredRecs.length ? filteredRecs : allRecords));
        } else {
            indivChartType = type;
            renderIndivChart(computeIndivData(filteredRecs.length ? filteredRecs : allRecords));
        }
    }

    // ════════════════════════════════════════════════════════════════
    //  CUSTOM NAME
    // ════════════════════════════════════════════════════════════════
    function addCustomName() {
        const nameInput  = document.getElementById('custom-name-input');
        const valueInput = document.getElementById('custom-value-input');
        const errEl      = document.getElementById('custom-name-error');
        const name = nameInput.value.trim();

        // Validate name
        if (!name || !/^[\w\s\-\.']+$/u.test(name)) {
            errEl.style.display = 'block';
            nameInput.focus();
            return;
        }
        errEl.style.display = 'none';

        const ov = valueInput.value === '' ? null : Math.max(0, parseInt(valueInput.value) || 0);
        customEntry = { label: name, overrideValue: ov };
        document.getElementById('remove-custom-btn').style.display = 'inline-block';

        const src = filteredRecs.length ? filteredRecs : allRecords;
        renderIndivChart(computeIndivData(src));
        renderSummaryTable('indiv-summary-wrap', computeIndivData(src), 'Individual Performance Summary');
        renderTopPerformerBoxes(computeGroupData(src), computeIndivData(src));
    }

    function removeCustomName() {
        customEntry = null;
        document.getElementById('custom-name-input').value  = '';
        document.getElementById('custom-value-input').value = '';
        document.getElementById('remove-custom-btn').style.display = 'none';
        const src = filteredRecs.length ? filteredRecs : allRecords;
        renderIndivChart(computeIndivData(src));
        renderSummaryTable('indiv-summary-wrap', computeIndivData(src), 'Individual Performance Summary');
        renderTopPerformerBoxes(computeGroupData(src), computeIndivData(src));
    }

    // ════════════════════════════════════════════════════════════════
    //  EXPORT PNG
    // ════════════════════════════════════════════════════════════════
    function exportChartPNG(canvasId, filename) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const link = document.createElement('a');
        link.download = filename + '_' + new Date().toISOString().split('T')[0] + '.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    // ════════════════════════════════════════════════════════════════
    //  PRINT  — convert canvases to white-background images first
    // ════════════════════════════════════════════════════════════════
    function canvasToWhiteBgDataURL(canvas) {
        // Draw onto a temporary canvas with a white fill so the chart
        // is visible when printed on a white page.
        const tmp = document.createElement('canvas');
        tmp.width  = canvas.width;
        tmp.height = canvas.height;
        const ctx  = tmp.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, tmp.width, tmp.height);
        ctx.drawImage(canvas, 0, 0);
        return tmp.toDataURL('image/png');
    }

    function printPage() {
        // Stamp the print date
        const footerDate = document.getElementById('print-footer-date');
        if (footerDate) {
            footerDate.textContent = 'Printed: ' + new Date().toLocaleString('en-PH', {
                year: 'numeric', month: 'long', day: '2-digit',
                hour: '2-digit', minute: '2-digit'
            });
        }

        // Also stamp filter range if active
        const fromStr = (document.getElementById('filter-from').value || '').trim();
        const toStr   = (document.getElementById('filter-to').value   || '').trim();
        if (footerDate && (fromStr || toStr)) {
            footerDate.textContent += '  |  Range: ' + (fromStr || 'any') + ' → ' + (toStr || 'any');
        }
        const printImgs = [];
        ['groupChart', 'indivChart'].forEach(id => {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            const wrap = canvas.closest('.chart-wrap');
            if (!wrap) return;

            // Remove any previous print image
            const prev = wrap.querySelector('.chart-print-img');
            if (prev) prev.remove();

            const img = document.createElement('img');
            img.className = 'chart-print-img';
            img.src = canvasToWhiteBgDataURL(canvas);
            wrap.appendChild(img);
            printImgs.push({ wrap, img });
        });

        // Give browser one frame to attach the images, then print
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                window.print();

                // Clean up injected images after the print dialog closes
                // (afterprint fires when the dialog is dismissed)
                const cleanup = () => {
                    printImgs.forEach(({ img }) => img.remove());
                    window.removeEventListener('afterprint', cleanup);
                };
                window.addEventListener('afterprint', cleanup);
            });
        });
    }

    // ════════════════════════════════════════════════════════════════
    //  SEED CHARTS WITH PHP DATA (instant render before API)
    // ════════════════════════════════════════════════════════════════
    function buildFromPHPSeed() {
        const gd = PHP_GROUP_DATA.length > 0
            ? PHP_GROUP_DATA.map((d, i) => ({
                ...d,
                color: GROUP_DEFS[i] ? GROUP_DEFS[i].color : 'rgba(100,100,200,0.8)',
                hover: GROUP_DEFS[i] ? GROUP_DEFS[i].hover : 'rgba(100,100,200,1)',
              }))
            : GROUP_DEFS.map(g => ({ label: g.label, value: 0, color: g.color, hover: g.hover }));

        const id = PHP_INDIV_DATA.length > 0
            ? PHP_INDIV_DATA.map((d, i) => ({
                ...d,
                color: INDIV_DEFS[i] ? INDIV_DEFS[i].color : 'rgba(100,200,100,0.8)',
                hover: INDIV_DEFS[i] ? INDIV_DEFS[i].hover : 'rgba(100,200,100,1)',
              }))
            : INDIV_DEFS.map(i => ({ label: i.label, value: 0, color: i.color, hover: i.hover }));

        renderGroupChart(gd);
        renderIndivChart(id);
        renderSummaryTable('group-summary-wrap', gd, 'Group Performance Summary');
        renderSummaryTable('indiv-summary-wrap', id, 'Individual Performance Summary');
        renderTopPerformerBoxes(gd, id);

        const tag = document.getElementById('data-source-tag');
        tag.textContent = 'Loading live data…';
    }

    // ════════════════════════════════════════════════════════════════
    //  INITIALISE — fetch live records from the existing API
    // ════════════════════════════════════════════════════════════════
    async function init() {
        // Render seed data immediately for a fast initial paint
        buildFromPHPSeed();

        const tag = document.getElementById('data-source-tag');
        try {
            // Uses existing DB helper (db.js) — no modifications to API
            const records = await DB.list('epm_records_v1');
            allRecords   = Array.isArray(records) ? records : [];
            filteredRecs = [...allRecords];
            apiLoaded    = true;
            tag.textContent = `Live data: ${allRecords.length} record(s) loaded.`;
            document.getElementById('filter-info').textContent = `Showing all ${allRecords.length} record(s).`;
            buildCharts(filteredRecs);
        } catch (e) {
            console.warn('API fetch failed, using PHP seed data.', e);
            tag.textContent = '⚠ Could not load live data — displaying server-rendered totals.';
            // Leave seed charts in place; allRecords stays empty so filters are a no-op
        }
    }

    // Start after DOM ready
    document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
