<?php require_once __DIR__.'/db-config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <link rel="icon" type="image/x-icon" href="images/favicon.ico">
  <title>Store List - Saved Records</title>
  <style>
    /* ── CSS Variables & Design Tokens ──────────────────────────── */
    :root {
      --page-bg: #f1f5f9;
      --panel-bg: #ffffff;
      --panel-alt: #f8fafc;
      --line: #e2e8f0;
      --line-strong: #cbd5e1;
      --accent: #0f766e;
      --accent-hover: #0d6460;
      --accent-soft: #dff5f2;
      --accent-xsoft: #f0fdfa;
      --warning: #f59e0b;
      --warning-hover: #d97706;
      --text: #0f172a;
      --text-secondary: #475569;
      --muted: #94a3b8;
      --danger: #dc2626;
      --danger-soft: #fef2f2;
      --success: #059669;
      --shadow-sm: 0 1px 3px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
      --shadow-md: 0 4px 12px rgba(15,23,42,0.08), 0 2px 4px rgba(15,23,42,0.04);
      --shadow-lg: 0 12px 40px rgba(15,23,42,0.1);
      --radius-sm: 8px;
      --radius-md: 12px;
      --radius-lg: 16px;
      --radius-xl: 20px;
      --font: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
      --transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ── Reset & Base ──────────────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font);
      background: var(--page-bg);
      color: var(--text);
      min-height: 100vh;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Page Header ───────────────────────────────────────────── */
    .page-header {
      background: linear-gradient(135deg, #0f766e 0%, #134e4a 50%, #1e293b 100%);
      color: #fff;
      padding: 24px 32px;
      position: relative;
      overflow: hidden;
    }
    .page-header::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
    }
    .page-header h1 {
      font-size: 1.4rem;
      font-weight: 800;
      letter-spacing: -0.01em;
      margin-bottom: 4px;
    }
    .page-header .subtitle {
      font-size: 0.82rem;
      opacity: 0.75;
      font-weight: 400;
    }

    /* ── Container ─────────────────────────────────────────────── */
    .container {
      max-width: 100%;
      margin: 0 auto;
      padding: 20px 24px;
    }

    /* ── Toolbar ───────────────────────────────────────────────── */
    .toolbar {
      display: flex;
      gap: 8px;
      margin-bottom: 12px;
      flex-wrap: wrap;
      align-items: center;
    }
    .toolbar .record-count {
      margin-left: auto;
      font-size: 0.8rem;
      color: var(--text-secondary);
      font-weight: 600;
      background: var(--panel-alt);
      padding: 4px 12px;
      border-radius: 999px;
      border: 1px solid var(--line);
    }

    /* ── Buttons ───────────────────────────────────────────────── */
    button {
      border: 0;
      padding: 9px 18px;
      border-radius: var(--radius-sm);
      cursor: pointer;
      font-size: 0.8125rem;
      font-weight: 600;
      transition: all var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: var(--font);
      white-space: nowrap;
      line-height: 1.4;
    }
    button:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow-md);
    }
    button:active { transform: translateY(0); }
    .btn-primary {
      background: var(--accent) !important;
      color: #fff !important;
      border: none !important;
    }
    .btn-primary:hover { background: var(--accent-hover) !important; }
    .btn-secondary {
      background: #e2e8f0 !important;
      color: var(--text) !important;
      border: none !important;
    }
    .btn-secondary:hover { background: var(--line-strong) !important; }
    .btn-danger {
      background: var(--danger-soft) !important;
      color: var(--danger) !important;
      border: 1px solid #fecaca !important;
    }
    .btn-danger:hover { background: #fee2e2 !important; }
    .btn-outline {
      background: transparent !important;
      border: 1.5px solid var(--accent) !important;
      color: var(--accent) !important;
    }
    .btn-outline:hover { background: var(--accent-xsoft) !important; }
    .btn-warning {
      background: var(--warning) !important;
      color: #fff !important;
      border: none !important;
    }
    .btn-warning:hover { background: var(--warning-hover) !important; }
    .btn-sm {
      padding: 5px 12px;
      font-size: 0.75rem;
      border-radius: 6px;
      justify-content: center;
      text-align: center;
    }

    /* ── Filter Bar ────────────────────────────────────────────── */
    .filter-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 16px;
      flex-wrap: wrap;
      align-items: center;
      background: var(--panel-bg);
      padding: 14px 18px;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--line);
    }
    .filter-bar label {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--text-secondary);
      white-space: nowrap;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .filter-bar input[type=date],
    .filter-bar select,
    .filter-bar input[type=text] {
      padding: 7px 12px;
      border: 1.5px solid var(--line-strong);
      border-radius: var(--radius-sm);
      font-size: 0.8125rem;
      color: var(--text);
      background: var(--panel-alt);
      font-family: var(--font);
      transition: border-color var(--transition), box-shadow var(--transition);
    }
    .filter-bar input:focus,
    .filter-bar select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(15,118,110,0.12);
      background: #fff;
    }
    .filter-bar .filter-group {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .filter-bar .sep { color: var(--line-strong); font-weight: 300; }
    #filterInfo {
      margin-left: auto;
      font-size: 0.78rem;
      color: var(--text-secondary);
      font-weight: 500;
    }

    /* ── Card & Table ──────────────────────────────────────────── */
    .card {
      background: var(--panel-bg);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--line);
      overflow: hidden;
      transition: box-shadow var(--transition);
    }
    .table-wrapper {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.8125rem;
      table-layout: auto;
    }
    thead th {
      background: #1e293b;
      color: #e2e8f0;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.68rem;
      letter-spacing: 0.05em;
      padding: 12px 12px;
      border-bottom: 2px solid #334155;
      text-align: left;
      white-space: nowrap;
      cursor: pointer;
      user-select: none;
      position: sticky;
      top: 0;
      z-index: 2;
      transition: background var(--transition);
    }
    thead th:hover { background: #334155; }
    thead th .sort-icon {
      font-size: 0.56rem;
      margin-left: 4px;
      opacity: 0.45;
      display: inline-block;
      vertical-align: middle;
    }
    thead th.sorted .sort-icon { opacity: 1; color: #5eead4; }
    tbody td {
      padding: 10px 12px;
      border-bottom: 1px solid var(--line);
      vertical-align: middle;
      color: var(--text);
      font-weight: 500;
      white-space: normal;
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    tbody tr { transition: background var(--transition); }
    tbody tr:nth-child(even) { background: #d4e8e5; }
    tbody tr:hover { background: var(--accent-xsoft); }
    tbody tr:last-child td { border-bottom: none; }
    .cell-filename {
      font-weight: 700;
      color: var(--accent);
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    .cell-date {
      font-size: 0.78rem;
      white-space: nowrap;
      color: var(--text-secondary);
    }

    /* ── Column widths ─────────────────────────────────────────── */
    col.col-id { width: 5%; }
    col.col-filename { width: 22%; }
    col.col-areas { width: 8%; }
    col.col-main { width: 8%; }
    col.col-sat { width: 8%; }
    col.col-redemption { width: 10%; }
    col.col-date { width: 14%; }
    col.col-actions { width: 25%; }

    /* ── Actions Cell ──────────────────────────────────────────── */
    .actions-cell {
      display: flex;
      gap: 4px;
      flex-wrap: nowrap;
      align-items: center;
    }
    .actions-cell button { flex: 0 0 auto; }

    /* ── Stat badges in table ──────────────────────────────────── */
    .stat-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      padding: 2px 10px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.75rem;
      background: var(--accent-soft);
      color: var(--accent);
    }

    /* ── Empty State ───────────────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--muted);
    }
    .empty-state .icon {
      font-size: 48px;
      margin-bottom: 12px;
      opacity: 0.5;
    }
    .empty-state p {
      font-size: 1rem;
      margin-bottom: 6px;
      font-weight: 600;
      color: var(--text-secondary);
    }
    .empty-state .hint {
      font-size: 0.85rem;
      color: var(--muted);
    }

    /* ── View Modal ────────────────────────────────────────────── */
    .view-overlay {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 9000;
      background: rgba(15,23,42,0.5);
      backdrop-filter: blur(6px);
      justify-content: center;
      align-items: center;
    }
    .view-overlay.active { display: flex; }
    .view-modal {
      background: var(--panel-bg);
      border-radius: var(--radius-xl);
      width: min(96vw, 1100px);
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      box-shadow: 0 24px 60px rgba(0,0,0,0.2);
      animation: modalIn 0.25s ease-out;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: translateY(16px) scale(0.97); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .view-modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--line);
    }
    .view-modal-header h2 {
      margin: 0;
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--text);
    }
    .view-modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--muted);
      line-height: 1;
      padding: 4px 8px;
      border-radius: var(--radius-sm);
      transition: all var(--transition);
    }
    .view-modal-close:hover { color: var(--text); background: var(--panel-alt); }
    .view-modal-body {
      padding: 20px 24px;
      overflow-y: auto;
      flex: 1;
    }
    .view-modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 16px 24px;
      border-top: 1px solid var(--line);
    }

    /* ── View Modal Stats ──────────────────────────────────────── */
    .view-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 10px;
      margin-bottom: 18px;
    }
    .view-stat {
      background: var(--panel-alt);
      border-radius: var(--radius-md);
      padding: 14px 14px;
      text-align: center;
      border: 1px solid var(--line);
    }
    .view-stat .label {
      font-size: 0.68rem;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 600;
    }
    .view-stat .value {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--accent);
      margin-top: 2px;
    }

    /* ── View Area Blocks ──────────────────────────────────────── */
    .view-area-block { margin-bottom: 14px; }
    .view-area-block h4 {
      margin: 0 0 6px;
      font-size: 0.82rem;
      color: var(--text-secondary);
      padding: 8px 12px;
      background: var(--panel-alt);
      border-radius: var(--radius-sm);
      font-weight: 700;
      border: 1px solid var(--line);
    }
    .view-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.78rem;
    }
    .view-table th {
      background: var(--line);
      padding: 6px 8px;
      text-align: left;
      font-weight: 600;
      color: var(--text-secondary);
      position: sticky;
      top: 0;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .view-table td {
      padding: 5px 8px;
      border-bottom: 1px solid var(--line);
    }
    .view-table tr:hover td { background: var(--panel-alt); }

    /* ── Toast ──────────────────────────────────────────────────── */
    .toast {
      position: fixed;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      background: var(--accent);
      color: #fff;
      padding: 12px 24px;
      border-radius: var(--radius-md);
      font-weight: 600;
      font-size: 0.875rem;
      box-shadow: var(--shadow-lg);
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.3s, transform 0.3s;
      pointer-events: none;
    }
    .toast.show {
      opacity: 1;
      transform: translateX(-50%) translateY(-4px);
    }

    /* ── Responsive ────────────────────────────────────────────── */
    @media (max-width: 768px) {
      .page-header { padding: 16px 18px; }
      .page-header h1 { font-size: 1.15rem; }
      .container { padding: 12px; }
      .toolbar { gap: 6px; }
      button { padding: 8px 14px; font-size: 0.78rem; }
      .btn-sm { padding: 4px 10px; font-size: 0.72rem; }
      table { font-size: 0.78rem; }
      thead th { padding: 10px 8px; font-size: 0.62rem; }
      tbody td { padding: 8px; }
      .filter-bar { padding: 10px 14px; gap: 8px; }
    }
    @media (max-width: 768px) {
      .container { max-width: 1200px !important; margin-left: auto !important; margin-right: auto !important; }
      .toolbar, .filter-bar { flex-wrap: nowrap !important; overflow: auto !important; padding-bottom: 6px; }
      .toolbar > *, .filter-bar > * { flex: 0 0 auto !important; }
      .table-wrapper { overflow-x: auto !important; }
      .table-wrapper table { display: table !important; table-layout: auto !important; min-width: 900px !important; }
      thead th, tbody td { white-space: nowrap !important; }
      html, body { min-width: 1000px !important; }
    }

    /* ── Print ──────────────────────────────────────────────────── */
    @media print {
      .toolbar, .filter-bar, .page-header, .actions-cell, .th-actions, .col-actions { display: none !important; }
      body { background: #fff !important; padding: 8mm; }
      .card { box-shadow: none; border-radius: 0; border: none; }
      table { font-size: 9pt !important; table-layout: fixed !important; width: 100% !important; border-collapse: collapse !important; }
      thead th {
        background: #0f766e !important;
        color: #fff !important;
        padding: 6px 7px !important;
        font-size: 9pt !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      tbody td { padding: 5px 7px !important; border-bottom: 1px solid #d1fae5 !important; }
      tbody tr:nth-child(even) td {
        background: #f0fdf9 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .cell-filename { color: #0f766e !important; font-weight: 700 !important; }
      col.col-id { width: 5% !important; }
      col.col-filename { width: 32% !important; }
      col.col-areas { width: 9% !important; }
      col.col-main { width: 9% !important; }
      col.col-sat { width: 9% !important; }
      col.col-redemption { width: 12% !important; }
      col.col-date { width: 24% !important; }
      @page { size: A3 landscape; margin: 10mm; }
    }
  </style>
  <link rel="stylesheet" href="hangar-theme.css">
  <link rel="stylesheet" href="responsive.css">
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
  <script src="firebase-config.js"></script>
  <script src="auth-guard.js"></script>
  <script src="db.js"></script>
</head>
<body>
  <div class="page-header">
    <h1>Store List Records</h1>
    <div class="subtitle">Saved store list snapshots &amp; reports</div>
  </div>
  <div class="container">
    <div class="toolbar">
      <button class="btn-primary" onclick="window.open('store_list.php','_blank')">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Open Store List
      </button>
      <button class="btn-outline" onclick="print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print List
      </button>
      <button class="btn-secondary" onclick="window.location.href='index.php'">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Home
      </button>
      <span class="record-count" id="recordCount"></span>
    </div>

    <div class="filter-bar">
      <div class="filter-group">
        <label>Search:</label>
        <input type="text" id="searchInput" placeholder="File name..." style="min-width:180px">
      </div>
      <div class="filter-group">
        <label>From:</label>
        <input type="date" id="dateFrom">
      </div>
      <div class="filter-group">
        <label>To:</label>
        <input type="date" id="dateTo">
      </div>
      <div class="filter-group">
        <label>Quick:</label>
        <select id="quickFilter">
          <option value="">All Time</option>
          <option value="thisWeek">This Week</option>
          <option value="lastWeek">Last Week</option>
          <option value="thisMonth">This Month</option>
          <option value="lastMonth">Last Month</option>
          <option value="last3">Last 3 Months</option>
          <option value="last6">Last 6 Months</option>
          <option value="thisYear">This Year</option>
        </select>
      </div>
      <button class="btn-primary btn-sm" onclick="applyFilters()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter
      </button>
      <button class="btn-secondary btn-sm" onclick="clearFilters()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
        Clear
      </button>
      <span id="filterInfo"></span>
    </div>

    <div class="card">
      <div class="table-wrapper" id="tableContainer">
        <div class="empty-state" id="emptyState">
          <div class="icon">&#128203;</div>
          <p>No saved store list records yet</p>
          <div class="hint">Save a snapshot from the Store List page to see it here.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- View Detail Modal -->
  <div class="view-overlay" id="viewOverlay">
    <div class="view-modal">
      <div class="view-modal-header">
        <h2 id="viewTitle">Store List Snapshot</h2>
        <button class="view-modal-close" id="viewClose" type="button">&times;</button>
      </div>
      <div class="view-modal-body" id="viewBody"></div>
      <div class="view-modal-footer">
        <button class="btn-outline btn-sm" id="viewPrintBtn">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Print
        </button>
        <button class="btn-secondary btn-sm" id="viewCloseBtn">Close</button>
      </div>
    </div>
  </div>
  <div class="toast" id="toast"></div>

  <script src="hangar-theme.js"></script>
  <script src="theme-loader.js"></script>
  <script>
    const COLLECTION = 'epm_store_list_v1';
    let allRecords = [];
    let filteredRecords = [];
    let isAdminUser = false;
    let sortCol = 'savedAt';
    let sortDir = 'desc';

    const escapeHTML = s => String(s||'')
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    const showToast = (msg, isError) => {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? '#dc2626' : 'var(--accent, #0f766e)';
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 3000);
    };

    // ── Admin check ──────────────────────────────────────────────
    setTimeout(function checkAdmin() {
      const user = firebase.auth().currentUser;
      if (!user) { setTimeout(checkAdmin, 500); return; }
      user.getIdTokenResult(true).then(r => {
        if (r.claims && r.claims.admin === true) {
          isAdminUser = true;
          renderTable();
        }
      }).catch(() => {});
    }, 2000);

    // ── Date helpers ─────────────────────────────────────────────
    const toLocalDate = s => {
      if (!s) return '';
      const d = new Date(s);
      return isNaN(d) ? '' : d.toLocaleDateString('en-US', {year:'numeric',month:'short',day:'numeric'}) + ' ' + d.toLocaleTimeString('en-US', {hour:'2-digit',minute:'2-digit'});
    };
    const toISO = s => {
      if (!s) return '';
      const d = new Date(s);
      return isNaN(d) ? '' : d.toISOString().slice(0,10);
    };

    // ── Quick filter date range helper ───────────────────────────
    const getQuickRange = key => {
      const now = new Date();
      const startOfWeek = d => { const r = new Date(d); r.setDate(r.getDate() - r.getDay()); r.setHours(0,0,0,0); return r; };
      let from, to;
      switch(key) {
        case 'thisWeek': from = startOfWeek(now); to = new Date(); break;
        case 'lastWeek': { const s = startOfWeek(now); s.setDate(s.getDate()-7); from=s; to=new Date(s); to.setDate(to.getDate()+6); to.setHours(23,59,59); break; }
        case 'thisMonth': from = new Date(now.getFullYear(), now.getMonth(), 1); to = new Date(); break;
        case 'lastMonth': from = new Date(now.getFullYear(), now.getMonth()-1, 1); to = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59); break;
        case 'last3': from = new Date(now.getFullYear(), now.getMonth()-3, now.getDate()); to = new Date(); break;
        case 'last6': from = new Date(now.getFullYear(), now.getMonth()-6, now.getDate()); to = new Date(); break;
        case 'thisYear': from = new Date(now.getFullYear(), 0, 1); to = new Date(); break;
        default: return null;
      }
      return { from, to };
    };

    // ── Filtering ────────────────────────────────────────────────
    const applyFilters = () => {
      const search = document.getElementById('searchInput').value.trim().toLowerCase();
      const dateFrom = document.getElementById('dateFrom').value;
      const dateTo = document.getElementById('dateTo').value;
      const quick = document.getElementById('quickFilter').value;

      let from = dateFrom ? new Date(dateFrom + 'T00:00:00') : null;
      let to = dateTo ? new Date(dateTo + 'T23:59:59') : null;

      if (quick) {
        const range = getQuickRange(quick);
        if (range) { from = range.from; to = range.to; }
      }

      filteredRecords = allRecords.filter(r => {
        if (search && !(r.fileName || '').toLowerCase().includes(search)) return false;
        if (from || to) {
          const d = new Date(r.savedAt);
          if (isNaN(d)) return false;
          if (from && d < from) return false;
          if (to && d > to) return false;
        }
        return true;
      });

      sortRecords();
      renderTable();

      const info = document.getElementById('filterInfo');
      if (search || from || to) {
        info.textContent = 'Showing ' + filteredRecords.length + ' of ' + allRecords.length + ' records';
      } else {
        info.textContent = '';
      }
    };

    const clearFilters = () => {
      document.getElementById('searchInput').value = '';
      document.getElementById('dateFrom').value = '';
      document.getElementById('dateTo').value = '';
      document.getElementById('quickFilter').value = '';
      document.getElementById('filterInfo').textContent = '';
      filteredRecords = [...allRecords];
      sortRecords();
      renderTable();
    };

    // Mutual exclusion: quick filter clears manual dates and vice versa
    document.getElementById('quickFilter').addEventListener('change', () => {
      document.getElementById('dateFrom').value = '';
      document.getElementById('dateTo').value = '';
    });
    document.getElementById('dateFrom').addEventListener('change', () => document.getElementById('quickFilter').value = '');
    document.getElementById('dateTo').addEventListener('change', () => document.getElementById('quickFilter').value = '');

    // Debounced search on typing
    let searchDebounce;
    document.getElementById('searchInput').addEventListener('input', () => {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(applyFilters, 300);
    });

    // ── Sorting ──────────────────────────────────────────────────
    const sortRecords = () => {
      filteredRecords.sort((a, b) => {
        let va = a[sortCol], vb = b[sortCol];
        if (sortCol === 'savedAt') {
          va = new Date(va || 0).getTime();
          vb = new Date(vb || 0).getTime();
        } else if (['totalMain','totalSat','totalRedemption','totalAreas','id'].includes(sortCol)) {
          va = Number(va) || 0;
          vb = Number(vb) || 0;
        } else {
          va = String(va || '').toLowerCase();
          vb = String(vb || '').toLowerCase();
        }
        if (va < vb) return sortDir === 'asc' ? -1 : 1;
        if (va > vb) return sortDir === 'asc' ? 1 : -1;
        return 0;
      });
    };

    const onSort = col => {
      if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        sortCol = col;
        sortDir = col === 'savedAt' ? 'desc' : 'asc';
      }
      sortRecords();
      renderTable();
    };

    // ── Render ───────────────────────────────────────────────────
    const sortIcon = col => {
      if (sortCol !== col) return '<span class="sort-icon">&#9650;&#9660;</span>';
      return sortDir === 'asc'
        ? '<span class="sort-icon">&#9650;</span>'
        : '<span class="sort-icon">&#9660;</span>';
    };

    const renderTable = () => {
      const container = document.getElementById('tableContainer');
      const count = document.getElementById('recordCount');
      count.textContent = filteredRecords.length + ' record' + (filteredRecords.length !== 1 ? 's' : '');

      if (filteredRecords.length === 0) {
        container.innerHTML = `<div class="empty-state">
          <div class="icon">&#128203;</div>
          <p>No records found</p>
          <div class="hint">Save a snapshot from the Store List page or adjust your filters.</div>
        </div>`;
        return;
      }

      const thClass = col => sortCol === col ? 'sorted' : '';

      let html = `<table>
        <colgroup>
          <col class="col-id"><col class="col-filename"><col class="col-areas">
          <col class="col-main"><col class="col-sat"><col class="col-redemption">
          <col class="col-date"><col class="col-actions">
        </colgroup>
        <thead><tr>
          <th class="${thClass('id')}" onclick="onSort('id')"># ${sortIcon('id')}</th>
          <th class="${thClass('fileName')}" onclick="onSort('fileName')">File Name ${sortIcon('fileName')}</th>
          <th class="${thClass('totalAreas')}" onclick="onSort('totalAreas')">Areas ${sortIcon('totalAreas')}</th>
          <th class="${thClass('totalMain')}" onclick="onSort('totalMain')">Main ${sortIcon('totalMain')}</th>
          <th class="${thClass('totalSat')}" onclick="onSort('totalSat')">Sat ${sortIcon('totalSat')}</th>
          <th class="${thClass('totalRedemption')}" onclick="onSort('totalRedemption')">Redemption ${sortIcon('totalRedemption')}</th>
          <th class="${thClass('savedAt')}" onclick="onSort('savedAt')">Saved At ${sortIcon('savedAt')}</th>
          <th class="th-actions">Actions</th>
        </tr></thead><tbody>`;

      filteredRecords.forEach(r => {
        const deleteBtn = isAdminUser ? `<button class="btn-danger btn-sm" onclick="deleteRecord(${r.id})">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
          Delete
        </button>` : '';
        html += `<tr>
          <td>${escapeHTML(r.id)}</td>
          <td class="cell-filename">${escapeHTML(r.fileName)}</td>
          <td><span class="stat-badge">${escapeHTML(r.totalAreas)}</span></td>
          <td><span class="stat-badge">${escapeHTML(r.totalMain)}</span></td>
          <td><span class="stat-badge">${escapeHTML(r.totalSat)}</span></td>
          <td><span class="stat-badge">${escapeHTML(r.totalRedemption)}</span></td>
          <td class="cell-date">${toLocalDate(r.savedAt)}</td>
          <td><div class="actions-cell">
            <button class="btn-primary btn-sm" onclick="viewRecord(${r.id})">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View
            </button>
            <button class="btn-warning btn-sm" onclick="editRecord(${r.id})">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit
            </button>
            <button class="btn-outline btn-sm" onclick="printRecord(${r.id})">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Print
            </button>
            <button class="btn-secondary btn-sm" onclick="exportRecord(${r.id})">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              JSON
            </button>
            ${deleteBtn}
          </div></td>
        </tr>`;
      });

      html += '</tbody></table>';
      container.innerHTML = html;
    };

    // ── Load data ────────────────────────────────────────────────
    const loadRecords = async () => {
      try {
        allRecords = await DB.list(COLLECTION);
        if (!Array.isArray(allRecords)) allRecords = [];
        filteredRecords = [...allRecords];
        sortRecords();
        renderTable();
      } catch (err) {
        showToast('Failed to load records: ' + err.message, true);
      }
    };

    // ── View detail ──────────────────────────────────────────────
    const viewRecord = id => {
      const rec = allRecords.find(r => r.id === id);
      if (!rec) return;

      document.getElementById('viewTitle').textContent = rec.fileName || 'Store List Snapshot';
      const body = document.getElementById('viewBody');

      let snapshot = rec.snapshot;
      if (typeof snapshot === 'string') {
        try { snapshot = JSON.parse(snapshot); } catch(e) { snapshot = null; }
      }

      let statsHtml = `<div class="view-stats">
        <div class="view-stat"><div class="label">Areas</div><div class="value">${escapeHTML(rec.totalAreas)}</div></div>
        <div class="view-stat"><div class="label">Main</div><div class="value">${escapeHTML(rec.totalMain)}</div></div>
        <div class="view-stat"><div class="label">Sat</div><div class="value">${escapeHTML(rec.totalSat)}</div></div>
        <div class="view-stat"><div class="label">Redemption</div><div class="value">${escapeHTML(rec.totalRedemption)}</div></div>
        <div class="view-stat"><div class="label">Saved</div><div class="value" style="font-size:0.82rem">${toLocalDate(rec.savedAt)}</div></div>
      </div>`;

      let areasHtml = '';
      if (snapshot && Array.isArray(snapshot.areas)) {
        areasHtml = snapshot.areas.map(area => {
          const filled = (area.entries || []).filter(e => (e.branchName||'').trim() || (e.plCode||'').trim() || (e.branchCode||'').trim());
          if (filled.length === 0) return '';
          const rows = filled.map(e => `<tr>
            <td>${escapeHTML(e.plCode)}</td>
            <td>${escapeHTML(e.branchCode)}</td>
            <td>${escapeHTML(e.branchName)}</td>
            <td>${escapeHTML(e.sqm)}</td>
            <td>${escapeHTML(e.tradeName)}</td>
            <td>${escapeHTML(e.company)}</td>
            <td>${escapeHTML(e.lastPmDate)}</td>
            <td>${escapeHTML(e.conductedBy)}</td>
          </tr>`).join('');
          return `<div class="view-area-block">
            <h4>${escapeHTML(area.name)} (${filled.length} entries)</h4>
            <table class="view-table">
              <thead><tr><th>PL</th><th>Code</th><th>Branch</th><th>SQM</th><th>Trade Name</th><th>Company</th><th>Last PM</th><th>Conducted By</th></tr></thead>
              <tbody>${rows}</tbody>
            </table>
          </div>`;
        }).join('');
      }

      if (!areasHtml) areasHtml = '<p style="color:var(--muted);text-align:center;padding:30px 0">No area data available in this snapshot.</p>';

      body.innerHTML = statsHtml + areasHtml;
      document.getElementById('viewOverlay').classList.add('active');
    };

    // ── Close modal ──────────────────────────────────────────────
    const closeView = () => document.getElementById('viewOverlay').classList.remove('active');
    document.getElementById('viewClose').addEventListener('click', closeView);
    document.getElementById('viewCloseBtn').addEventListener('click', closeView);
    document.getElementById('viewOverlay').addEventListener('click', e => { if (e.target === document.getElementById('viewOverlay')) closeView(); });
    // Close modal with Escape key
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeView(); });

    // ── Print single record ──────────────────────────────────────
    document.getElementById('viewPrintBtn').addEventListener('click', () => {
      const content = document.getElementById('viewBody').innerHTML;
      const title = document.getElementById('viewTitle').textContent;
      const w = window.open('', '_blank');
      if (!w) { alert('Please allow pop-ups to print.'); return; }
      w.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${escapeHTML(title)}</title>
        <style>
          *{box-sizing:border-box;margin:0;padding:0}
          body{font-family:'Segoe UI',Arial,sans-serif;padding:12mm 14mm;color:#1a1a2e;font-size:10pt;background:#fff}
          .print-title{border-bottom:3px solid #0f766e;padding-bottom:10px;margin-bottom:14px}
          .print-title h1{font-size:15pt;color:#0f766e;font-weight:700;letter-spacing:0.3px}
          .print-title .print-meta{font-size:8pt;color:#64748b;margin-top:4px}
          .view-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-bottom:16px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
          .view-stat{background:#e8f5f3;border:1.5px solid #0f766e;border-radius:6px;padding:9px 8px;text-align:center;-webkit-print-color-adjust:exact;print-color-adjust:exact}
          .view-stat .label{font-size:7.5pt;color:#0f766e;text-transform:uppercase;font-weight:700;letter-spacing:0.05em}
          .view-stat .value{font-size:15pt;font-weight:700;color:#0f766e;margin-top:2px}
          .view-area-block{margin-bottom:18px;page-break-inside:avoid}
          h4{margin:0 0 6px;font-size:9.5pt;background:#0f766e;color:#fff;padding:5px 10px;border-radius:4px;font-weight:700;
            -webkit-print-color-adjust:exact;print-color-adjust:exact}
          table{width:100%;border-collapse:collapse;font-size:8.5pt;table-layout:fixed;margin-bottom:6px;border:1.5px solid #0f766e}
          thead th{background:#0f766e;color:#fff;padding:5px 7px;text-align:left;font-weight:700;border-right:1px solid #0d6460;
            word-wrap:break-word;overflow-wrap:break-word;
            -webkit-print-color-adjust:exact;print-color-adjust:exact}
          thead th:last-child{border-right:none}
          tbody td{padding:4px 7px;border-bottom:1px solid #d1fae5;word-wrap:break-word;overflow-wrap:break-word;vertical-align:top}
          tbody tr:nth-child(even) td{background:#f0fdf9;-webkit-print-color-adjust:exact;print-color-adjust:exact}
          tbody tr:last-child td{border-bottom:2px solid #0f766e}
          .view-table th:nth-child(1),.view-table td:nth-child(1){width:8%}
          .view-table th:nth-child(2),.view-table td:nth-child(2){width:9%}
          .view-table th:nth-child(3),.view-table td:nth-child(3){width:18%}
          .view-table th:nth-child(4),.view-table td:nth-child(4){width:7%}
          .view-table th:nth-child(5),.view-table td:nth-child(5){width:16%}
          .view-table th:nth-child(6),.view-table td:nth-child(6){width:18%}
          .view-table th:nth-child(7),.view-table td:nth-child(7){width:12%}
          .view-table th:nth-child(8),.view-table td:nth-child(8){width:12%}
          @page{size:A3 landscape;margin:10mm}
        </style>
      </head><body>
        <div class="print-title">
          <h1>${escapeHTML(title)}</h1>
          <div class="print-meta">Printed: ${new Date().toLocaleString('en-US',{year:'numeric',month:'long',day:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
        </div>
        ${content}
      </body></html>`);
      w.document.close();
      w.focus();
      setTimeout(() => w.print(), 400);
    });

    const printRecord = id => {
      viewRecord(id);
      setTimeout(() => document.getElementById('viewPrintBtn').click(), 300);
    };

    // ── Export single record JSON ────────────────────────────────
    const exportRecord = id => {
      const rec = allRecords.find(r => r.id === id);
      if (!rec) return;
      const blob = new Blob([JSON.stringify(rec.snapshot || rec, null, 2)], {type:'application/json'});
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = (rec.fileName || 'store-list-snapshot') + '.json';
      link.click();
      URL.revokeObjectURL(link.href);
    };

    // ── Edit record (load snapshot into store_list.php) ───────────
    const editRecord = id => {
      const rec = allRecords.find(r => r.id === id);
      if (!rec) return;
      if (!confirm('This will load the snapshot into the Store List editor, replacing any unsaved work.\n\nContinue?')) return;
      let snapshot = rec.snapshot;
      if (typeof snapshot === 'string') {
        try { snapshot = JSON.parse(snapshot); } catch(e) { showToast('Invalid snapshot data', true); return; }
      }
      if (!snapshot || !Array.isArray(snapshot.areas)) {
        showToast('This record has no editable snapshot data.', true);
        return;
      }
      localStorage.setItem('store-list-interface-v1', JSON.stringify(snapshot));
      window.open('store_list.php', '_blank');
    };

    // ── Delete record ────────────────────────────────────────────
    const deleteRecord = async id => {
      if (!isAdminUser) { showToast('Only admins can delete records.', true); return; }
      if (!confirm('Are you sure you want to delete this record?')) return;
      try {
        await DB.delete(COLLECTION, id);
        allRecords = allRecords.filter(r => r.id !== id);
        filteredRecords = filteredRecords.filter(r => r.id !== id);
        renderTable();
        showToast('Record deleted', false);
      } catch (err) {
        showToast('Delete failed: ' + err.message, true);
      }
    };

    // ── Init ─────────────────────────────────────────────────────
    loadRecords();
  </script>
</body>
</html>
