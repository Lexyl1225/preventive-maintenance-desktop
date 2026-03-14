<?php
$defaultAreas = [
	'NORTH 1',
	'NORTH 2',
	'NORTH 3',
	'NORTH 4',
	'NORTH 5',
	'NORTH 6',
	'CENTRAL 1',
	'CENTRAL 2',
	'CENTRAL 3',
	'SOUTH 1',
	'SOUTH 2',
	'SOUTH 3',
	'SOUTH 4',
	'SOUTH 5',
	'SOUTH 6',
	'VISAYAS 1',
	'VISAYAS 2',
	'MINDANAO 1',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Store List</title>
	<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
	<script src="firebase-config.js"></script>
	<script src="auth-guard.js"></script>
	<script src="db.js"></script>
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
		* { box-sizing: border-box; }

		body {
			margin: 0;
			font-family: var(--font);
			background: var(--page-bg);
			color: var(--text);
			line-height: 1.5;
			-webkit-font-smoothing: antialiased;
		}

		/* ── Page Layout ───────────────────────────────────────────── */
		.page {
			width: min(1800px, calc(100vw - 32px));
			margin: 20px auto 60px;
		}

		/* hidden on screen, shown in print */
		.print-doc-header { display: none; }

		/* ── Hero / Page Header ────────────────────────────────────── */
		.hero {
			display: flex;
			flex-wrap: wrap;
			gap: 16px;
			align-items: flex-end;
			justify-content: space-between;
			background: linear-gradient(135deg, #0f766e 0%, #134e4a 50%, #1e293b 100%);
			color: #fff;
			border-radius: var(--radius-xl);
			padding: 28px 32px;
			box-shadow: var(--shadow-lg);
			position: relative;
			overflow: hidden;
		}
		.hero::before {
			content: '';
			position: absolute;
			inset: 0;
			background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			pointer-events: none;
		}
		.hero h1 {
			margin: 0;
			font-size: clamp(1.6rem, 2.5vw, 2.2rem);
			font-weight: 800;
			letter-spacing: -0.02em;
		}
		.hero p {
			margin: 6px 0 0;
			max-width: 760px;
			color: rgba(255,255,255,0.75);
			font-size: 0.9rem;
		}
		.hero .date-display {
			text-align: right;
			font-size: 0.82rem;
			color: rgba(255,255,255,0.65);
		}
		.hero .date-display strong {
			display: block;
			font-size: 1.15rem;
			color: #fff;
			margin-top: 2px;
		}

		/* ── Toolbar ───────────────────────────────────────────────── */
		.toolbar {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			align-items: center;
			margin-top: 16px;
			padding: 16px 20px;
			background: var(--panel-bg);
			border: 1px solid var(--line);
			border-radius: var(--radius-lg);
			box-shadow: var(--shadow-sm);
		}
		.toolbar input {
			min-width: 240px;
			padding: 9px 14px;
			border: 1.5px solid var(--line-strong);
			border-radius: var(--radius-sm);
			font: inherit;
			font-size: 0.875rem;
			background: var(--panel-alt);
			color: var(--text);
			transition: border-color var(--transition), box-shadow var(--transition);
		}
		.toolbar input:focus {
			outline: none;
			border-color: var(--accent);
			box-shadow: 0 0 0 3px rgba(15,118,110,0.12);
			background: #fff;
		}
		.toolbar input::placeholder { color: var(--muted); }

		/* ── Buttons ───────────────────────────────────────────────── */
		.btn {
			border: 0;
			border-radius: var(--radius-sm);
			padding: 9px 18px;
			font: inherit;
			font-size: 0.8125rem;
			font-weight: 600;
			cursor: pointer;
			display: inline-flex;
			align-items: center;
			gap: 6px;
			transition: all var(--transition);
			white-space: nowrap;
			line-height: 1.4;
		}
		.btn:hover {
			transform: translateY(-1px);
			box-shadow: var(--shadow-md);
		}
		.btn:active {
			transform: translateY(0);
		}
		.btn-primary {
			background: var(--accent);
			color: #fff;
		}
		.btn-primary:hover { background: var(--accent-hover); }
		.btn-secondary {
			background: #e2e8f0;
			color: var(--text);
		}
		.btn-secondary:hover { background: #cbd5e1; }
		.btn-danger {
			background: var(--danger-soft);
			color: var(--danger);
			border: 1px solid #fecaca;
		}
		.btn-danger:hover { background: #fee2e2; }
		.btn-ghost {
			background: transparent;
			color: var(--accent);
			border: 1.5px solid #99f6e4;
		}
		.btn-ghost:hover { background: var(--accent-xsoft); border-color: var(--accent); }
		.btn-icon {
			padding: 7px 10px;
			font-size: 0.75rem;
		}
		.toolbar-separator {
			width: 1px;
			height: 28px;
			background: var(--line);
			margin: 0 4px;
		}

		/* ── Summary Cards Grid ────────────────────────────────────── */
		.summary-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 14px;
			margin: 16px 0 20px;
		}
		.summary-card {
			background: var(--panel-bg);
			border-radius: var(--radius-lg);
			padding: 20px;
			box-shadow: var(--shadow-sm);
			border: 1px solid var(--line);
			transition: box-shadow var(--transition);
		}
		.summary-card:hover { box-shadow: var(--shadow-md); }
		.summary-card h2 {
			margin: 0 0 14px;
			font-size: 0.8rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: var(--text-secondary);
		}
		.metric {
			display: flex;
			justify-content: space-between;
			align-items: center;
			padding: 10px 0;
			border-bottom: 1px solid var(--line);
			font-size: 0.875rem;
			color: var(--text-secondary);
		}
		.metric:last-child { border-bottom: 0; padding-bottom: 0; }
		.metric strong {
			font-size: 1.35rem;
			color: var(--accent);
			font-weight: 700;
		}

		/* ── Area Cards ────────────────────────────────────────────── */
		.areas { display: grid; gap: 16px; }
		.area-card {
			background: var(--panel-bg);
			border-radius: var(--radius-lg);
			overflow: hidden;
			box-shadow: var(--shadow-sm);
			border: 1px solid var(--line);
			transition: box-shadow var(--transition);
		}
		.area-card:hover { box-shadow: var(--shadow-md); }
		.area-header {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			justify-content: space-between;
			align-items: center;
			padding: 14px 20px;
			border-bottom: 1px solid var(--line);
			background: var(--panel-alt);
		}
		.area-title {
			display: flex;
			gap: 10px;
			align-items: center;
		}
		.area-pill {
			padding: 4px 10px;
			border-radius: 999px;
			background: var(--accent-soft);
			color: var(--accent);
			font-size: 0.7rem;
			font-weight: 700;
			letter-spacing: 0.05em;
			text-transform: uppercase;
		}
		.area-title h3 {
			margin: 0;
			font-size: 0.95rem;
			font-weight: 700;
			letter-spacing: 0.04em;
			color: var(--text);
		}
		.area-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
		}

		/* ── Data Table ────────────────────────────────────────────── */
		.table-wrap { overflow-x: auto; }
		table {
			width: 100%;
			min-width: 1560px;
			border-collapse: collapse;
			background: #fff;
		}
		th, td {
			border: 1px solid var(--line);
			padding: 5px 6px;
			text-align: center;
			vertical-align: middle;
			font-size: 0.76rem;
			word-break: break-word;
			white-space: normal;
		}
		th {
			background: var(--panel-alt);
			font-size: 0.68rem;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			white-space: nowrap;
			color: var(--text-secondary);
			border-color: var(--line-strong);
			position: sticky;
			top: 0;
			z-index: 2;
		}
		tbody tr { transition: background var(--transition); }
		tbody tr:nth-child(even) { background: var(--panel-alt); }
		tbody tr:hover { background: var(--accent-xsoft); }
		td.label { text-align: left; }

		/* ── Cell Inputs ───────────────────────────────────────────── */
		input.cell, select.cell {
			width: 100%;
			min-width: 0;
			border: 0;
			background: transparent;
			padding: 4px;
			font: inherit;
			text-align: inherit;
			box-sizing: border-box;
			color: var(--text);
			transition: background var(--transition);
		}
		input.cell:focus, select.cell:focus, .toolbar input:focus {
			outline: 2px solid rgba(15,118,110,0.18);
			outline-offset: -1px;
			background: var(--accent-xsoft);
			border-radius: 4px;
		}
		textarea.cell {
			resize: none;
			overflow: hidden;
			white-space: pre-wrap;
			word-break: break-word;
			height: auto;
			line-height: 1.4;
			vertical-align: top;
			text-align: center;
			border: 0;
			background: transparent;
			outline: none;
			padding: 4px;
			box-sizing: border-box;
			color: var(--text);
		}
		textarea.cell:focus {
			outline: 2px solid rgba(15,118,110,0.18);
			background: var(--accent-xsoft);
			border-radius: 4px;
		}
		input[readonly].cell {
			background: #eff6ff;
			color: #1d4ed8;
			cursor: default;
			font-weight: 600;
			text-align: center;
			border-radius: 4px;
		}

		/* ── Column Widths ─────────────────────────────────────────── */
		.wide { min-width: 180px; }
		.medium { min-width: 120px; }
		.narrow { min-width: 70px; }
		.branch-name-col {
			width: 200px; min-width: 160px;
			vertical-align: top; padding: 4px; text-align: left;
		}
		.brand-col {
			width: 44px; min-width: 44px; max-width: 52px;
			overflow: hidden; word-break: break-word; white-space: normal;
		}
		.brand-col input.cell {
			width: 100%; max-width: 100%;
			text-align: center; padding: 2px 2px; overflow: hidden;
		}
		.wide-col {
			width: 200px; min-width: 160px;
			overflow: visible; white-space: normal;
			word-break: break-word; vertical-align: top; padding: 4px;
		}
		.wide-col .dropdown-cell { min-width: 0; width: 100%; }
		.row-actions { min-width: 90px; }
		.sqm-col {
			width: 70px; min-width: 70px; max-width: 70px;
			overflow: hidden; word-break: break-word; white-space: normal;
		}
		.sqm-col input.cell {
			width: 100%; max-width: 100%;
			text-align: center; padding: 2px 2px; overflow: hidden;
		}

		/* ── Dropdown Cell ──────────────────────────────────────────── */
		.dropdown-cell {
			position: relative;
			display: flex;
			flex-direction: column;
			justify-content: center;
			min-width: 0;
			width: 100%;
			min-height: 26px;
		}
		.wide-col .dropdown-cell:not(.is-custom) select.cell {
			position: absolute; top: 0; left: 0;
			width: 100%; height: 100%;
			opacity: 0; cursor: pointer; z-index: 2;
			min-width: 0; padding: 0;
		}
		.wide-col .dropdown-cell.is-custom select.cell {
			position: static; width: 100%; height: auto;
			opacity: 1; font-size: 0.7rem; padding: 2px;
			color: var(--text-secondary);
		}
		.dropdown-cell:not(.is-custom)::after {
			content: '';
			position: absolute; right: 6px; top: 50%;
			transform: translateY(-50%);
			width: 0; height: 0;
			border-left: 4px solid transparent;
			border-right: 4px solid transparent;
			border-top: 5px solid var(--muted);
			pointer-events: none; z-index: 3;
		}
		.dropdown-custom-input {
			position: relative; z-index: 3;
			width: 100%;
			border: 1px dashed var(--line-strong) !important;
			border-radius: 4px;
			padding: 3px 5px;
			font-size: 0.72rem;
		}
		.value-display {
			display: block;
			font-size: 0.72rem; line-height: 1.4;
			white-space: normal; word-break: break-word; overflow-wrap: break-word;
			padding: 3px 16px 3px 4px;
			color: var(--text);
			min-height: 20px;
			position: relative; z-index: 1; pointer-events: none;
		}
		.value-display[data-empty="true"] { color: var(--muted); }
		.value-display[data-empty="true"]::before { content: '-- Select --'; }

		/* ── Empty State ───────────────────────────────────────────── */
		.empty-state {
			padding: 18px;
			color: var(--muted);
			text-align: center;
			background: var(--panel-alt);
			border-top: 1px solid var(--line);
		}

		/* ── Footer Summary ────────────────────────────────────────── */
		.footer-summary {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
			gap: 16px;
			margin-top: 20px;
		}
		.summary-table { width: 100%; min-width: 0; }
		.summary-table td, .summary-table th {
			font-size: 0.82rem;
			padding: 8px 12px;
		}

		/* ── Save Preview Modal ─────────────────────────────────────── */
		.save-overlay {
			display: none;
			position: fixed; inset: 0; z-index: 9000;
			background: rgba(15,23,42,0.5);
			backdrop-filter: blur(6px);
			justify-content: center; align-items: center;
		}
		.save-overlay.active { display: flex; }
		.save-modal {
			background: var(--panel-bg);
			border-radius: var(--radius-xl);
			width: min(94vw, 900px);
			max-height: 88vh;
			display: flex; flex-direction: column;
			box-shadow: 0 24px 60px rgba(0,0,0,0.2);
			animation: modalIn 0.25s ease-out;
		}
		@keyframes modalIn {
			from { opacity: 0; transform: translateY(16px) scale(0.97); }
			to { opacity: 1; transform: translateY(0) scale(1); }
		}
		.save-modal-header {
			display: flex; align-items: center; justify-content: space-between;
			padding: 20px 24px 16px;
			border-bottom: 1px solid var(--line);
		}
		.save-modal-header h2 { margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--text); }
		.save-modal-close {
			background: none; border: none; font-size: 1.5rem; cursor: pointer;
			color: var(--muted); line-height: 1; padding: 4px 8px;
			border-radius: var(--radius-sm);
			transition: all var(--transition);
		}
		.save-modal-close:hover { color: var(--text); background: var(--panel-alt); }
		.save-modal-body {
			padding: 20px 24px;
			overflow-y: auto; flex: 1;
		}
		.save-filename-group {
			display: flex; gap: 10px; align-items: center; margin-bottom: 18px;
		}
		.save-filename-group label { font-weight: 600; white-space: nowrap; color: var(--text-secondary); }
		.save-filename-group input {
			flex: 1; padding: 10px 14px;
			border: 1.5px solid var(--line-strong);
			border-radius: var(--radius-sm);
			font-size: 0.9rem; font-family: inherit;
			transition: border-color var(--transition);
		}
		.save-filename-group input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(15,118,110,0.12); }
		.preview-stats {
			display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
			gap: 10px; margin-bottom: 18px;
		}
		.preview-stat {
			background: var(--panel-alt); border-radius: var(--radius-md);
			padding: 12px 14px; text-align: center;
			border: 1px solid var(--line);
		}
		.preview-stat .label { font-size: 0.7rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
		.preview-stat .value { font-size: 1.35rem; font-weight: 700; color: var(--accent); margin-top: 2px; }
		.preview-areas-list { max-height: 300px; overflow-y: auto; }
		.preview-area-block { margin-bottom: 12px; }
		.preview-area-block h4 {
			margin: 0 0 6px; font-size: 0.82rem; color: var(--text-secondary);
			padding: 6px 12px; background: var(--panel-alt); border-radius: var(--radius-sm);
			font-weight: 700; border: 1px solid var(--line);
		}
		.preview-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
		.preview-table th {
			background: var(--line); padding: 6px 8px; text-align: left;
			font-weight: 600; color: var(--text-secondary); position: sticky; top: 0;
			font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em;
		}
		.preview-table td { padding: 5px 8px; border-bottom: 1px solid var(--line); }
		.preview-table tr:hover td { background: var(--panel-alt); }
		.save-modal-footer {
			display: flex; justify-content: flex-end; gap: 10px;
			padding: 16px 24px; border-top: 1px solid var(--line);
		}
		.save-modal-footer .btn { min-width: 110px; }
		.save-toast {
			position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%);
			background: var(--accent); color: #fff;
			padding: 12px 24px; border-radius: var(--radius-md);
			font-weight: 600; font-size: 0.875rem;
			box-shadow: var(--shadow-lg); z-index: 9999;
			opacity: 0; transition: opacity 0.3s, transform 0.3s;
			pointer-events: none;
		}
		.save-toast.show { opacity: 1; transform: translateX(-50%) translateY(-4px); }

		/* ── Animation ─────────────────────────────────────────────── */
		@keyframes reveal {
			from { opacity: 0; transform: translateY(10px); }
			to { opacity: 1; transform: translateY(0); }
		}

		/* ── Responsive ────────────────────────────────────────────── */
		@media (max-width: 900px) {
			.page { width: min(100vw - 16px, 1800px); margin-top: 12px; }
			.hero, .toolbar, .summary-card, .area-card { border-radius: var(--radius-md); }
			.hero { padding: 20px 22px; }
			.hero h1 { font-size: 1.3rem; }
			.toolbar { padding: 12px 14px; gap: 8px; }
			.toolbar input { min-width: 180px; }
		}

		/* ── Print Styles ──────────────────────────────────────────── */
		@media print {
			* { box-sizing: border-box; }
			body {
				background: #fff !important;
				color: #1e293b;
				font-family: "Segoe UI", Arial, sans-serif;
				font-size: 8pt;
				margin: 0;
				line-height: 1.35;
			}

			/* hide screen-only elements */
			.toolbar, .area-actions, .row-actions,
			.hero, .summary-grid, .no-print {
				display: none !important;
			}
			.page { width: 100%; margin: 0; padding: 0; }

			/* ── Print Document Header ── */
			.print-doc-header {
				display: flex !important;
				align-items: flex-end;
				justify-content: space-between;
				padding-bottom: 7pt;
				margin-bottom: 12pt;
				border-bottom: 2pt solid #0f766e;
			}
			.print-logo-line {
				font-size: 17pt;
				font-weight: 800;
				color: #0f766e;
				letter-spacing: 0.03em;
				line-height: 1.1;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
			.print-subtitle {
				font-size: 7.5pt;
				color: #475569;
				margin-top: 2pt;
				font-weight: 400;
			}
			.print-meta-right {
				text-align: right;
				font-size: 7.5pt;
				color: #475569;
				line-height: 1.7;
			}
			.print-meta-right strong { color: #1e293b; }

			/* ── Print Area Cards ── */
			.areas { display: block; }
			.area-card {
				border: 0.5pt solid #cbd5e1;
				border-radius: 0;
				box-shadow: none;
				margin-bottom: 14pt;
				page-break-inside: avoid;
				break-inside: avoid;
				overflow: visible;
			}
			.area-header {
				padding: 4pt 10pt;
				background: #1e293b !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
				border-bottom: 2pt solid #0f766e !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
			.area-title { display: flex; align-items: center; gap: 6pt; }
			.area-title h3 {
				font-size: 8pt;
				color: #fff !important;
				font-weight: 700;
				margin: 0;
				letter-spacing: 0.06em;
				text-transform: uppercase;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
			.area-pill {
				background: rgba(255,255,255,0.18) !important;
				color: #fff !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
				font-size: 5.5pt;
				padding: 1.5pt 5pt;
				border-radius: 2pt;
				font-weight: 700;
				letter-spacing: 0.05em;
				text-transform: uppercase;
			}

			/* ── Print Table ── */
			.table-wrap { overflow: visible; }
			table {
				width: 100%;
				min-width: 0;
				border-collapse: collapse;
				font-size: 7pt;
				table-layout: fixed;
			}
			th, td {
				border: 0.5pt solid #c7d4e0;
				padding: 3pt 3.5pt;
				vertical-align: middle;
				word-break: break-word;
				white-space: normal;
				text-align: center;
			}
			th {
				background: #0f766e !important;
				color: #fff !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
				font-size: 6pt;
				font-weight: 700;
				letter-spacing: 0.04em;
				text-transform: uppercase;
				border-color: rgba(255,255,255,0.25) !important;
				padding: 4pt 3pt;
			}
			tbody tr:nth-child(even) td {
				background: #f0fdf9 !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
			tbody tr:last-child td { border-bottom: 0.5pt solid #c7d4e0 !important; }

			/*
			 * ── Print Column Widths (percentage-based, 17 visible cols) ──
			 *  1:Main  2:Sat  3:Redemption  4:PL  5:BranchCode  6:BranchName
			 *  7:SQM  8:TradeName  9:TW  10:FH  11:GOTCHA  12:AUSTIN
			 *  13:ADAM'S  14:JACK'S  15:Company  16:LastPMDate  17:ConductedBy
			 */
			table thead th:nth-child(1)  { width: 4%; }
			table thead th:nth-child(2)  { width: 4%; }
			table thead th:nth-child(3)  { width: 4.5%; }
			table thead th:nth-child(4)  { width: 4.5%; }
			table thead th:nth-child(5)  { width: 5%; }
			table thead th:nth-child(6)  { width: 14%; text-align: left; }
			table thead th:nth-child(7)  { width: 4%; }
			table thead th:nth-child(8)  { width: 11%; }
			table thead th:nth-child(9)  { width: 3.5%; }
			table thead th:nth-child(10) { width: 3.5%; }
			table thead th:nth-child(11) { width: 3.5%; }
			table thead th:nth-child(12) { width: 3.5%; }
			table thead th:nth-child(13) { width: 3.5%; }
			table thead th:nth-child(14) { width: 3.5%; }
			table thead th:nth-child(15) { width: 10%; }
			table thead th:nth-child(16) { width: 7%; }
			table thead th:nth-child(17) { width: 10%; }

			/* ── Print Inputs Hidden ── */
			input.cell, textarea.cell, select.cell,
			.dropdown-cell select, .dropdown-cell .value-display,
			.dropdown-cell .dropdown-custom-input, .dropdown-cell::after {
				display: none !important;
			}
			.print-val {
				display: block;
				word-break: break-word;
				white-space: pre-wrap;
				text-align: center;
				font-size: 7pt;
				line-height: 1.35;
				color: #1e293b;
			}
			.branch-name-col .print-val { text-align: left; }
			.branch-name-col td { text-align: left; }
			input[readonly].cell + .print-val { color: #0f766e; font-weight: 700; }
			.no-print-col { display: none !important; }

			/* ── Print Footer Summary ── */
			.footer-summary {
				display: grid !important;
				grid-template-columns: 1fr 1fr;
				gap: 14pt;
				margin-top: 16pt;
				page-break-before: auto;
			}
			.footer-summary .summary-card {
				border: 0.5pt solid #c7d4e0;
				border-radius: 0;
				box-shadow: none;
				overflow: hidden;
			}
			.footer-summary .summary-card h2 {
				font-size: 8pt;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: 0.05em;
				color: #fff !important;
				background: #1e293b !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
				margin: 0;
				padding: 5pt 10pt;
				border-bottom: 2pt solid #0f766e;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}
			.summary-table { width: 100%; border-collapse: collapse; }
			.summary-table td {
				font-size: 7.5pt;
				padding: 3.5pt 8pt;
				border-bottom: 0.5pt solid #e2e8f0;
				color: #1e293b;
			}
			.summary-table td strong {
				color: #0f766e;
				font-weight: 700;
				font-size: 8.5pt;
			}
			.summary-table tr:nth-child(even) td {
				background: #f0fdf9 !important;
				-webkit-print-color-adjust: exact;
				print-color-adjust: exact;
			}

			@page {
				size: A3 landscape;
				margin: 10mm 12mm;
			}
		}
	</style>
</head>
<body>
	<div class="page">

		<!-- Print-only document header — hidden on screen -->
		<div class="print-doc-header" aria-hidden="true">
			<div>
				<div class="print-logo-line">STORE LIST REPORT</div>
				<div class="print-subtitle">Branches Summary — grouped by area</div>
			</div>
			<div class="print-meta-right">
				<div><strong>Date Printed:</strong> <span id="printDate"></span></div>
				<div><strong>Page:</strong> <span class="print-page-num"></span></div>
			</div>
		</div>

		<section class="hero">
			<div>
				<h1>Branches Summary</h1>
				<p>Editable store list grouped by area, with automatic Main, Sat, Redemption, and trade-brand summary counts.</p>
			</div>
			<div>
				<div class="date-display">
					<span>Month view</span>
					<strong id="currentDate"></strong>
				</div>
			</div>
		</section>

		<section class="toolbar">
			<input id="newAreaName" type="text" placeholder="Add a new area name...">
			<button class="btn btn-primary" id="addAreaBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
				Add Area
			</button>
			<button class="btn btn-secondary" id="resetBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12a9 9 0 019-9 9.75 9.75 0 016.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 01-9 9 9.75 9.75 0 01-6.74-2.74L3 16"/><path d="M3 21v-5h5"/></svg>
				Reset
			</button>
			<div class="toolbar-separator"></div>
			<button class="btn btn-ghost" id="exportBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
				Export JSON
			</button>
			<input type="file" id="importJsonInput" accept=".json" style="display:none">
			<button class="btn btn-ghost" id="importJsonBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
				Upload JSON
			</button>
			<div class="toolbar-separator"></div>
			<button class="btn btn-primary" id="saveToRecordsBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
				Save to Records
			</button>
			<button class="btn btn-ghost" id="viewRecordsBtn" type="button" onclick="window.open('store_list_records.php','_blank')">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
				View Records
			</button>
			<button class="btn btn-secondary" id="printBtn" type="button">
				<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
				Print
			</button>
		</section>

		<section class="summary-grid" id="summaryGrid"></section>

		<section class="areas" id="areasContainer"></section>

		<section class="footer-summary">
			<div class="summary-card">
				<h2>Overall Store Summary</h2>
				<table class="summary-table">
					<tbody id="storeTotals"></tbody>
				</table>
			</div>
			<div class="summary-card">
				<h2>Summary of Trade Name</h2>
				<table class="summary-table">
					<tbody id="tradeTotals"></tbody>
				</table>
			</div>
		</section>
	</div>

	<!-- Save Preview Modal -->
	<div class="save-overlay" id="saveOverlay">
		<div class="save-modal">
			<div class="save-modal-header">
				<h2>Preview &amp; Save to Records</h2>
				<button class="save-modal-close" id="saveModalClose" type="button">&times;</button>
			</div>
			<div class="save-modal-body">
				<div class="save-filename-group">
					<label for="saveFileName">File Name:</label>
					<input type="text" id="saveFileName" placeholder="e.g. Store List - January 2025">
				</div>
				<div class="preview-stats" id="previewStats"></div>
				<div class="preview-areas-list" id="previewAreasList"></div>
			</div>
			<div class="save-modal-footer">
				<button class="btn btn-secondary" id="saveModalCancel" type="button">Cancel</button>
				<button class="btn btn-primary" id="saveModalConfirm" type="button">
					<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
					Save
				</button>
			</div>
		</div>
	</div>
	<div class="save-toast" id="saveToast"></div>

	<script>
		const defaultAreas = <?php echo json_encode($defaultAreas, JSON_UNESCAPED_SLASHES); ?>;
		const storageKey = 'store-list-interface-v1';
		const brandKeys = [
			{ key: 'tw', label: 'Tom\'s World' },
			{ key: 'fh', label: 'Funhouse' },
			{ key: 'gotcha', label: 'Gotcha' },
			{ key: 'austin', label: 'Austin' },
			{ key: 'adams', label: 'Adams' },
			{ key: 'jacks', label: 'Jack Adventure' },
		];

		const blankRow = () => ({
			id: crypto.randomUUID(),
			storeType: '',
			noRedemption: false,
			plCode: '',
			branchCode: '',
			branchName: '',
			sqm: '',
			tradeName: '',
			tw: '',
			fh: '',
			gotcha: '',
			austin: '',
			adams: '',
			jacks: '',
			company: '',
			lastPmDate: '',
			conductedBy: '',
		});

		const createDefaultState = () => ({
			areas: defaultAreas.map((name) => ({
				id: crypto.randomUUID(),
				name,
				locked: true,
				entries: Array.from({ length: 7 }, () => blankRow()),
			})),
		});

		const loadState = () => {
			const saved = localStorage.getItem(storageKey);
			if (!saved) {
				return createDefaultState();
			}

			try {
				const parsed = JSON.parse(saved);
				if (!parsed || !Array.isArray(parsed.areas)) {
					return createDefaultState();
				}
				return parsed;
			} catch (error) {
				return createDefaultState();
			}
		};

		let state = loadState();

		const persistState = () => {
			localStorage.setItem(storageKey, JSON.stringify(state));
		};

		const escapeHtml = (value) => String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');

		const computeSummary = () => {
			let redemptionCount = 0;
			let totalMainCount = 0;
			let totalSatCount = 0;

			const brandCounters = { tw: 0, fh: 0, gotcha: 0, austin: 0, adams: 0, jacks: 0 };

			const rowIndexMap = new Map();

			state.areas.forEach((area) => {
				let mainCount = 0;
				let satCount = 0;
				area.entries.forEach((entry) => {
					const countsRedemption = !entry.noRedemption;
					if (countsRedemption) { redemptionCount += 1; }

					let mainNumber = '';
					let satNumber = '';

					if (entry.storeType === 'main') {
						mainCount += 1;
						totalMainCount += 1;
						mainNumber = mainCount;
					}

					if (entry.storeType === 'sat') {
						satCount += 1;
						totalSatCount += 1;
						satNumber = satCount;
					}

					const mapping = tradeNameBrandMap[entry.tradeName];
					const brandNumbers = {};
					brandFields.forEach((f) => {
						const active = mapping ? !!mapping[f] : String(entry[f]).trim() !== '';
						if (active) {
							brandCounters[f] += 1;
							brandNumbers[f] = brandCounters[f];
						} else {
							brandNumbers[f] = '';
						}
					});

					rowIndexMap.set(entry.id, {
						mainNumber,
						satNumber,
						redemptionNumber: countsRedemption ? redemptionCount : '',
						brandNumbers,
					});
				});
			});

			return {
				redemptionCount,
				mainCount: totalMainCount,
				satCount: totalSatCount,
				tradeCounts: brandCounters,
				rowIndexMap,
			};
		};

		const conductedByOptions = [
			'Johny Austria / Joseph Cristal Jr',
			'Petemar Solano / Jerico Simon',
			'Ronil Jardio / Chris Neri',
			'Joseph Cristal Jr / Johny Austria',
			'Jerico Simon / Petemar Solano',
			'Chris Neri / Ronil Jardio',
			'Joseph Cristal Jr',
			'Jerico Simon',
			'Johny Austria',
			'Petemar Solano',
			'Ronil Jardio',
			'Chris Neri',
		];

		const companyOptions = [
			'Tom & Joy Inc',
			'Planet Tom Inc',
			'Joe Master Claw Inc',
			'Afentiko Philippines Corporation',
			"Tom's Universe Inc",
			'Jack and Adam Corporation',
			'JA Claw Creation Inc',
			'Joy Planet Inc',
			'Pambi Corporation',
			"Tom's World Philippines Corporation",
		];

		const tradeNameOptions = [
			"Tom's World",
			'Funhouse',
			'Gotcha',
			'Austin',
			'Jack Adventure',
			"Adam's",
			"Tom's World / Funhouse",
			"Tom's World / Gotcha",
			"Tom's World / Austin",
			"Tom's World / Jack Adventure",
			"Tom's World / Adam's",
			"Tom's World / Funhouse / Gotcha",
			"Tom's World / Funhouse / Austin",
			"Tom's World / Funhouse / Jack Adventure",
			"Tom's World / Funhouse / Adam's",
			"Funhouse / Austin",
		];

		const tradeNameBrandMap = {
			"Tom's World":                           { tw: 1 },
			'Funhouse':                              { fh: 1 },
			'Gotcha':                                { gotcha: 1 },
			'Austin':                                { austin: 1 },
			"Adam's":                                { adams: 1 },
			'Jack Adventure':                        { jacks: 1 },
			"Tom's World / Funhouse":                { tw: 1, fh: 1 },
			"Tom's World / Gotcha":                  { tw: 1, gotcha: 1 },
			"Tom's World / Austin":                  { tw: 1, austin: 1 },
			"Tom's World / Jack Adventure":          { tw: 1, jacks: 1 },
			"Tom's World / Adam's":                  { tw: 1, adams: 1 },
			"Tom's World / Funhouse / Gotcha":       { tw: 1, fh: 1, gotcha: 1 },
			"Tom's World / Funhouse / Austin":       { tw: 1, fh: 1, austin: 1 },
			"Tom's World / Funhouse / Jack Adventure": { tw: 1, fh: 1, jacks: 1 },
			"Tom's World / Funhouse / Adam's":       { tw: 1, fh: 1, adams: 1 },
			"Funhouse / Austin":                     { fh: 1, austin: 1 },
		};

		const brandFields = ['tw', 'fh', 'gotcha', 'austin', 'adams', 'jacks'];

		const applyTradeNameBrands = (entry, tradeName) => {
			const mapping = tradeNameBrandMap[tradeName];
			if (mapping) {
				brandFields.forEach((f) => { entry[f] = mapping[f] ? '1' : ''; });
			}
		};

		const dropdownCell = (areaIndex, entry, field, options) => {
			const currentVal = entry[field] || '';
			const isCustom = currentVal !== '' && !options.includes(currentVal);
			const selectVal = isCustom ? '__custom__' : currentVal;

			const optionsHtml = `<option value="">-- Select --</option>` +
				options.map((opt) =>
					`<option value="${escapeHtml(opt)}" ${selectVal === opt ? 'selected' : ''}>${escapeHtml(opt)}</option>`
				).join('') +
				`<option value="__custom__" ${isCustom ? 'selected' : ''}>Custom Name</option>`;

			const displayText = isCustom ? currentVal : (currentVal && currentVal !== '__custom__' ? currentVal : '');

			return `
				<div class="dropdown-cell${isCustom ? ' is-custom' : ''}">
					<select class="cell" data-dropdown-for-field="${field}" data-area-index="${areaIndex}" data-row-id="${entry.id}">
						${optionsHtml}
					</select>
					<span class="value-display" ${!displayText ? 'data-empty="true"' : ''}>${escapeHtml(displayText)}</span>
					<input class="cell dropdown-custom-input" type="text"
						data-field="${field}"
						data-area-index="${areaIndex}"
						data-row-id="${entry.id}"
						value="${escapeHtml(currentVal)}"
						placeholder="Type custom name..."
						style="display:${isCustom ? 'block' : 'none'}">
				</div>
			`;
		};

		const renderSummaryCards = (summary) => {
			const summaryGrid = document.getElementById('summaryGrid');
			summaryGrid.innerHTML = `
				<article class="summary-card">
					<h2>Counting Rules</h2>
					<div class="metric"><span>Stores with Redemption</span><strong>${summary.redemptionCount}</strong></div>
					<div class="metric"><span>Number of Sat</span><strong>${summary.satCount}</strong></div>
					<div class="metric"><span>Number of Main Area</span><strong>${summary.mainCount}</strong></div>
				</article>
				<article class="summary-card">
					<h2>Area Coverage</h2>
					<div class="metric"><span>Default Areas</span><strong>${defaultAreas.length}</strong></div>
					<div class="metric"><span>Current Areas</span><strong>${state.areas.length}</strong></div>
					<div class="metric"><span>Custom Areas</span><strong>${state.areas.filter((area) => !area.locked).length}</strong></div>
				</article>
			`;

			const storeTotals = document.getElementById('storeTotals');
			storeTotals.innerHTML = `
				<tr><td><strong>${summary.redemptionCount}</strong></td><td>STORES WITH REDEMPTION</td></tr>
				<tr><td><strong>${summary.satCount}</strong></td><td>NUMBER OF SAT</td></tr>
				<tr><td><strong>${summary.mainCount}</strong></td><td>NUMBER OF MAIN AREA</td></tr>
			`;

			const tradeTotals = document.getElementById('tradeTotals');
			tradeTotals.innerHTML = brandKeys.map(({ key, label }) => `
				<tr><td>${label}</td><td><strong>${summary.tradeCounts[key]}</strong></td></tr>
			`).join('');
		};

		const renderAreas = (summary) => {
			const container = document.getElementById('areasContainer');

			container.innerHTML = state.areas.map((area, areaIndex) => {
				const rowsMarkup = area.entries.map((entry) => {
					const rowNumbers = summary.rowIndexMap.get(entry.id) || {
						mainNumber: '',
						satNumber: '',
						redemptionNumber: '',
					};

					return `
						<tr data-row-id="${entry.id}">
							<td>${rowNumbers.mainNumber}</td>
							<td>${rowNumbers.satNumber}</td>
							<td>${rowNumbers.redemptionNumber}</td>
							<td><input class="cell narrow" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="plCode" value="${escapeHtml(entry.plCode)}"></td>
							<td><input class="cell narrow" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="branchCode" value="${escapeHtml(entry.branchCode)}"></td>
							<td class="branch-name-col"><textarea class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="branchName" rows="1">${escapeHtml(entry.branchName)}</textarea></td>
							<td class="sqm-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="sqm" value="${escapeHtml(entry.sqm)}"></td>
							<td class="wide-col">${dropdownCell(areaIndex, entry, 'tradeName', tradeNameOptions)}</td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="tw" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.tw || '') : escapeHtml(entry.tw)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="fh" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.fh || '') : escapeHtml(entry.fh)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="gotcha" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.gotcha || '') : escapeHtml(entry.gotcha)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="austin" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.austin || '') : escapeHtml(entry.austin)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="adams" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.adams || '') : escapeHtml(entry.adams)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
						<td class="brand-col"><input class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="jacks" value="${tradeNameBrandMap[entry.tradeName] ? (rowNumbers.brandNumbers.jacks || '') : escapeHtml(entry.jacks)}" ${tradeNameBrandMap[entry.tradeName] ? 'readonly title="Auto-filled by Trade Name"' : ''}></td>
							<td class="wide-col">${dropdownCell(areaIndex, entry, 'company', companyOptions)}</td>
							<td><input class="cell medium" type="date" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="lastPmDate" value="${escapeHtml(entry.lastPmDate)}"></td>
							<td class="wide-col">${dropdownCell(areaIndex, entry, 'conductedBy', conductedByOptions)}</td>
											<td class="no-print-col">
												<select class="cell" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="storeType">
													<option value="" ${entry.storeType === '' ? 'selected' : ''}>None</option>
									<option value="main" ${entry.storeType === 'main' ? 'selected' : ''}>Main</option>
									<option value="sat" ${entry.storeType === 'sat' ? 'selected' : ''}>Sat</option>
								</select>
							</td><td class="no-print-col" style="text-align:center;">
							<input type="checkbox" data-area-index="${areaIndex}" data-row-id="${entry.id}" data-field="noRedemption" ${entry.noRedemption ? 'checked' : ''} title="Skip redemption count for this store">
						</td>							<td class="no-print-col row-actions"><button class="btn btn-danger btn-icon delete-row" type="button" data-area-index="${areaIndex}" data-row-id="${entry.id}">
								<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
							</button></td>
						</tr>
					`;
				}).join('');

				return `
					<article class="area-card">
						<div class="area-header">
							<div class="area-title">
								<span class="area-pill">AREA</span>
								<h3>${escapeHtml(area.name)}</h3>
							</div>
							<div class="area-actions">
								<button class="btn btn-primary btn-icon add-row" type="button" data-area-index="${areaIndex}">
									<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
									Add Row
								</button>
								<button class="btn btn-danger btn-icon delete-area" type="button" data-area-index="${areaIndex}">
									<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
									Delete Area
								</button>
							</div>
						</div>
						<div class="table-wrap">
							<table>
								<thead>
									<tr>
										<th>Main</th>
										<th>Sat</th>
										<th>Redemption</th>
										<th>PL code</th>
										<th>Branch code</th>
										<th>Branch Name</th>
										<th class="sqm-col">SQM</th>
										<th>Trade Name</th>
										<th>TW</th>
										<th>FH</th>
										<th>GOTCHA</th>
										<th>AUSTIN</th>
										<th>ADAM'S</th>
										<th>JACK'S</th>
										<th>Company</th>
										<th>LAST PM DATE</th>
										<th>CONDUCTED BY</th>
										<th class="no-print-col">Count As</th>									<th class="no-print-col">No Redemption</th>										<th class="no-print-col">Action</th>
									</tr>
								</thead>
								<tbody>
									${rowsMarkup || `<tr><td class="empty-state" colspan="20">No store entries yet for ${escapeHtml(area.name)}.</td></tr>`}
								</tbody>
							</table>
						</div>
					</article>
				`;
			}).join('');
		};

		const renderSummaryOnly = () => {
			const summary = computeSummary();
			renderSummaryCards(summary);
		};

		const render = () => {
			const savedScroll = window.scrollY;
			const summary = computeSummary();
			renderSummaryCards(summary);
			renderAreas(summary);
			autoResizeTextareas();
			window.scrollTo(0, savedScroll);

			document.getElementById('currentDate').textContent = new Intl.DateTimeFormat('en-US', {
				month: 'long',
				year: 'numeric',
			}).format(new Date());
		};

		const autoResizeTextareas = () => {
			document.querySelectorAll('textarea.cell').forEach((ta) => {
				ta.style.height = 'auto';
				ta.style.height = ta.scrollHeight + 'px';
			});
		};

		const updateEntryField = (areaIndex, rowId, field, value) => {
			const area = state.areas[areaIndex];
			if (!area) {
				return;
			}

			const entry = area.entries.find((row) => row.id === rowId);
			if (!entry) {
				return;
			}

			entry[field] = value;
			persistState();
			render();
		};

		document.addEventListener('input', (event) => {
			const target = event.target;
			if (!(target instanceof HTMLElement) || !target.matches('input[data-row-id][data-field], textarea[data-row-id][data-field]')) {
				return;
			}
			if (brandFields.includes(target.dataset.field)) { return; }

			const area = state.areas[Number(target.dataset.areaIndex)];
			if (!area) { return; }
			const entry = area.entries.find((row) => row.id === target.dataset.rowId);
			if (!entry) { return; }

			if (target.dataset.field === 'tradeName') {
				entry.tradeName = target.value;
				applyTradeNameBrands(entry, entry.tradeName);
			} else {
				entry[target.dataset.field] = target.value;
			}

			const cell = target.closest('.dropdown-cell');
			if (cell) {
				const valueDisplay = cell.querySelector('.value-display');
				if (valueDisplay) {
					valueDisplay.textContent = target.value;
					if (target.value) { valueDisplay.removeAttribute('data-empty'); } else { valueDisplay.setAttribute('data-empty', 'true'); }
				}
			}

			if (target.tagName === 'TEXTAREA') {
				target.style.height = 'auto';
				target.style.height = target.scrollHeight + 'px';
			}

			persistState();
			renderSummaryOnly();
		});

		document.addEventListener('change', (event) => {
			const target = event.target;
			if (!(target instanceof HTMLElement)) { return; }

			if (target.matches('select[data-dropdown-for-field]')) {
				const field = target.dataset.dropdownForField;
				const area = state.areas[Number(target.dataset.areaIndex)];
				if (!area) { return; }
				const entry = area.entries.find((r) => r.id === target.dataset.rowId);
				if (!entry) { return; }

				const cell = target.closest('.dropdown-cell');
				const customInput = cell.querySelector('.dropdown-custom-input');
				const valueDisplay = cell.querySelector('.value-display');

				if (target.value === '__custom__') {
					if (customInput) { customInput.style.display = 'block'; customInput.focus(); }
					if (valueDisplay) { valueDisplay.textContent = ''; valueDisplay.setAttribute('data-empty', 'true'); }
					cell.classList.add('is-custom');
				} else {
					if (customInput) customInput.style.display = 'none';
					if (valueDisplay) { valueDisplay.textContent = target.value; if (target.value) { valueDisplay.removeAttribute('data-empty'); } else { valueDisplay.setAttribute('data-empty', 'true'); } }
					cell.classList.remove('is-custom');
					entry[field] = target.value;
					if (field === 'tradeName') { applyTradeNameBrands(entry, entry.tradeName); }
					persistState();
					render();
				}
				return;
			}

			if (!target.matches('[data-row-id][data-field]')) { return; }
			const value = target.type === 'checkbox' ? target.checked : target.value;
			updateEntryField(Number(target.dataset.areaIndex), target.dataset.rowId, target.dataset.field, value);
		});

		document.addEventListener('click', (event) => {
			const target = event.target;
			if (!(target instanceof HTMLElement)) {
				return;
			}

			const addBtn = target.closest('.add-row');
			if (addBtn) {
				const area = state.areas[Number(addBtn.dataset.areaIndex)];
				if (!area) {
					return;
				}

				area.entries.push(blankRow());
				persistState();
				render();
				return;
			}

			const delBtn = target.closest('.delete-row');
			if (delBtn) {
				const area = state.areas[Number(delBtn.dataset.areaIndex)];
				if (!area) {
					return;
				}

				area.entries = area.entries.filter((entry) => entry.id !== delBtn.dataset.rowId);
				persistState();
				render();
				return;
			}

			const delArea = target.closest('.delete-area');
			if (delArea) {
				state.areas.splice(Number(delArea.dataset.areaIndex), 1);
				persistState();
				render();
			}
		});

		document.getElementById('addAreaBtn').addEventListener('click', () => {
			const input = document.getElementById('newAreaName');
			const areaName = input.value.trim().toUpperCase();

			if (!areaName) {
				input.focus();
				return;
			}

			const exists = state.areas.some((area) => area.name.toUpperCase() === areaName);
			if (exists) {
				input.select();
				return;
			}

			state.areas.push({
				id: crypto.randomUUID(),
				name: areaName,
				locked: false,
				entries: [],
			});
			input.value = '';
			persistState();
			render();
		});

		document.getElementById('newAreaName').addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				document.getElementById('addAreaBtn').click();
			}
		});

		document.getElementById('resetBtn').addEventListener('click', () => {
			if (!confirm('Reset all areas to default? This will clear all current data.')) return;
			state = createDefaultState();
			persistState();
			render();
		});

		document.getElementById('printBtn').addEventListener('click', () => {
			// Stamp current date/time into print header
			const now = new Date();
			const dateStr = now.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
				+ '  ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
			const pd = document.getElementById('printDate');
			if (pd) pd.textContent = dateStr;

			const cleanup = [];
			const restored = [];

			document.querySelectorAll('td input.cell, td textarea.cell').forEach((el) => {
				if (el.closest('.dropdown-cell')) return; // handled by dropdown loop below
				const span = document.createElement('span');
				span.className = 'print-val';
				span.textContent = el.value || '';
				if (el.readOnly) { span.style.color = '#0369a1'; span.style.fontWeight = '600'; }
				el.parentNode.insertBefore(span, el.nextSibling);
				cleanup.push(span);
			});

			document.querySelectorAll('td .dropdown-cell').forEach((cell) => {
				const vd = cell.querySelector('.value-display');
				const customInput = cell.querySelector('.dropdown-custom-input');

				const text = (customInput && customInput.style.display !== 'none')
					? customInput.value
					: (vd && !vd.hasAttribute('data-empty') ? vd.textContent.trim() : '');

				[vd, customInput].forEach((node) => {
					if (node && node.parentNode) {
						restored.push({ node, parent: node.parentNode, next: node.nextSibling });
						node.parentNode.removeChild(node);
					}
				});

				const span = document.createElement('span');
				span.className = 'print-val';
				span.textContent = text;
				cell.appendChild(span);
				cleanup.push(span);
			});

			window.print();

			cleanup.forEach((el) => el.remove());
			// Restore in reverse order so each reference node is already back in the
			// parent DOM before it is used as the insertBefore anchor.
			restored.slice().reverse().forEach(({ node, parent, next }) => {
				if (next && next.parentNode === parent) {
					parent.insertBefore(node, next);
				} else {
					parent.appendChild(node);
				}
			});
		});

		document.getElementById('exportBtn').addEventListener('click', () => {
			const blob = new Blob([JSON.stringify(state, null, 2)], { type: 'application/json' });
			const link = document.createElement('a');
			link.href = URL.createObjectURL(blob);
			link.download = 'store-list-snapshot.json';
			link.click();
			URL.revokeObjectURL(link.href);
		});

		document.getElementById('importJsonBtn').addEventListener('click', () => {
			document.getElementById('importJsonInput').click();
		});

		document.getElementById('importJsonInput').addEventListener('change', (event) => {
			const file = event.target.files[0];
			if (!file) return;
			const reader = new FileReader();
			reader.onload = (e) => {
				try {
					const parsed = JSON.parse(e.target.result);
					if (!parsed || !Array.isArray(parsed.areas)) {
						showSaveToast('Invalid JSON: missing areas array.', true);
						return;
					}
					state = parsed;
					persistState();
					render();
					showSaveToast('JSON imported successfully!', false);
				} catch (err) {
					showSaveToast('Failed to parse JSON: ' + err.message, true);
				}
			};
			reader.readAsText(file);
			event.target.value = '';
		});

		const STORE_LIST_COLLECTION = 'epm_store_list_v1';
		const saveOverlay = document.getElementById('saveOverlay');
		const saveFileName = document.getElementById('saveFileName');
		const previewStats = document.getElementById('previewStats');
		const previewAreasList = document.getElementById('previewAreasList');
		const saveToast = document.getElementById('saveToast');

		const showSaveToast = (msg, isError) => {
			saveToast.textContent = msg;
			saveToast.style.background = isError ? '#dc2626' : 'var(--accent)';
			saveToast.classList.add('show');
			setTimeout(() => saveToast.classList.remove('show'), 3000);
		};

		const openSavePreview = () => {
			const summary = computeSummary();

			const now = new Date();
			const monthYear = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(now);
			saveFileName.value = 'Store List - ' + monthYear;

			const totalEntries = state.areas.reduce((sum, a) => sum + a.entries.filter(e => e.branchName.trim() || e.plCode.trim() || e.branchCode.trim()).length, 0);
			previewStats.innerHTML = `
				<div class="preview-stat"><div class="label">Areas</div><div class="value">${state.areas.length}</div></div>
				<div class="preview-stat"><div class="label">Main</div><div class="value">${summary.mainCount}</div></div>
				<div class="preview-stat"><div class="label">Sat</div><div class="value">${summary.satCount}</div></div>
				<div class="preview-stat"><div class="label">Redemption</div><div class="value">${summary.redemptionCount}</div></div>
				<div class="preview-stat"><div class="label">Entries</div><div class="value">${totalEntries}</div></div>
			`;

			previewAreasList.innerHTML = state.areas.map(area => {
				const filled = area.entries.filter(e => e.branchName.trim() || e.plCode.trim() || e.branchCode.trim());
				if (filled.length === 0) return '';
				const rows = filled.map(e => `<tr>
					<td>${escapeHtml(e.plCode)}</td>
					<td>${escapeHtml(e.branchCode)}</td>
					<td>${escapeHtml(e.branchName)}</td>
					<td>${escapeHtml(e.tradeName)}</td>
					<td>${escapeHtml(e.company)}</td>
					<td>${escapeHtml(e.lastPmDate)}</td>
					<td>${escapeHtml(e.conductedBy)}</td>
				</tr>`).join('');
				return `<div class="preview-area-block">
					<h4>${escapeHtml(area.name)} (${filled.length} entries)</h4>
					<table class="preview-table">
						<thead><tr><th>PL</th><th>Code</th><th>Branch</th><th>Trade Name</th><th>Company</th><th>Last PM</th><th>Conducted By</th></tr></thead>
						<tbody>${rows}</tbody>
					</table>
				</div>`;
			}).join('');

			saveOverlay.classList.add('active');
			saveFileName.focus();
			saveFileName.select();
		};

		const closeSavePreview = () => {
			saveOverlay.classList.remove('active');
		};

		const confirmSave = async () => {
			const name = saveFileName.value.trim();
			if (!name) {
				saveFileName.focus();
				return;
			}

			const confirmBtn = document.getElementById('saveModalConfirm');
			confirmBtn.disabled = true;
			confirmBtn.textContent = 'Saving...';

			try {
				const summary = computeSummary();
				const payload = {
					fileName: name,
					snapshot: state,
					totalMain: summary.mainCount,
					totalSat: summary.satCount,
					totalRedemption: summary.redemptionCount,
					totalAreas: state.areas.length,
					savedAt: new Date().toISOString(),
				};
				await DB.create(STORE_LIST_COLLECTION, payload);
				closeSavePreview();
				showSaveToast('Saved successfully!', false);
			} catch (err) {
				showSaveToast('Save failed: ' + err.message, true);
			} finally {
				confirmBtn.disabled = false;
				confirmBtn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Save';
			}
		};

		document.getElementById('saveToRecordsBtn').addEventListener('click', openSavePreview);
		document.getElementById('saveModalClose').addEventListener('click', closeSavePreview);
		document.getElementById('saveModalCancel').addEventListener('click', closeSavePreview);
		document.getElementById('saveModalConfirm').addEventListener('click', confirmSave);
		saveOverlay.addEventListener('click', (e) => { if (e.target === saveOverlay) closeSavePreview(); });

		render();
	</script>
</body>
</html>
