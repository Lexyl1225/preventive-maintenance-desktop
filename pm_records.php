<?php
require_once __DIR__.'/db-config.php';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width,initial-scale=1"/>
	<link rel="icon" type="image/x-icon" href="images/favicon.ico">
	<title>PM Records - Backup Viewer</title>
	<style>
		*{margin:0;padding:0;box-sizing:border-box}
		:root{--bg:#000000;--card:#2e0606;--accent:#0b78d1;--muted:#6b7280}
		body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:#111;padding:20px}
		.container{max-width:1400px;margin:0 auto}
		h1{font-size:24px;margin-bottom:8px;color:#111}
		.subtitle{color:var(--muted);font-size:14px;margin-bottom:20px}
		.toolbar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
		button{background:var(--accent);color:#531d1d;border:0;padding:10px 16px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:500;transition:all 0.2s}
		button:hover{opacity:0.9;transform:translateY(-1px)}
		button.secondary{background:#0b2049;color:#111}
		.card{background:var(--card);border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:20px;margin-bottom:20px}
		table{width:100%;border-collapse:collapse;font-size:13px}
		th,td{text-align:left;padding:10px;border-bottom:1px solid #283b61}
		th{background:#0a2d74;font-weight:600;position:sticky;top:0}
        tr:hover{background:rgba(0,40,80,.85)}
		.empty{text-align:center;color:var(--muted);padding:40px;font-size:14px}
		@media (max-width:768px){
			body{padding:10px}
			h1{font-size:18px}
			.subtitle{font-size:12px}
			.toolbar{gap:6px}
			button{padding:8px 12px;font-size:12px}
			.card{padding:12px}
			table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;font-size:11px}
			th,td{padding:6px;white-space:nowrap}
		}
		@media print{.toolbar{display:none}}
		/* files/controls that should not appear on printed output */
		.no-print{display:inline-block}
		.print-only{display:none}
		@media print{ .no-print{display:none !important} }
		.files-thumb{width:60px;height:40px;object-fit:cover;border-radius:4px;margin-right:6px}
		/* date filter inputs */
		.date-filter-label{font-weight:500;color:#e0e0e0;font-size:13px;white-space:nowrap}
		.date-filter-input{padding:6px 8px;border:1px solid #3a5580;border-radius:4px;background:#0b2049;color:#fff;font-size:13px}

		/* Print optimizations — moved to a dedicated <style media="print"> block below hangar-theme.css */
	</style>
	<link rel="stylesheet" href="hangar-theme.css">
	<style media="print">
		/* Force a clean white page — completely independent of any theme */
		@page { size: A4 landscape; margin: 12mm; }
		* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
		body, html { background: #fff !important; color: #000 !important; padding: 0 !important; margin: 0 !important; }
		.container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
		/* Hide screen-only elements — use high specificity for table cells */
		h1, .subtitle, .toolbar, .filter-section, .no-print,
		th.no-print, td.no-print { display: none !important; }
		.card { box-shadow: none !important; background: #fff !important; padding: 0 !important; border: none !important; border-radius: 0 !important; }
		/* Show print-only elements */
		.print-only { display: block !important; }
		/* Print header block */
		#print-header { margin-bottom: 14px !important; padding-bottom: 8px !important; border-bottom: 2px solid #333 !important; background: #fff !important; }
		#print-header * { background: #fff !important; }
		#print-header h2 { font-size: 16px !important; color: #000 !important; margin: 0 0 4px !important; background: none !important; }
		#print-header .ph-meta { display: flex !important; gap: 16px !important; flex-wrap: wrap !important; }
		#print-header .ph-meta span, #print-header span { font-size: 11px !important; color: #333 !important; background: none !important; }
		/* Month section dividers — keep with following table */
		div.month-header { color: #000 !important; background: none !important; border-bottom: 2px solid #555 !important; font-size: 13px !important; font-weight: 700 !important; margin: 16px 0 6px !important; padding-bottom: 4px !important; page-break-after: avoid !important; }
		/* Table reset — use auto layout so hidden columns collapse cleanly */
		table { width: 100% !important; table-layout: auto !important; border-collapse: collapse !important; font-size: 10px !important; background: #fff !important; border: none !important; margin-bottom: 0 !important; }
		thead { display: table-header-group !important; }
		thead, thead tr { background: #dde8f7 !important; }
		thead th, th { background: #dde8f7 !important; color: #000 !important; font-weight: 700 !important; border: 1px solid #aaa !important; padding: 5px 4px !important; position: static !important; white-space: normal !important; word-wrap: break-word !important; }
		tbody tr:nth-child(odd) td { background: #fff !important; }
		tbody tr:nth-child(even) td { background: #f5f7fa !important; }
		td { color: #000 !important; border: 1px solid #aaa !important; padding: 5px 4px !important; vertical-align: top !important; white-space: normal !important; word-wrap: break-word !important; overflow-wrap: break-word !important; }
		tr { page-break-inside: avoid !important; }
		/* Prevent orphaned headers at bottom of page */
		thead tr { page-break-after: avoid !important; }
	</style>
	<!-- Firebase SDK -->
	<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
	<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-database-compat.js"></script>
	<script src="firebase-config.js"></script>
	<script src="auth-guard.js"></script>
	<script src="db.js"></script>
</head>
<body>
	<div class="container">
		<h1>PM Records - Backup Viewer</h1>
		<p class="subtitle">View, export, and manage your preventive maintenance records</p>
		
		<div class="toolbar">
			<button onclick="window.location.href='index.php'">← Back to App</button>
			<button onclick="openPreventiveMaintenanceRecord()">Open Preventive Maintenance Record</button>
			<button id="open-backup-btn" onclick="openBackupFile()" style="display:none">Open Json / CSV Backup</button>
			<button class="secondary" onclick="downloadJSON()">Download JSON</button>
			<button class="secondary" onclick="exportCSV()">Export CSV</button>
			<button class="secondary" onclick="printAsPDF()">Print / Save PDF</button>
			<button class="secondary" onclick="window.open('YOUR_ONEDRIVE_BACKUP_URL_HERE','_blank')">Download from OneDrive</button>
		</div>

		<div class="filter-section" style="background: #1767b8; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
			<label style="font-weight: 500; color: #e0e0e0;">Month/Year:</label>
			<select id="month-filter" onchange="filterChange()" style="padding: 6px 8px; border: 1px solid #3a5580; border-radius: 4px; min-width: 150px; background:#0b2049; color:#fff;">
				<option value="">-- All Months --</option>
			</select>
			<span style="width:1px;height:24px;background:rgba(255,255,255,0.25);display:inline-block;"></span>
			<label class="date-filter-label">From Date:</label>
			<input type="date" id="date-from" class="date-filter-input" onchange="filterChange()" />
			<label class="date-filter-label">To Date:</label>
			<input type="date" id="date-to" class="date-filter-input" onchange="filterChange()" />
			<button onclick="resetFilters()" style="padding: 6px 12px; background: #6b7280; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Reset Filters</button>
			<span id="filter-info" style="color: #e0e0e0; font-size: 13px; margin-left: auto;"></span>
		</div>

		<!-- Print-only header (hidden on screen) -->
		<div id="print-header" class="print-only">
			<h2 id="ph-title">Electrical Preventive Maintenance Records</h2>
			<div class="ph-meta">
				<span id="ph-filter"></span>
				<span id="ph-count"></span>
				<span id="ph-date"></span>
			</div>
		</div>

		<input type="file" id="backup-file-input" accept=".json,.csv" style="display:none" />

		<div class="card">
			<div id="table-container"></div>
			<div id="empty" class="empty" style="display:none">No records found. Return to the main app to add records.</div>
		</div>
	</div>

	<script>
		const KEY = 'epm_records_v1';

		async function loadRecords(){
            return await DB.list(KEY);
        }

		// after making changes you should call the appropriate API endpoint
        // this helper is rarely used; we keep it for compatibility
        async function saveRecords(records) {
            await fetch('api/bulk.php?collection=' + encodeURIComponent(KEY), {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify(records)
            });
        }

		// Extract month/year from date string (handles YYYY-MM-DD format)
		function getMonthYear(dateStr) {
			if (!dateStr) return null;
			const parts = dateStr.split('-');
			if (parts.length >= 2) {
				return parts[0] + '-' + parts[1]; // YYYY-MM
			}
			return null;
		}

		// Format month-year for display (e.g., "December 2025")
		function formatMonthYear(monthYear) {
			if (!monthYear) return '';
			const [year, month] = monthYear.split('-');
			const date = new Date(year, parseInt(month) - 1, 1);
			return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
		}

		// Populate month/year filter dropdown
		async function populateFilterOptions() {
			try {
				const records = await loadRecords();
				console.log('Populating filter with records:', records.length);
				const monthYears = new Set();

				records.forEach(rec => {
					const my = getMonthYear(rec.date);
					console.log('Record date:', rec.date, 'Month-Year:', my);
					if (my) monthYears.add(my);
				});

				const sorted = Array.from(monthYears).sort().reverse(); // Newest first
				console.log('Sorted months:', sorted);
				const select = document.getElementById('month-filter');
				const currentValue = select.value;

				// Clear and repopulate (keep "All Months" option)
				select.innerHTML = '<option value="">-- All Months --</option>';
				sorted.forEach(my => {
					const option = document.createElement('option');
					option.value = my;
					option.textContent = formatMonthYear(my);
					console.log('Adding option:', my, '->', formatMonthYear(my));
					select.appendChild(option);
				});

				select.value = currentValue; // Restore selection if it still exists
			} catch(e) {
				console.error('Error populating filter:', e);
			}
		}

		async function renderTable(){
			const records = await loadRecords();
			// load uploads and map by record_id for quick lookup
			try{
				const ups = await fetch('api/uploads.php').then(r=>r.ok? r.json(): []);
				window._pm_uploads_map = {};
				if(Array.isArray(ups)){
					ups.forEach(u=>{ if(u.record_id){ window._pm_uploads_map[u.record_id] = window._pm_uploads_map[u.record_id] || []; window._pm_uploads_map[u.record_id].push(u); } });
				}
			}catch(e){ window._pm_uploads_map = {}; }
			const container = document.getElementById('table-container');
			const emptyEl = document.getElementById('empty');
			const filterInfo = document.getElementById('filter-info');

			// Get selected filters
			const selectedMonth = document.getElementById('month-filter').value;
			const dateFrom = document.getElementById('date-from').value;
			const dateTo = document.getElementById('date-to').value;

			// Filter records based on selected month and/or date range
			let filteredRecords = records;
			if (selectedMonth) {
				filteredRecords = filteredRecords.filter(rec => getMonthYear(rec.date) === selectedMonth);
			}
			if (dateFrom) {
				filteredRecords = filteredRecords.filter(rec => rec.date && rec.date >= dateFrom);
			}
			if (dateTo) {
				filteredRecords = filteredRecords.filter(rec => rec.date && rec.date <= dateTo);
			}

			if(filteredRecords.length === 0){
				container.innerHTML = '';
				emptyEl.style.display = 'block';
				filterInfo.textContent = selectedMonth ? `No records for ${formatMonthYear(selectedMonth)}` : 'No records found';
				return;
			}

			emptyEl.style.display = 'none';
			let filterDesc = '';
			if (selectedMonth) filterDesc = `for ${formatMonthYear(selectedMonth)}`;
			else if (dateFrom || dateTo) filterDesc = `from ${dateFrom||'start'} to ${dateTo||'today'}`;
			filterInfo.textContent = filterDesc
				? `Showing ${filteredRecords.length} record(s) ${filterDesc}`
				: `Showing ${filteredRecords.length} total record(s)`;

			// Sort records by date descending
			filteredRecords.sort((a, b) => {
				const dateA = new Date(a.date || '1900-01-01');
				const dateB = new Date(b.date || '1900-01-01');
				return dateB - dateA;
			});

			let html = '';
			let currentMonth = '';

			filteredRecords.forEach(rec => {
				const monthYear = getMonthYear(rec.date);
				
				// Add month header if different from previous
				if (monthYear !== currentMonth) {
					if (currentMonth !== '') {
						html += '</tbody></table>';
					}
					currentMonth = monthYear;
					html += `<div class="month-header" style="margin-top: 20px; margin-bottom: 12px; font-weight: 600; color: #0b78d1; font-size: 14px; padding-bottom: 8px; border-bottom: 2px solid #0b78d1;">${formatMonthYear(monthYear)}</div>`;
					html += '<table><thead><tr>';
					html += '<th>Date</th><th>Branch Code</th><th>Branch Name</th><th>Equipment</th><th>Location</th>';
					html += '<th>Task</th><th>Status</th><th>Performed By</th><th>Verified By</th><th>Next Due</th><th>Notes</th><th class="no-print">Files</th>';
					html += '</tr></thead><tbody>';
				}

				html += '<tr>';
				html += `<td>${escapeHTML(rec.date || '')}</td>`;
				html += `<td>${escapeHTML(rec.BranchCode || '')}</td>`;
				html += `<td>${escapeHTML(rec.BranchName || '')}</td>`;
				html += `<td>${escapeHTML(rec.equipment || '')}</td>`;
				html += `<td>${escapeHTML(rec.location || '')}</td>`;
				html += `<td>${escapeHTML(rec.task || '')}</td>`;
				html += `<td>${escapeHTML(rec.status || '')}</td>`;
				html += `<td>${escapeHTML(rec.performedBy || '')}</td>`;
				html += `<td>${escapeHTML(rec.verifiedBy || '')}</td>`;
				html += `<td>${escapeHTML(rec.nextDue || '')}</td>`;
				html += `<td>${escapeHTML(rec.notes || '')}</td>`;

				// files cell (non-printable)
				const recFiles = (window._pm_uploads_map && window._pm_uploads_map[rec.id]) || [];
				if(recFiles.length === 0){
					// show placeholder thumbnail + View button
					html += `<td class="no-print"><div class="files-thumb" style="display:inline-flex;align-items:center;justify-content:center;background:#ccc;color:#333;width:60px;height:40px;border-radius:4px;margin-right:6px;font-size:12px">No</div><button class="small no-print" onclick="openGalleryForRecord(${rec.id})">View</button></td>`;
				} else {
					// show first file as thumbnail plus count and View button
					const f0 = recFiles[0];
					const fn0 = (f0.file_name||'').toLowerCase();
					const ext0 = (fn0.split('.').pop()||'');
					let thumbHtml = '';
					if(['mp4','webm','mov','ogg','avi','mkv'].includes(ext0)){
						thumbHtml = `<a href="${f0.file_path}" target="_blank" title="${escapeHTML(f0.file_name)}"><video class="files-thumb" src="${f0.file_path}" muted></video></a>`;
					} else {
						thumbHtml = `<a href="${f0.file_path}" target="_blank" title="${escapeHTML(f0.file_name)}"><img class="files-thumb" src="${f0.file_path}"/></a>`;
					}
					let more = '';
					if(recFiles.length > 1) more = `<span style="font-size:12px;color:#ccc;margin-left:6px">+${recFiles.length-1}</span>`;
					html += `<td class="no-print">${thumbHtml} ${more} <button class="small no-print" onclick="openGalleryForRecord(${rec.id})">View</button></td>`;
				}
				html += '</tr>';
			});

			if (filteredRecords.length > 0) {
				html += '</tbody></table>';
			}

			container.innerHTML = html;
		}

		function resetFilters() {
			document.getElementById('month-filter').value = '';
			document.getElementById('date-from').value = '';
			document.getElementById('date-to').value = '';
			renderTable();
		}

		function filterChange() {
			renderTable();
		}

		function escapeHTML(str){
			const div = document.createElement('div');
			div.textContent = str;
			return div.innerHTML;
		}

		function openGalleryForRecord(id){
			// open gallery in new tab; user can manage associations there
			window.open('uploads_gallery.php?record_id=' + encodeURIComponent(id), '_blank');
		}

		async function downloadJSON(){
			const records = await loadRecords();
			const blob = new Blob([JSON.stringify(records, null, 2)], {type:'application/json'});
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `pm_records_backup_${new Date().toISOString().split('T')[0]}.json`;
			a.click();
			URL.revokeObjectURL(url);
		}

		async function exportCSV(){
			const records = await loadRecords();
			if(records.length === 0){
				alert('No records to export');
				return;
			}

			const headers = ['Date','Branch Code','Branch Name','Equipment','Location','Task','Status','Performed By','Verified By','Next Due','Notes'];
			let csv = headers.join(',') + '\n';

			records.forEach(rec => {
				const row = [
					rec.date || '',
					rec.BranchCode || '',
					rec.BranchName || '',
					rec.equipment || '',
					rec.location || '',
					rec.task || '',
					rec.status || '',
					rec.performedBy || '',
					rec.verifiedBy || '',
					rec.nextDue || '',
					rec.notes || ''
				];
				csv += row.map(field => `"${(field+'').replace(/"/g,'""')}"`).join(',') + '\n';
			});

			const blob = new Blob([csv], {type:'text/csv'});
			const url = URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `pm_records_${new Date().toISOString().split('T')[0]}.csv`;
			a.click();
			URL.revokeObjectURL(url);
		}

		function printAsPDF(){
			const selectedMonth = document.getElementById('month-filter').value;
			const dateFrom = document.getElementById('date-from').value;
			const dateTo = document.getElementById('date-to').value;
			const countText = document.getElementById('filter-info').textContent;

			let filterLabel = 'All Records';
			if (selectedMonth) filterLabel = formatMonthYear(selectedMonth);
			else if (dateFrom || dateTo) filterLabel = (dateFrom ? 'From: ' + dateFrom : '') + (dateFrom && dateTo ? '  ' : '') + (dateTo ? 'To: ' + dateTo : '');

			document.getElementById('ph-filter').textContent = 'Filter: ' + filterLabel;
			document.getElementById('ph-count').textContent = countText;
			document.getElementById('ph-date').textContent = 'Printed: ' + new Date().toLocaleDateString('en-US', {year:'numeric',month:'long',day:'numeric'});

			window.print();
		}

		async function openPreventiveMaintenanceRecord(){
			await renderTable();
			const records = await loadRecords();
			if (records.length > 0) {
				alert(`Loaded ${records.length} preventive maintenance record(s).`);
			} else {
				alert('No records found. Please add records in the main app first.');
			}
		}

		function openBackupFile(){
			const fileInput = document.getElementById('backup-file-input');
			fileInput.value = ''; // Clear previous selection
			fileInput.onchange = handleFileSelect;
			fileInput.click();
		}

		async function handleFileSelect(event){
			const file = event.target.files[0];
			if(!file) return;

			const reader = new FileReader();
			const fileName = file.name.toLowerCase();

			reader.onload = async function(e){
				try {
					let newRecords = [];

					if(fileName.endsWith('.json')){
						const data = JSON.parse(e.target.result);
						if(Array.isArray(data)){
							newRecords = data;
						} else {
							alert('Invalid JSON format. Expected an array of records.');
							return;
						}
					} else if(fileName.endsWith('.csv')){
						const csv = e.target.result;
						newRecords = parseCSV(csv);
						if(newRecords.length === 0){
							alert('No records found in CSV file.');
							return;
						}
					} else {
						alert('Please select a JSON or CSV file.');
						return;
					}

					await mergeAndPersist(newRecords);
				} catch(err){
					alert('Error processing file: ' + err.message);
				}
			};

			reader.readAsText(file);
		}

		function parseCSV(csv){
			const lines = csv.split(/\r\n|\n/).filter(line => line.trim() !== '');
			if(lines.length < 1) return [];

			// Normalize headers
			const rawHeaders = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
			const headerMap = {
				'date': 'date',
				'branch code': 'BranchCode',
				'branch name': 'BranchName',
				'equipment': 'equipment',
				'location': 'location',
				'task': 'task',
				'status': 'status',
				'performed by': 'performedBy',
				'verified by': 'verifiedBy',
				'next due': 'nextDue',
				'notes': 'notes'
			};

			const headers = rawHeaders.map(h => h.toLowerCase());
			const records = [];

			for(let i = 1; i < lines.length; i++){
				const values = parseCSVLine(lines[i]);
				if(values.length === 0) continue;

				const record = {};
				headers.forEach((header, idx) => {
					const key = headerMap[header];
					if(key && idx < values.length){
						record[key] = values[idx].trim();
					}
				});
				records.push(record);
			}

			return records;
		}

		function parseCSVLine(line){
			const result = [];
			let current = '';
			let inQuotes = false;

			for(let i = 0; i < line.length; i++){
				const char = line[i];
				const nextChar = i + 1 < line.length ? line[i + 1] : null;

				if(char === '"'){
					if(inQuotes && nextChar === '"'){
						current += '"';
						i++;
					} else {
						inQuotes = !inQuotes;
					}
				} else if(char === ',' && !inQuotes){
					result.push(current.trim());
					current = '';
				} else {
					current += char;
				}
			}
			result.push(current.trim());
			return result;
		}

		async function mergeAndPersist(newRecords){
			if(!Array.isArray(newRecords) || newRecords.length === 0){
				alert('No valid records to import.');
				return;
			}

			const existing = await loadRecords();
			const merged = [...existing];

			let addedCount = 0;
			newRecords.forEach(newRec => {
				const isDup = existing.some(e =>
					e.date === newRec.date &&
					e.BranchCode === newRec.BranchCode &&
					e.equipment === newRec.equipment &&
					e.task === newRec.task &&
					e.performedBy === newRec.performedBy
				);

				if(!isDup){
					merged.push(newRec);
					addedCount++;
				}
			});

			await saveRecords(merged);
			alert(`Import complete! Added ${addedCount} new record(s). Total records: ${merged.length}`);
			await renderTable();
		}

		// Initialize
		async function init() {
			console.log('Initializing pm_records...');
			await populateFilterOptions();
			await renderTable();
			console.log('Initialization complete');
		}

		// Wait for DOM to be ready
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	</script>
	<script src="hangar-theme.js"></script>
<script src="theme-loader.js"></script>
<script>
	function checkAdminForBackupBtn() {
		const firebaseUser = firebase.auth().currentUser;

		if (firebaseUser) {
			console.log('pm_records: Firebase user email:', firebaseUser.email);

			firebaseUser.getIdTokenResult(true).then(function(idTokenResult) {
				console.log('pm_records: Token claims:', idTokenResult.claims);
				const isAdmin = idTokenResult.claims.admin === true;
				console.log('pm_records: Is admin:', isAdmin);

				if (isAdmin) {
					const btn = document.getElementById('open-backup-btn');
					if (btn) btn.style.display = '';
				}
			}).catch(function(err) {
				console.error('pm_records: Error checking admin status:', err);
			});
		} else {
			console.log('pm_records: Firebase user not ready, retrying...');
			setTimeout(checkAdminForBackupBtn, 500);
		}
	}

	// Wait for Firebase to initialize before checking admin status
	setTimeout(checkAdminForBackupBtn, 2000);
</script>
</body>
</html>
