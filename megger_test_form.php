<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="icon" type="image/x-icon" href="images/favicon.ico">
  <title>Megger / Insulation Resistance Test</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
	/* ===== DARK THEME DESIGN SYSTEM ===== */
	:root {
	  --bg-base:            #0d0f14;
	  --bg-card:            #161a23;
	  --bg-card-alt:        #1c2030;
	  --bg-input:           #1e2235;
	  --bg-input-hover:     #242840;
	  --bg-disabled:        #161924;
	  --border:             rgba(255,255,255,0.09);
	  --border-strong:      rgba(255,255,255,0.17);
	  --border-focus:       #4f8ef7;
	  --text-primary:       #e6e9f0;
	  --text-secondary:     #8a93ab;
	  --text-muted:         #515a70;
	  --accent:             #4f8ef7;
	  --accent-hover:       #7aaeff;
	  --accent-dim:         rgba(79,142,247,0.14);
	  --btn-secondary-bg:   rgba(255,255,255,0.06);
	  --btn-secondary-border: rgba(255,255,255,0.11);
	  --table-header:       #181d2c;
	  --table-row-alt:      rgba(255,255,255,0.022);
	  --shadow-card:        0 4px 28px rgba(0,0,0,0.55);
	  --shadow-btn:         0 2px 8px rgba(0,0,0,0.4);
	  --transition:         0.14s ease;
	  --radius-sm:          6px;
	  --radius-md:          10px;
	  --radius-lg:          14px;
	}

	/* ===== RESET & BASE ===== */
	*, *::before, *::after { box-sizing: border-box; }
	body {
	  font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif;
	  margin: 0;
	  min-height: 100vh;
	  color: var(--text-primary);
	  background: var(--bg-base) url('images/toms_background.png') no-repeat center center fixed;
	  background-size: cover;
	  background-blend-mode: overlay;
	  -webkit-font-smoothing: antialiased;
	}

	/* ===== FLOATING NAV BUTTONS ===== */
	.back-home-btn,
	.view-mtr-btn {
	  position: fixed;
	  right: 20px;
	  z-index: 1000;
	  border: 1px solid var(--border-strong);
	  padding: 9px 15px;
	  border-radius: var(--radius-md);
	  cursor: pointer;
	  font-family: inherit;
	  font-size: 12px;
	  font-weight: 600;
	  letter-spacing: 0.3px;
	  box-shadow: var(--shadow-btn);
	  transition: all var(--transition);
	  backdrop-filter: blur(10px);
	  -webkit-backdrop-filter: blur(10px);
	}
	.back-home-btn { top: 16px; background: rgba(79,142,247,0.16); color: #7aaeff; }
	.view-mtr-btn  { top: 54px; background: rgba(255,255,255,0.07); color: var(--text-primary); }
	.back-home-btn:hover { background: rgba(79,142,247,0.30); color: #fff; transform: translateY(-1px); }
	.view-mtr-btn:hover  { background: rgba(255,255,255,0.14); color: #fff; transform: translateY(-1px); }

	/* ===== MAIN CARD ===== */
	.page {
	  max-width: 1160px;
	  margin: 18px auto;
	  padding: 28px 30px 34px;
	  background: var(--bg-card);
	  border-radius: var(--radius-lg);
	  border: 1px solid var(--border);
	  box-shadow: var(--shadow-card);
	}

	/* ===== PAGE HEADING ===== */
	.page-heading {
	  display: flex;
	  align-items: center;
	  gap: 14px;
	  margin: 0 0 24px;
	  padding-bottom: 20px;
	  border-bottom: 1px solid var(--border);
	}
	.page-heading .heading-icon {
	  width: 40px; height: 40px;
	  background: var(--accent-dim);
	  border: 1px solid rgba(79,142,247,0.28);
	  border-radius: var(--radius-md);
	  display: flex; align-items: center; justify-content: center;
	  font-size: 20px; flex-shrink: 0;
	}
	.page-heading h2 {
	  margin: 0;
	  font-size: 17px;
	  font-weight: 700;
	  color: var(--text-primary);
	  letter-spacing: 0.6px;
	  text-transform: uppercase;
	  line-height: 1.3;
	}
	.page-heading .heading-badge {
	  margin-left: auto;
	  font-size: 10px;
	  font-weight: 700;
	  letter-spacing: 0.9px;
	  text-transform: uppercase;
	  color: var(--accent);
	  background: var(--accent-dim);
	  border: 1px solid rgba(79,142,247,0.24);
	  padding: 4px 11px;
	  border-radius: 20px;
	  flex-shrink: 0;
	}

	/* ===== META PANEL CARDS ===== */
	.header-grid { display: grid; grid-template-columns: 1fr 370px; gap: 16px; }
	.meta {
	  background: var(--bg-card-alt);
	  border: 1px solid var(--border);
	  border-radius: var(--radius-md);
	  padding: 16px 18px;
	}
	.meta-row {
	  display: flex;
	  align-items: center;
	  gap: 10px;
	}
	.meta-row + .meta-row { margin-top: 10px; }
	.meta label {
	  display: inline-block;
	  min-width: 116px;
	  font-size: 11px;
	  font-weight: 600;
	  color: var(--text-secondary);
	  letter-spacing: 0.3px;
	  text-transform: uppercase;
	  flex-shrink: 0;
	}

	/* ===== CIRCUIT SELECTOR STRIP ===== */
	.circuit-selector-row {
	  margin-top: 18px;
	  padding: 13px 18px;
	  background: var(--bg-card-alt);
	  border: 1px solid var(--border);
	  border-radius: var(--radius-md);
	  display: flex;
	  align-items: center;
	  gap: 16px;
	  flex-wrap: wrap;
	}
	.circuit-selector-row label {
	  font-size: 11px;
	  font-weight: 700;
	  color: var(--text-secondary);
	  letter-spacing: 0.5px;
	  text-transform: uppercase;
	  white-space: nowrap;
	  flex-shrink: 0;
	}

	/* ===== TABLE TOOLBAR ===== */
	.table-toolbar {
	  display: flex;
	  justify-content: space-between;
	  align-items: center;
	  margin-top: 20px;
	  margin-bottom: 10px;
	  flex-wrap: wrap;
	  gap: 10px;
	}
	.table-toolbar .table-title {
	  font-size: 11px;
	  font-weight: 700;
	  letter-spacing: 0.6px;
	  text-transform: uppercase;
	  color: var(--text-secondary);
	}
	.controls { display: flex; gap: 6px; flex-wrap: wrap; }

	/* ===== BUTTONS ===== */
	.btn {
	  display: inline-flex;
	  align-items: center;
	  gap: 4px;
	  font-family: inherit;
	  font-weight: 600;
	  border: 1px solid transparent;
	  border-radius: var(--radius-sm);
	  cursor: pointer;
	  transition: all var(--transition);
	  letter-spacing: 0.2px;
	  background: var(--accent);
	  color: #fff;
	  box-shadow: var(--shadow-btn);
	}
	.btn:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(79,142,247,0.38); }
	.btn:active { transform: translateY(0); box-shadow: var(--shadow-btn); }
	.btn.secondary {
	  background: var(--btn-secondary-bg);
	  color: var(--text-primary);
	  border-color: var(--btn-secondary-border);
	  box-shadow: none;
	}
	.btn.secondary:hover { background: rgba(255,255,255,0.12); border-color: var(--border-strong); transform: translateY(-1px); }
	.small { font-size: 12px; padding: 7px 12px; }

	/* ===== FORM INPUTS ===== */
	input[type=text],
	input[type=number],
	input[type=date],
	select,
	textarea {
	  width: 100%;
	  padding: 7px 10px;
	  font-family: inherit;
	  font-size: 13px;
	  color: var(--text-primary);
	  background: var(--bg-input);
	  border: 1px solid var(--border);
	  border-radius: var(--radius-sm);
	  transition: border-color var(--transition), background var(--transition), box-shadow var(--transition);
	  appearance: auto;
	  -webkit-appearance: auto;
	}
	input[type=text]:hover,
	input[type=number]:hover,
	input[type=date]:hover,
	select:hover {
	  background: var(--bg-input-hover);
	  border-color: var(--border-strong);
	}
	input[type=text]:focus,
	input[type=number]:focus,
	input[type=date]:focus,
	select:focus,
	textarea:focus {
	  outline: none;
	  border-color: var(--border-focus);
	  box-shadow: 0 0 0 3px rgba(79,142,247,0.17);
	  background: var(--bg-input-hover);
	}
	input[type=text]::placeholder,
	textarea::placeholder { color: var(--text-muted); }
	input:disabled {
	  background: var(--bg-disabled) !important;
	  border-color: rgba(255,255,255,0.04) !important;
	  color: var(--text-muted) !important;
	  cursor: not-allowed;
	  opacity: 0.45;
	}

	/* ===== DATA TABLE ===== */
	.table-scroll {
	  max-height: 440px;
	  overflow: auto;
	  border: 1px solid var(--border);
	  border-radius: var(--radius-md);
	  scrollbar-width: thin;
	  scrollbar-color: rgba(79,142,247,0.35) transparent;
	}
	.table-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
	.table-scroll::-webkit-scrollbar-track { background: transparent; }
	.table-scroll::-webkit-scrollbar-thumb { background: rgba(79,142,247,0.35); border-radius: 3px; }
	.panel-table { width: 100%; border-collapse: collapse; }
	.panel-table thead { position: sticky; top: 0; z-index: 2; }
	.panel-table th {
	  background: var(--table-header);
	  color: var(--text-secondary);
	  font-size: 10px;
	  font-weight: 700;
	  letter-spacing: 0.6px;
	  text-transform: uppercase;
	  padding: 10px 8px;
	  border-bottom: 2px solid rgba(79,142,247,0.28);
	  border-right: 1px solid var(--border);
	  white-space: nowrap;
	  text-align: center;
	}
	.panel-table td {
	  border-bottom: 1px solid var(--border);
	  border-right: 1px solid var(--border);
	  padding: 4px 5px;
	  font-size: 12px;
	  color: var(--text-primary);
	  vertical-align: middle;
	}
	.panel-table tbody tr:nth-child(even) td { background: var(--table-row-alt); }
	.panel-table tbody tr:hover td { background: rgba(79,142,247,0.055); }
	/* Compact table-cell inputs */
	.panel-table td input[type=text],
	.panel-table td input[type=number],
	.panel-table td select {
	  padding: 5px 6px;
	  font-size: 11px;
	  border-radius: 4px;
	  min-width: 62px;
	}

	/* ===== SIGNATURE SECTION ===== */
	.bottom-sign {
	  display: flex;
	  justify-content: space-between;
	  gap: 20px;
	  margin-top: 28px;
	  padding-top: 22px;
	  border-top: 1px solid var(--border);
	}
	.sign-block { width: 30%; }
	.sign-label {
	  font-size: 10px;
	  font-weight: 700;
	  letter-spacing: 0.7px;
	  text-transform: uppercase;
	  color: var(--text-secondary);
	  margin-bottom: 6px;
	}
	.sign-line {
	  height: 44px;
	  border-bottom: 1px dashed rgba(255,255,255,0.18);
	  margin-bottom: 10px;
	}

	/* ===== RESPONSIVE ===== */
	@media (max-width: 960px) {
	  .page { margin: 12px; padding: 20px 16px 26px; }
	  .header-grid { grid-template-columns: 1fr; gap: 12px; }
	}
	@media (max-width: 768px) {
	  .page { margin: 8px; padding: 14px 12px 18px; font-size: 12px; }
	  .page-heading h2 { font-size: 13px; }
	  .page-heading .heading-badge { display: none; }
	  .page-heading .heading-icon { width: 32px; height: 32px; font-size: 16px; }
	  .meta { padding: 12px; }
	  .meta label { min-width: 90px; font-size: 10px; }
	  .meta input, .meta select { font-size: 11px; padding: 5px 8px; }
	  .circuit-selector-row { padding: 10px 12px; }
	  #circuitType { width: 100%; font-size: 11px; }
	  .controls { flex-wrap: wrap; gap: 4px; }
	  .btn.small { padding: 6px 9px; font-size: 11px; }
	  .panel-table { min-width: 1200px; }
	  .panel-table th, .panel-table td { padding: 5px; font-size: 11px; min-width: 70px; }
	  .table-scroll { max-height: 300px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
	  .bottom-sign { flex-direction: column; gap: 14px; }
	  .sign-block { width: 100%; }
	  .back-home-btn { top: 10px; right: 10px; padding: 7px 11px; font-size: 11px; }
	  .view-mtr-btn  { top: 46px; right: 10px; padding: 7px 11px; font-size: 11px; }
	}
	@media print {
	  .controls, .add-row-btn, .back-home-btn, .view-mtr-btn { display: none !important; }
	  .page { box-shadow: none; border: none; background: #fff; color: #111; }
	  body { background: #fff; }
	}
  </style>
  <link rel="stylesheet" href="hangar-theme.css">
  <link rel="stylesheet" href="responsive.css">
  <!-- Firebase SDK -->
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database-compat.js"></script>
  <script src="firebase-config.js"></script>
  <script src="auth-guard.js"></script>
  <script src="db.js"></script>
</head>
<body>
  <button class="back-home-btn" onclick="window.location.href='index.php'">← Back to Home Page</button>
  <button class="view-mtr-btn" onclick="window.location.href='megger_test_viewer.php'">View MTR</button>
  <div class="page">

		<!-- Page Heading -->
		<div class="page-heading">
			<div class="heading-icon">⚡</div>
			<h2>Megger Test Result / Insulation Test</h2>
			<span class="heading-badge">IR Test</span>
		</div>

		<div class="header-grid">
			<div class="meta">
				<div class="meta-row"><label>Company Name:</label><input id="company" type="text" placeholder="Company" /></div>
				<div class="meta-row"><label>Location:</label><input id="location" type="text" placeholder="Location" /></div>
				<div class="meta-row"><label>Subject:</label><input id="subject" type="text" value="MEGGER TEST RESULT / INSULATION TEST" /></div>
			</div>
			<div class="meta">
				<div class="meta-row"><label>Date:</label><input id="testDate" type="date" /></div>
				<div class="meta-row"><label>IR Test Type:</label><select id="testTime"><option value="PI - 10mins and Above">PI - 10mins and Above</option><option value="DAR - 30Second period">DAR - 30Second period</option></select></div>
				<div class="meta-row"><label>Test Voltage:</label><input id="testVoltage" type="text" placeholder="e.g. 500V" /></div>
				<div class="meta-row"><label>Reference No:</label><input id="referenceNo" type="text" placeholder="REF-001" /></div>
			</div>
		</div>

		<div class="circuit-selector-row">
			<label>Circuit Type:</label>
			<select id="circuitType" style="width:260px">
				<option value="3phase-delta">Three Phase Delta</option>
				<option value="3phase-wye">Three Phase Wye</option>
				<option value="1phase-ll">Single Phase Line to Line</option>
				<option value="1phase-ln">Single Phase Line to Neutral</option>
			</select>
		</div>

		<div class="table-toolbar">
			<div class="table-title">Panel Board / Branch Circuit Readings in MΩ</div>
			<div class="controls">
				<button id="add-row" class="btn small">+ Add Row</button>
				<button id="remove-row" class="btn small secondary">− Remove Row</button>
				<button id="save-test" class="btn small">💾 Save Test</button>
				<button id="download-csv" class="btn small secondary">↓ CSV</button>
				<button id="print-report" class="btn small">🖨 Print</button>
			</div>
		</div>

		<div class="table-scroll">
			<table class="panel-table" id="panelTable">
				<thead>
					<tr>
						<th>PANEL</th>
						<th>BRANCH CKT #</th>
						<th>L1-L2</th>
						<th>L1-L3</th>
						<th>L2-L3</th>
						<th>L1-N</th>
						<th>L2-N</th>
						<th>L3-N</th>
						<th>L1-G</th>
						<th>L2-G</th>
						<th>L3-G</th>
						<th>N-G</th>
						<th>SIZE &amp; TYPE OF WIRE</th>
						<th>REMARKS</th>
					</tr>
				</thead>
				<tbody id="panelBody">
					<!-- rows inserted here -->
				</tbody>
			</table>
		</div>

		<div class="bottom-sign">
			<div class="sign-block">
				<div class="sign-label">Conducted By</div>
				<div class="sign-line"></div>
				<input id="conductedBy" type="text" placeholder="Electrician / Technician" />
			</div>
			<div class="sign-block">
				<div class="sign-label">Witnessed By</div>
				<div class="sign-line"></div>
				<input id="witnessedBy" type="text" placeholder="Representative" />
			</div>
			<div class="sign-block">
				<div class="sign-label">Verified By</div>
				<div class="sign-line"></div>
				<input id="verifiedBy" type="text" placeholder="Electrical Engineer" />
			</div>
		</div>
	</div>

	<script>
		const STORAGE_KEY = 'megger_tests_v2';

		function loadTests(){ try{ const r = localStorage.getItem(STORAGE_KEY); return r?JSON.parse(r):[] }catch(e){return[]} }
		function saveTests(arr){ localStorage.setItem(STORAGE_KEY, JSON.stringify(arr)); }

		function generateReferenceNumber(){
			const now = new Date();
			const date = now.toISOString().slice(0,10).replace(/-/g,'');
			const time = now.toTimeString().slice(0,8).replace(/:/g,'');
			return `MTR${date}${time}`;
		}

		function makeRow(){
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td><input class="panel-name" type="text" /></td>
				<td><input class="branch-ckt" type="text" /></td>
				<td><input class="v l1l2" type="number" step="0.01" /></td>
				<td><input class="v l1l3" type="number" step="0.01" /></td>
				<td><input class="v l2l3" type="number" step="0.01" /></td>
				<td><input class="v l1n" type="number" step="0.01" /></td>
				<td><input class="v l2n" type="number" step="0.01" /></td>
				<td><input class="v l3n" type="number" step="0.01" /></td>
				<td><input class="v l1g" type="number" step="0.01" /></td>
				<td><input class="v l2g" type="number" step="0.01" /></td>
				<td><input class="v l3g" type="number" step="0.01" /></td>
				<td><input class="v ng" type="number" step="0.01" /></td>
				<td>
					<select class="wire-select" style="width:100%;padding:4px;font-size:11px">
						<option value="">--Select--</option>
						<option value="3.5 SQ MM, THHN">3.5 SQ MM, THHN</option>
						<option value="5.5 SQ MM, THHN">5.5 SQ MM, THHN</option>
						<option value="8.0 SQ MM, THHN">8.0 SQ MM, THHN</option>
						<option value="14.0 SQ MM, THHN">14.0 SQ MM, THHN</option>
						<option value="22.0 SQ MM, THHN">22.0 SQ MM, THHN</option>
						<option value="30.0 SQ MM, THHN">30.0 SQ MM, THHN</option>
						<option value="38.0 SQ MM, THHN">38.0 SQ MM, THHN</option>
						<option value="50.0 SQ MM, THHN">50.0 SQ MM, THHN</option>
						<option value="60 SQ MM, THHN">60 SQ MM, THHN</option>
						<option value="80 SQ MM, THHN">80 SQ MM, THHN</option>
						<option value="100 SQ MM, THHN">100 SQ MM, THHN</option>
						<option value="125 SQ MM, THHN">125 SQ MM, THHN</option>
						<option value="__custom__">Custom</option>
					</select>
					<input class="wire-custom" type="text" style="display:none;width:100%;margin-top:4px;padding:4px;font-size:11px" placeholder="Enter wire type" />
				</td>
				<td><input class="remarks" type="text" /></td>
			`;
			tr.querySelectorAll('input.v').forEach(inp => { inp.addEventListener('input', () => autoRemarks(tr)); });
			const wireSel = tr.querySelector('.wire-select');
			const wireCustom = tr.querySelector('.wire-custom');
			wireSel.addEventListener('change', () => {
				if(wireSel.value === '__custom__'){ wireCustom.style.display = ''; wireCustom.focus(); }
				else { wireCustom.style.display = 'none'; wireCustom.value = ''; }
			});
			return tr;
		}

		// Auto-set REMARKS to Passed/Failed based on MΩ readings
		function autoRemarks(tr){
			const inputs = tr.querySelectorAll('input.v');
			const remarksInput = tr.querySelector('.remarks');
			if(!remarksInput) return;
			// Collect all enabled, non-empty voltage values
			const values = [];
			inputs.forEach(inp => {
				if(!inp.disabled && inp.value !== ''){
					const n = parseFloat(inp.value);
					if(!isNaN(n)) values.push(n);
				}
			});
			if(values.length === 0){
				// No readings yet — clear auto-remark
				if(remarksInput.value === 'Passed' || remarksInput.value === 'Failed') remarksInput.value = '';
				remarksInput.style.color = '';
				return;
			}
			// If ANY value is below 350, mark Failed; otherwise Passed
			const anyBelow = values.some(v => v < 350);
			remarksInput.value = anyBelow ? 'Failed' : 'Passed';
			remarksInput.style.color = anyBelow ? '#c00' : '#060';
		}

		function applyCircuitTypeRules(){
			const type = document.getElementById('circuitType').value;
			let disableClasses = [];
			
			if(type === '3phase-delta'){
				// Three Phase Delta: Disable L1-N, L2-N, L3-N, N-G
				disableClasses = ['l1n', 'l2n', 'l3n', 'ng'];
			} else if(type === '1phase-ll'){
				// Single Phase Line to Line: Enable L1-L2, L1-G, L2-G only
				disableClasses = ['l1l3', 'l2l3', 'l1n', 'l2n', 'l3n', 'l3g', 'ng'];
			} else if(type === '1phase-ln'){
				// Single Phase Line to Neutral: Enable L1-N, L1-G, N-G only
				disableClasses = ['l1l2', 'l1l3', 'l2l3', 'l2n', 'l3n', 'l2g', 'l3g'];
			} else if(type === '3phase-wye'){
				// Three Phase Wye: Enable all fields
				disableClasses = [];
			}
			
			// First enable all fields
			document.querySelectorAll('#panelBody input.v').forEach(inp => {
				inp.disabled = false;
				inp.style.background = '';
			});
		
		disableClasses.forEach(cls => {
			if(cls){
				document.querySelectorAll('#panelBody input.v.'+cls).forEach(inp => {
					inp.disabled = true;
					inp.value = '';
					inp.style.background = '#161924';
				});
			}
		});
	}

	function getFormData(){
		const rows = [];
		document.querySelectorAll('#panelBody tr').forEach(tr=>{
			const obj = {
				panel: tr.querySelector('.panel-name').value.trim(),
				branch: tr.querySelector('.branch-ckt').value.trim(),
				'L1-L2': tr.querySelector('.l1l2').value || '',
				'L1-L3': tr.querySelector('.l1l3').value || '',
				'L2-L3': tr.querySelector('.l2l3').value || '',
				'L1-N': tr.querySelector('.l1n').value || '',
				'L2-N': tr.querySelector('.l2n').value || '',
				'L3-N': tr.querySelector('.l3n').value || '',
				'L1-G': tr.querySelector('.l1g').value || '',
				'L2-G': tr.querySelector('.l2g').value || '',
				'L3-G': tr.querySelector('.l3g').value || '',
				'N-G': tr.querySelector('.ng').value || '',
				wire: (function(){ const s=tr.querySelector('.wire-select'); return s&&s.value==='__custom__' ? tr.querySelector('.wire-custom').value.trim() : (s?s.value:''); })(),
				remarks: tr.querySelector('.remarks').value || ''
			};
			const any = Object.values(obj).some(v=>v!="");
			if(any) rows.push(obj);
		});
		return {
			company: document.getElementById('company').value.trim(),
			location: document.getElementById('location').value.trim(),
			subject: document.getElementById('subject').value.trim(),
			date: document.getElementById('testDate').value || new Date().toISOString().slice(0,10),
			time: document.getElementById('testTime').value || '',
			testVoltage: document.getElementById('testVoltage').value || '',
			referenceNo: document.getElementById('referenceNo').value.trim(),
			circuitType: document.getElementById('circuitType').value,
			rows: rows,
			conductedBy: document.getElementById('conductedBy').value.trim(),
			witnessedBy: document.getElementById('witnessedBy').value.trim(),
			verifiedBy: document.getElementById('verifiedBy').value.trim(),
			createdAt: new Date().toISOString()
		};
	}

	async function saveTest(){
			const rec = getFormData();
			// Require a file name from user and save into viewer file list
			let fileName = prompt('Enter file name to save this test (no extension):');
			if(!fileName) { alert('Save cancelled - file name required.'); return; }
			fileName = fileName.trim();
			if(!fileName) { alert('Save cancelled - file name required.'); return; }

			// Prepare file object for viewer
			const fileObj = { name: fileName, data: rec, savedAt: new Date().toISOString() };
			

			// Load existing viewer files (separate key)
			const VIEW_KEY = 'megger_test_files_v1';
			async function loadViewerFiles(){
				try{
					return await DB.list(VIEW_KEY);
				}catch(e){
					console.warn('DB load failed, falling back to localStorage',e);
					try{ const raw = localStorage.getItem(VIEW_KEY); return raw?JSON.parse(raw):[] }catch(e){return[]}
				}
			}
			async function saveViewerFiles(a){
				// Normalize stored items to {name,data,savedAt} shape for viewer compatibility
				const norm = (a||[]).map(item=>{
					if(!item) return null;
					if(item.name && item.data) return item;
					if(item.rows || item.company) return { name: item.name || (item.referenceNo?item.referenceNo:'megger_'+(item.date||'')), data: item, savedAt: item.savedAt || item.createdAt || new Date().toISOString() };
					return item;
				}).filter(Boolean);
				localStorage.setItem(VIEW_KEY, JSON.stringify(norm));
				try{ localStorage.setItem(VIEW_KEY+'_backup', JSON.stringify(norm)); }catch(e){}
				try{
					await fetch('api/bulk.php?collection='+encodeURIComponent(VIEW_KEY),{
						method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(a)
					});
				}catch(e){ console.error('DB save failed:',e); }
			}

			let files = await loadViewerFiles();
			const editIdx = sessionStorage.getItem('megger_edit_idx');
			if(editIdx !== null){
				const idx = parseInt(editIdx,10);
				if(!isNaN(idx) && idx>=0 && idx<files.length){
					// overwrite existing file - preserve name if user left it blank
					files[idx] = fileObj;
					await saveViewerFiles(files);
					sessionStorage.removeItem('megger_edit_idx');
					alert('Saved test (edited).');
					return;
				}
			}

			// If same name exists, append timestamp to avoid silent overwrite
			if(files.some(f=>f.name === fileObj.name)){
				fileObj.name = `${fileObj.name}_${(new Date()).toISOString().replace(/[:.]/g,'')}`;
			}
			files.push(fileObj);
			await saveViewerFiles(files);
			alert('Saved test.');
	}

	function buildCSV(records){
		const BOM = '\uFEFF';
		const hdr = ['Date','Company','Location','Panel','Branch','L1-L2','L1-L3','L2-L3','L1-N','L2-N','L3-N','L1-G','L2-G','L3-G','N-G','Wire','Remarks','ConductedBy','WitnessedBy','VerifiedBy'];
		const lines = [hdr.map(h=>`"${h.replace(/"/g,'""')}"`).join(',')];
		records.forEach(r=>{
			(r.rows||[]).forEach(row=>{
				const vals = [r.date||'', r.company||'', r.location||'', row.panel||'', row.branch||'', row['L1-L2']||'', row['L1-L3']||'', row['L2-L3']||'', row['L1-N']||'', row['L2-N']||'', row['L3-N']||'', row['L1-G']||'', row['L2-G']||'', row['L3-G']||'', row['N-G']||'', row.wire||'', row.remarks||'', r.conductedBy||'', r.witnessedBy||'', r.verifiedBy||''];
				lines.push(vals.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(','));
			});
		});
		return BOM + lines.join('\r\n');
	}

	function renderPrintable(r){
				function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
				let rowsHtml = '';
				(r.rows||[]).forEach((row,i)=>{
					const bg = i%2===0 ? '' : ' style="background:#f9fafb"';
					const rmk = esc(row.remarks);
					const rmkStyle = rmk==='Passed' ? 'color:#060;font-weight:700' : rmk==='Failed' ? 'color:#c00;font-weight:700' : '';
					rowsHtml += `<tr${bg}><td>${esc(row.panel)}</td><td>${esc(row.branch)}</td><td>${esc(row['L1-L2'])}</td><td>${esc(row['L1-L3'])}</td><td>${esc(row['L2-L3'])}</td><td>${esc(row['L1-N'])}</td><td>${esc(row['L2-N'])}</td><td>${esc(row['L3-N'])}</td><td>${esc(row['L1-G'])}</td><td>${esc(row['L2-G'])}</td><td>${esc(row['L3-G'])}</td><td>${esc(row['N-G'])}</td><td>${esc(row.wire)}</td><td style="${rmkStyle}">${rmk}</td></tr>`;
				});
				const html = `<!doctype html><html><head><meta charset="utf-8"><title>Megger Test Report</title>`+
					`<style>`+
					`@page{size:landscape;margin:12mm}`+
					`body{font-family:'Segoe UI',Arial,Helvetica,sans-serif;margin:0;padding:24px;color:#111;background:#fff}`+
					`.report-title{text-align:center;margin-bottom:18px;border-bottom:3px solid #0b78d1;padding-bottom:12px}`+
					`.report-title h2{margin:0;font-size:20px;color:#0b78d1;letter-spacing:1px;text-transform:uppercase}`+
					`.report-title .subtitle{font-size:12px;color:#666;margin-top:4px}`+
					`.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 24px;margin-bottom:16px;padding:12px;border:1px solid #ddd;border-radius:6px;background:#fafbfc}`+
					`.info-grid .info-item{font-size:13px;padding:3px 0}`+
					`.info-grid .info-item strong{color:#333;min-width:110px;display:inline-block}`+
					`table{width:100%;border-collapse:collapse;margin-top:10px;font-size:11px}`+
					`th{background:#0b78d1;color:#fff;padding:7px 5px;text-align:center;font-weight:600;font-size:11px;border:1px solid #0966b3}`+
					`td{border:1px solid #ccc;padding:5px;text-align:center}`+
					`.sign-section{display:flex;gap:24px;justify-content:space-between;margin-top:28px;page-break-inside:avoid}`+
					`.sign-block{flex:1;text-align:center}`+
					`.sign-block .line{border-bottom:1px solid #000;height:32px;margin-bottom:6px}`+
					`.sign-block .name{font-weight:700;font-size:13px}`+
					`.sign-block .role{font-size:11px;color:#555;margin-top:2px}`+
					`.sign-block .date{font-size:11px;color:#555;margin-top:4px}`+
					`.footer{text-align:center;margin-top:20px;font-size:10px;color:#999;border-top:1px solid #eee;padding-top:8px}`+
					`</style></head><body>`+
					`<div class="report-title"><h2>Megger Test Report / Insulation Resistance Test</h2><div class="subtitle">${esc(r.subject)||'MEGGER TEST RESULT / INSULATION TEST'}</div></div>`+
					`<div class="info-grid">`+
						`<div class="info-item"><strong>Company:</strong> ${esc(r.company)}</div>`+
						`<div class="info-item"><strong>Reference No:</strong> ${esc(r.referenceNo)}</div>`+
						`<div class="info-item"><strong>Location:</strong> ${esc(r.location)}</div>`+
						`<div class="info-item"><strong>Date:</strong> ${esc(r.date)}</div>`+
						`<div class="info-item"><strong>Test Voltage:</strong> ${esc(r.testVoltage)}</div>`+
						`<div class="info-item"><strong>IR Test Type:</strong> ${esc(r.time)}</div>`+
					`</div>`+
					`<table><thead><tr><th>PANEL</th><th>BRANCH CKT #</th><th>L1-L2</th><th>L1-L3</th><th>L2-L3</th><th>L1-N</th><th>L2-N</th><th>L3-N</th><th>L1-G</th><th>L2-G</th><th>L3-G</th><th>N-G</th><th>WIRE</th><th>REMARKS</th></tr></thead><tbody>${rowsHtml}</tbody></table>`+
								`<div class="sign-section">`+
						`<div class="sign-block"><div class="line"></div><div class="name">${esc(r.conductedBy)||'&nbsp;'}</div><div class="role">Conducted By</div><div class="date">Date: ${esc(r.date)||''}</div></div>`+
									`<div class="sign-block"><div class="line"></div><div class="name">${esc(r.witnessedBy)||'&nbsp;'}</div><div class="role">Witnessed By</div><div class="date">Date: ${esc(r.date)||''}</div></div>`+
									`<div class="sign-block"><div class="line"></div><div class="name">${esc(r.verifiedBy)||'&nbsp;'}</div><div class="role">Verified By</div><div class="date">Date: ${esc(r.date)||''}</div></div>`+
								`</div>`+
								`<div class="footer">This document is auto-generated by the EPM Megger Test System</div>`+
								`<scr`+`ipt>window.print()</scr`+`ipt></body></html>`;
		return html;
	}

	// Initialize on page load
	document.addEventListener('DOMContentLoaded', function(){
		const panelBody = document.getElementById('panelBody');

		function populateFormFromRecord(r){
			if(!r) return;
			// basic fields
			document.getElementById('company').value = r.company || '';
			document.getElementById('location').value = r.location || '';
			document.getElementById('subject').value = r.subject || '';
			document.getElementById('testDate').value = r.date || new Date().toISOString().slice(0,10);
			document.getElementById('testTime').value = r.time || '';
			document.getElementById('testVoltage').value = r.testVoltage || '';
			document.getElementById('referenceNo').value = r.referenceNo || generateReferenceNumber();
			document.getElementById('circuitType').value = r.circuitType || '3phase-wye';
			document.getElementById('conductedBy').value = r.conductedBy || '';
			document.getElementById('witnessedBy').value = r.witnessedBy || '';
			document.getElementById('verifiedBy').value = r.verifiedBy || '';
			// rows
			panelBody.innerHTML = '';
			(r.rows||[]).forEach(row=>{
				const tr = makeRow();
				panelBody.appendChild(tr);
				tr.querySelector('.panel-name').value = row.panel || '';
				tr.querySelector('.branch-ckt').value = row.branch || '';
				tr.querySelector('.l1l2').value = row['L1-L2'] || '';
				tr.querySelector('.l1l3').value = row['L1-L3'] || '';
				tr.querySelector('.l2l3').value = row['L2-L3'] || '';
				tr.querySelector('.l1n').value = row['L1-N'] || '';
				tr.querySelector('.l2n').value = row['L2-N'] || '';
				tr.querySelector('.l3n').value = row['L3-N'] || '';
				tr.querySelector('.l1g').value = row['L1-G'] || '';
				tr.querySelector('.l2g').value = row['L2-G'] || '';
				tr.querySelector('.l3g').value = row['L3-G'] || '';
				tr.querySelector('.ng').value = row['N-G'] || '';
				// Wire: check if value matches a predefined option
				(function(){
					const ws = tr.querySelector('.wire-select');
					const wc = tr.querySelector('.wire-custom');
					const wv = row.wire || '';
					const opts = Array.from(ws.options).map(o=>o.value);
					if(opts.includes(wv)){ ws.value = wv; wc.style.display='none'; }
					else if(wv){ ws.value='__custom__'; wc.style.display=''; wc.value=wv; }
					else { ws.value=''; wc.style.display='none'; }
				})();
				tr.querySelector('.remarks').value = row.remarks || '';
			});
			applyCircuitTypeRules();
		}
		
		function addRow(){ 
			panelBody.appendChild(makeRow()); 
			applyCircuitTypeRules(); 
		}
		
		function removeRow(){ 
			if(panelBody.lastElementChild) panelBody.removeChild(panelBody.lastElementChild); 
		}
		
		// Check if editing an existing file (from viewer)
		const editIdx = sessionStorage.getItem('megger_edit_idx');
		if(editIdx !== null){
			try{
				const VIEW_KEY = 'megger_test_files_v1';
				const raw = localStorage.getItem(VIEW_KEY);
				if(raw){
					let arr = JSON.parse(raw);
					// arr items may wrap data as JSON string; normalize
					if(Array.isArray(arr) && arr.length>0){
						const idx = parseInt(editIdx,10);
						if(!isNaN(idx) && arr[idx]){
							let rec = arr[idx].data || arr[idx];
							if(typeof rec === 'string'){
								try{ rec = JSON.parse(rec); }catch(e){ console.warn('Failed to parse saved file data', e); }
							}
							populateFormFromRecord(rec);
							// keep editIdx until save overwrites and clears it
						}
					}
				}
			}catch(e){console.error('Failed to load edit file',e)}
		} else {
			// Set initial reference number
			document.getElementById('referenceNo').value = generateReferenceNumber();
			// Set today's date
			const today = new Date().toISOString().slice(0,10);
			document.getElementById('testDate').value = today;
			// Initialize with 12 empty rows
			for(let i=0;i<12;i++) addRow();
			// Apply initial circuit type rules
			applyCircuitTypeRules();
		}
		
		// Add event listeners
		document.getElementById('add-row').addEventListener('click', ()=> addRow());
		document.getElementById('remove-row').addEventListener('click', ()=> removeRow());
		document.getElementById('circuitType').addEventListener('change', applyCircuitTypeRules);
		document.getElementById('save-test').addEventListener('click', saveTest);
		document.getElementById('download-csv').addEventListener('click', ()=>{
			const rec = getFormData();
			const csv = buildCSV([rec]);
			const blob = new Blob([csv],{type:'text/csv;charset=utf-8;'});
			const a = document.createElement('a'); 
			a.href = URL.createObjectURL(blob); 
			a.download = `megger_${rec.date||new Date().toISOString().slice(0,10)}.csv`; 
			document.body.appendChild(a); 
			a.click(); 
			a.remove(); 
			URL.revokeObjectURL(a.href);
		});
		document.getElementById('print-report').addEventListener('click', ()=>{
			const rec = getFormData();
			// Auto-generate reference number for print
			rec.referenceNo = generateReferenceNumber();
			const html = renderPrintable(rec);
			const blob = new Blob([html],{type:'text/html'});
			const url = URL.createObjectURL(blob);
			const w = window.open(url, '_blank');
			setTimeout(()=> URL.revokeObjectURL(url), 2000);
		});
		

	});
	</script>
	<script>
		// Ensure page text contrasts with the current theme/background.
		(function(){
			function parseRGB(s){
				if(!s) return null;
				const m = s.match(/rgba?\(([^)]+)\)/);
				if(!m) return null;
				const parts = m[1].split(',').map(p=>p.trim());
				const r = parseInt(parts[0],10)||0;
				const g = parseInt(parts[1],10)||0;
				const b = parseInt(parts[2],10)||0;
				const a = parts[3] ? parseFloat(parts[3]) : 1;
				return {r,g,b,a};
			}
			function luminance(c){
				const sRGB = [c.r/255, c.g/255, c.b/255].map(v=>{
					return v <= 0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055, 2.4);
				});
				return 0.2126*sRGB[0] + 0.7152*sRGB[1] + 0.0722*sRGB[2];
			}
			function updateContrast(){
				const page = document.querySelector('.page');
				let bg = null;
				if(page) bg = getComputedStyle(page).backgroundColor;
				if(!bg || bg === 'transparent' || bg.indexOf('rgba(0, 0, 0, 0)')!==-1) bg = getComputedStyle(document.body).backgroundColor;
				const rgb = parseRGB(bg);
				if(!rgb) return;
				const L = luminance(rgb);
				// WCAG relative threshold ~0.5 is a safe split; lower luminance -> use light text
				const useLight = L < 0.5;
				document.documentElement.style.setProperty('--page-text', useLight ? '#fff' : '#111');
			}
			// Run once, and when theme attribute changes on body
			document.addEventListener('DOMContentLoaded', updateContrast);
			const obs = new MutationObserver(muts=>{
				for(const m of muts){
					if(m.attributeName === 'data-theme') { updateContrast(); break; }
				}
			});
			obs.observe(document.body, { attributes: true });
			// Also expose a small API for manual invocation
			window.__epm_update_contrast = updateContrast;
		})();
	</script>
	<script src="hangar-theme.js"></script>
<script src="theme-loader.js"></script>
</body>
</html>
