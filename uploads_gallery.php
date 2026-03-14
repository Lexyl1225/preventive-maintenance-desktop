<?php
// Simple gallery viewer for uploaded images/videos. Controls marked with .no-print
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Uploads Gallery</title>
  <link rel="stylesheet" href="hangar-theme.css">
  <style>
    body{font-family:Segoe UI,Arial;margin:16px;color:#111}
    .toolbar{display:flex;gap:8px;align-items:center;margin-bottom:12px}
    input[type=search]{padding:8px;border:1px solid #ddd;border-radius:6px}
      .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px}
    /* Make card a column so actions can stick to the bottom */
      .card{background:#fff;border-radius:8px;padding:10px;border:1px solid #eee;display:flex;flex-direction:column;min-height:300px;overflow:visible;justify-content:space-between;position:relative;padding-bottom:64px}
    .media-wrap{flex:0 0 auto}
      .thumb{width:100%;height:180px;object-fit:cover;border-radius:6px;background:#000;display:block}
    .meta{font-size:12px;color:#555;margin-top:6px;flex:1 1 auto}
    .no-print{display:inline-block}
    @media print{ .no-print{display:none !important} }
    /* Actions bar anchored to bottom of card */
    .actions{margin-top:0;flex:0 0 auto;display:flex;gap:6px;align-items:center;justify-content:flex-start;position:absolute;left:10px;right:10px;bottom:12px}
    .actions button{margin:0}
    /* Theme-safe button styles to ensure visibility across themes and centered text */
    .actions button{background:var(--card-btn-bg,#f3f3f3) !important;color:var(--card-btn-color,#111) !important;border:1px solid rgba(0,0,0,0.08) !important;padding:6px 8px;border-radius:6px !important;display:inline-flex;align-items:center;justify-content:center;text-align:center;min-width:72px}
    .actions button.secondary{background:transparent !important;border:1px solid rgba(0,0,0,0.06) !important}
    /* Specific action colors */
    .actions button.btn-open, .actions button.btn-assoc{background:#b7f3c5 !important;color:#034d26 !important;border-color:rgba(3,77,38,0.08) !important}
    .actions button.btn-delete{background:#ef4444 !important;color:#fff !important;border-color:rgba(0,0,0,0.12) !important}
    .actions button.btn-restore{background:#0b78d1 !important;color:#fff !important;border-color:rgba(0,0,0,0.08) !important}
    video.thumb{background:#000}
    .highlight{box-shadow:0 0 0 3px rgba(11,120,209,0.5);border-radius:6px}
    /* Larger card size for deleted items so Restore button fits */
    .card.deleted{min-height:380px;background:#fff6f6;padding-bottom:90px}
    .card.deleted .thumb{height:200px}
    .card.deleted .actions button{min-width:88px}
  </style>
  <script src="hangar-theme.js"></script>
  <script src="theme-loader.js"></script>
</head>
<body>
  <h2>Uploaded Files</h2>
  <div class="toolbar no-print">
    <button id="btn-back-home" class="small">Back to Home</button>
    <input id="q" type="search" placeholder="Filter by Branch Code, file name or task" />
    <button id="refresh" class="small">Refresh</button>
    <button id="admin-toggle" class="small" style="margin-left:8px">Admin</button>
    <button id="show-trash" class="small" style="margin-left:8px">Show Trash</button>
    <button id="bulk-restore" class="small" style="margin-left:8px;display:none">Bulk Restore</button>
    <button id="bulk-delete" class="small" style="margin-left:8px;display:none">Bulk Permanent Delete</button>
  </div>
  <div id="grid" class="grid"></div>

  <template id="card-tpl">
    <div class="card">
      <div class="media-wrap"></div>
      <div class="meta"></div>
      <div class="actions no-print"></div>
    </div>
  </template>

  <script>
    // show-trash flag (use this instead of reading dataset everywhere)
    let SHOW_TRASH = false;
    // initialize from button state (if present)
    const _showBtn = document.getElementById('show-trash');
    const _bulkRestoreInit = document.getElementById('bulk-restore');
    if(_showBtn){
      SHOW_TRASH = (_showBtn.dataset && _showBtn.dataset.show === '1');
      _showBtn.dataset.show = SHOW_TRASH ? '1' : '0';
      _showBtn.textContent = SHOW_TRASH ? 'Hide Trash' : 'Show Trash';
      if(_bulkRestoreInit) _bulkRestoreInit.style.display = SHOW_TRASH ? 'inline-block' : 'none';
    }

    async function load(){
      const url = 'api/uploads.php?show_deleted=' + (SHOW_TRASH ? '1' : '0');
      const res = await fetch(url, { cache: 'no-store' });
      if(!res.ok) throw new Error('Failed to load');
      let rows = await res.json();
      if(!Array.isArray(rows)) return [];
      // If SHOW_TRASH active, filter to deleted items; otherwise exclude deleted items
      if(SHOW_TRASH) rows = rows.filter(r => parseInt(r.deleted) === 1);
      else rows = rows.filter(r => parseInt(r.deleted) === 0);
      return rows;
    }
    function escape(s){ return String(s||''); }

    async function render(){
      const all = await load();
      const q = document.getElementById('q').value.toLowerCase();
      const grid = document.getElementById('grid'); grid.innerHTML = '';
      for(const r of all){
        const tpl = document.getElementById('card-tpl');
        const el = tpl.content.cloneNode(true);
        const mediaWrap = el.querySelector('.media-wrap');
        const meta = el.querySelector('.meta');
        const actions = el.querySelector('.actions');
        const checkbox = document.createElement('input'); checkbox.type='checkbox'; checkbox.className='selbox no-print'; checkbox.style.marginRight='8px';

        const ext = (r.file_name||'').split('.').pop().toLowerCase();
        if(['mp4','webm','mov','avi','mkv'].includes(ext)){
          const v = document.createElement('video'); v.className='thumb'; v.src = r.file_path; v.controls=true; mediaWrap.appendChild(v);
        } else {
          const img = document.createElement('img'); img.className='thumb'; img.src = r.file_path; mediaWrap.appendChild(img);
        }

        meta.innerHTML = `<strong>${escape(r.file_name)}</strong><div>${escape(r.BranchCode||r.BranchName||'')}</div><div>${escape(r.record_task||'')}</div><div style="font-size:11px;color:#888">${escape(r.uploaded_at)}</div>`;

        // actions: open, associate, delete / restore
        const openBtn = document.createElement('button'); openBtn.textContent='Open'; openBtn.className='small btn-open'; openBtn.addEventListener('click', ()=>{ window.open(r.file_path,'_blank'); });
        const assocBtn = document.createElement('button'); assocBtn.textContent = r.record_id ? 'Change Link' : 'Associate'; assocBtn.className='small btn-assoc';
        assocBtn.addEventListener('click', ()=> openAssociateModal(r));
        const delBtn = document.createElement('button'); delBtn.textContent='Delete'; delBtn.className='small btn-delete';
        delBtn.addEventListener('click', async ()=>{
          // Ask user whether permanent delete (admin) or soft-delete
          const perm = confirm('Click OK to permanently delete (admins only). Click Cancel to perform a soft-delete (non-admin).');
          if(perm){
            const token = prompt('Enter admin token for permanent delete:');
            if(!token) return alert('Admin token required for permanent delete.');
            try{
              const resp = await fetch('api/uploads.php?id='+encodeURIComponent(r.id), { method: 'DELETE', headers: { 'X-Admin-Token': token } });
              const j = await resp.json();
              if(resp.ok && j.ok){ render(); }
              else { alert('Permanent delete failed: '+(j.error||'unknown')); }
            }catch(e){ alert('Permanent delete failed: '+e.message); }
          } else {
            // soft-delete via POST action
            try{
              const resp = await fetch('api/uploads.php', { method: 'POST', headers: {'Content-Type':'application/json','X-User': 'web_user'}, body: JSON.stringify({ action: 'soft_delete', id: r.id }) });
              const j = await resp.json();
              if(resp.ok && j.ok) render(); else alert('Soft-delete failed: '+(j.error||'unknown'));
            }catch(e){ alert('Soft-delete failed: '+e.message); }
          }
        });

        const restoreBtn = document.createElement('button'); restoreBtn.textContent='Restore'; restoreBtn.className='small btn-restore';
        restoreBtn.addEventListener('click', async ()=>{
          if(!confirm('Restore this uploaded file?')) return;
          try{
            const resp = await fetch('api/uploads.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: 'restore', id: r.id }) });
            const j = await resp.json();
            if(resp.ok && j.ok) render(); else alert('Restore failed: '+(j.error||'unknown'));
          }catch(e){ alert('Restore failed: '+e.message); }
        });

        actions.appendChild(openBtn); actions.appendChild(assocBtn); actions.appendChild(delBtn); if(r.deleted) actions.appendChild(restoreBtn);

        const card = el.querySelector('.card');
        // mark card with upload id and deleted state so we can style it
        try{ card.dataset.uploadId = r.id; }catch(e){}
        if(r.deleted){ card.classList.add('deleted'); }
        // attach checkbox to card for admin selection
        card.prepend(checkbox);
        // filtering
        const hay = (r.file_name+' '+(r.BranchCode||'')+' '+(r.record_task||'')).toLowerCase();
        // If record_id is provided in URL, show only matching uploads for that record
        const urlParams = new URLSearchParams(window.location.search);
        const filterRecordId = urlParams.get('record_id');
        if(filterRecordId){
          if(String(r.record_id) !== String(filterRecordId)) continue;
        } else {
          if(q && hay.indexOf(q) === -1) continue;
        }

        grid.appendChild(card);
        // highlight if matches record_id
        if(filterRecordId && String(r.record_id) === String(filterRecordId)){
          const node = card; node.classList.add('highlight'); setTimeout(()=>node.classList.remove('highlight'), 4000);
        }
      }

      // Admin UI
      function isAdmin() { return !!sessionStorage.getItem('epm_admin_token'); }
      document.getElementById('admin-toggle').addEventListener('click', ()=>{
        const bulkRestore = document.getElementById('bulk-restore');
        const bulkDelete = document.getElementById('bulk-delete');
        if(isAdmin()){
          sessionStorage.removeItem('epm_admin_token'); alert('Admin logged out'); if(bulkRestore) bulkRestore.style.display='none'; if(bulkDelete) bulkDelete.style.display='none'; render(); return;
        }
        const t = prompt('Enter admin token to enable admin controls:'); if(!t) return; sessionStorage.setItem('epm_admin_token', t); alert('Admin enabled'); if(bulkRestore) bulkRestore.style.display='inline-block'; if(bulkDelete) bulkDelete.style.display='inline-block'; render();
      });

      // Show/Hide Trash toggle: use SHOW_TRASH flag
      const showBtn = document.getElementById('show-trash');
      if(showBtn){
        const bulkRestore = document.getElementById('bulk-restore');
        showBtn.addEventListener('click', ()=>{
          SHOW_TRASH = !SHOW_TRASH;
          showBtn.dataset.show = SHOW_TRASH ? '1' : '0';
          showBtn.textContent = SHOW_TRASH ? 'Hide Trash' : 'Show Trash';
          // show bulk-restore when viewing trash (restore doesn't need admin)
          if(bulkRestore) bulkRestore.style.display = SHOW_TRASH ? 'inline-block' : 'none';
          render();
        });
      }

      function collectSelectedIds(){ const boxes = Array.from(document.querySelectorAll('.selbox')); return boxes.filter(b=>b.checked).map(b=>parseInt(b.closest('.card').dataset.uploadId)); }

      document.getElementById('bulk-restore').addEventListener('click', async ()=>{
        const ids = collectSelectedIds(); if(ids.length===0) return alert('No items selected');
        if(!confirm('Restore '+ids.length+' item(s)?')) return;
        const resp = await fetch('api/uploads.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'bulk_restore', ids: ids }) }); const j = await resp.json(); if(resp.ok && j.ok) render(); else alert('Failed: '+(j.error||'unknown'));
      });

      document.getElementById('bulk-delete').addEventListener('click', async ()=>{
        const ids = collectSelectedIds(); if(ids.length===0) return alert('No items selected');
        if(!confirm('Permanently delete '+ids.length+' item(s)? This moves files to uploads/trash.')) return;
        const token = sessionStorage.getItem('epm_admin_token') || prompt('Enter admin token:'); if(!token) return alert('Admin token required');
        try{
          const resp = await fetch('api/uploads.php', { method:'POST', headers:{ 'Content-Type':'application/json','X-Admin-Token': token }, body: JSON.stringify({ action:'bulk_permanent_delete', ids: ids }) });
          const j = await resp.json(); if(resp.ok && j.ok){ render(); } else { alert('Failed: '+(j.error||'unknown')); }
        }catch(e){ alert('Error: '+e.message); }
      });
    }

    function openAssociateModal(upload){
      const m = document.createElement('div'); m.style.position='fixed'; m.style.left=0; m.style.top=0; m.style.right=0; m.style.bottom=0; m.style.display='flex'; m.style.alignItems='center'; m.style.justifyContent='center'; m.style.background='rgba(0,0,0,0.5)'; m.style.zIndex=3000;
      const box = document.createElement('div'); box.style.background='#fff'; box.style.padding='12px'; box.style.borderRadius='8px'; box.style.width='480px';
      const title = document.createElement('div'); title.textContent = 'Associate uploaded file to a maintenance record'; title.style.fontWeight='600'; title.style.marginBottom='8px';
      box.appendChild(title);
      const list = document.createElement('div'); list.style.maxHeight='300px'; list.style.overflow='auto'; list.style.border='1px solid #eee'; list.style.padding='8px';
      box.appendChild(list);
      const close = document.createElement('button'); close.textContent='Close'; close.className='small secondary'; close.style.marginTop='8px'; close.addEventListener('click', ()=> m.remove());
      box.appendChild(close);
      m.appendChild(box); document.body.appendChild(m);

      // load recent records
      fetch('api/maintenance.php').then(r=>r.json()).then(records=>{
        if(!Array.isArray(records) || records.length===0){ list.textContent='No records available.'; return; }
        for(const rec of records){
          const row = document.createElement('div'); row.style.display='flex'; row.style.justifyContent='space-between'; row.style.padding='6px 0'; row.style.borderBottom='1px dashed #f0f0f0';
          const info = document.createElement('div'); info.innerHTML = `<div style="font-weight:600">${escape(rec.BranchName||rec.BranchCode||'')}</div><div style="font-size:12px;color:#666">${escape(rec.task||'')} • ${escape(rec.date||'')}</div>`;
          const btn = document.createElement('button'); btn.textContent='Link'; btn.className='small'; btn.addEventListener('click', ()=>{
            // POST to api/uploads.php to set record_id (include action)
            fetch('api/uploads.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action: 'associate', id: upload.id, record_id: rec.id }) })
              .then(res=>res.json())
              .then((j)=>{
                if(j && j.ok){
                  // close modal and refresh gallery
                  m.remove(); render();
                  // notify opener (pm_records) to refresh view mapping if available
                  try{ if(window.opener && typeof window.opener.renderTable === 'function') window.opener.renderTable(); }catch(e){ /* ignore */ }
                } else {
                  alert('Failed to link: '+(j && j.error ? j.error : 'unknown'));
                }
              }).catch(err=>{ alert('Link failed: '+err.message); });
          });
          row.appendChild(info); row.appendChild(btn); list.appendChild(row);
        }
      }).catch(e=>{ list.textContent='Failed to load records'; });
    }

    document.getElementById('refresh').addEventListener('click', render);
    document.getElementById('q').addEventListener('input', render);
    // Back to Home button
    const backHomeBtn = document.getElementById('btn-back-home');
    if(backHomeBtn) backHomeBtn.addEventListener('click', ()=>{ window.location.href = 'index.php'; });
    render();
  </script>
</body>
</html>
