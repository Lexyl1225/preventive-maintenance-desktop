<?php
// Trash viewer: lists soft-deleted uploads and allows restore or admin permanent-delete
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Uploads Trash</title>
  <link rel="stylesheet" href="hangar-theme.css">
  <style>
    body{font-family:Segoe UI,Arial;margin:18px;color:#111}
    h2{margin-bottom:8px}
    .toolbar{display:flex;gap:8px;align-items:center;margin-bottom:12px}
    table{width:100%;border-collapse:collapse}
    th,td{padding:8px;border:1px solid #ddd;text-align:left;font-size:13px}
    .thumb{width:120px;height:70px;object-fit:cover;border-radius:6px}
    .no-print{display:inline-block}
    @media print{ .no-print{display:none !important} }
    button.small{padding:6px 10px}
  </style>
  <script src="hangar-theme.js"></script>
  <script src="theme-loader.js"></script>
</head>
<body>
  <h2>Uploads Trash</h2>
  <div class="toolbar no-print">
    <button id="btn-back" class="small">Back</button>
    <button id="btn-back-home" class="small">Back to Home</button>
    <button onclick="window.open('uploads_gallery.php','_blank')" class="small">Open Gallery</button>
    <button id="refresh" class="small">Refresh</button>
    <button id="bulk-restore" class="small">Bulk Restore</button>
    <button id="bulk-perm-delete" class="small">Bulk Permanent Delete</button>
  </div>

  <div id="table-wrap"></div>

  <script>
    async function loadTrash(){
      const res = await fetch('api/uploads.php?show_deleted=1');
      if(!res.ok) throw new Error('Failed to load');
      const all = await res.json();
      // filter only deleted items
      return (Array.isArray(all) ? all.filter(x => parseInt(x.deleted) === 1) : []);
    }

    function escape(s){ return String(s||''); }

    async function render(){
      const rows = await loadTrash();
      const wrap = document.getElementById('table-wrap');
      if(rows.length === 0){ wrap.innerHTML = '<div style="padding:18px;color:#666">Trash is empty.</div>'; return; }
      let html = '<table><thead><tr><th></th><th>Preview</th><th>File</th><th>Branch</th><th>Task</th><th>Deleted At</th><th>Deleted By</th><th class="no-print">Actions</th></tr></thead><tbody>';
      for(const r of rows){
        html += `<tr data-id="${r.id}">`;
        html += `<td class="no-print"><input type="checkbox" class="sel" /></td>`;
        const ext = (r.file_name||'').split('.').pop().toLowerCase();
        if(['mp4','webm','mov','avi','mkv'].includes(ext)){
          html += `<td><video class="thumb" src="${r.file_path}" muted></video></td>`;
        } else {
          html += `<td><img class="thumb" src="${r.file_path}" /></td>`;
        }
        html += `<td>${escape(r.file_name)}</td>`;
        html += `<td>${escape(r.BranchCode||r.BranchName||'')}</td>`;
        html += `<td>${escape(r.record_task||'')}</td>`;
        html += `<td>${escape(r.deleted_at||'')}</td>`;
        html += `<td>${escape(r.deleted_by||'')}</td>`;
        html += `<td class="no-print"><button class="small" onclick="restore(${r.id})">Restore</button> <button class="small" onclick="permDelete(${r.id})">Permanent Delete</button></td>`;
        html += `</tr>`;
      }
      html += '</tbody></table>';
      wrap.innerHTML = html;
    }

    async function restore(id){
      if(!confirm('Restore this file?')) return;
      const resp = await fetch('api/uploads.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'restore', id: id }) });
      const j = await resp.json(); if(resp.ok && j.ok) render(); else alert('Restore failed: '+(j.error||'unknown'));
    }

    async function permDelete(id){
      if(!confirm('Permanently delete this file? This moves file to uploads/trash and removes DB record.')) return;
      const token = prompt('Enter admin token for permanent delete:'); if(!token) return alert('Admin token required');
      try{
        const resp = await fetch('api/uploads.php?id='+encodeURIComponent(id), { method: 'DELETE', headers: { 'X-Admin-Token': token } });
        const j = await resp.json(); if(resp.ok && j.ok) render(); else alert('Delete failed: '+(j.error||'unknown'));
      }catch(e){ alert('Delete failed: '+e.message); }
    }

    function collectSelected(){ const boxes = Array.from(document.querySelectorAll('.sel')); return boxes.filter(b=>b.checked).map(b=>parseInt(b.closest('tr').dataset.id)); }

    document.getElementById('refresh').addEventListener('click', render);
    // Back buttons
    const backBtn = document.getElementById('btn-back');
    if(backBtn) backBtn.addEventListener('click', ()=>{ try{ history.back(); }catch(e){ /* noop */ } });
    const backHomeBtn = document.getElementById('btn-back-home');
    if(backHomeBtn) backHomeBtn.addEventListener('click', ()=>{ window.location.href = 'index.php'; });
    document.getElementById('bulk-restore').addEventListener('click', async ()=>{
      const ids = collectSelected(); if(ids.length===0) return alert('No items selected'); if(!confirm('Restore '+ids.length+' item(s)?')) return;
      const resp = await fetch('api/uploads.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'bulk_restore', ids: ids }) }); const j = await resp.json(); if(resp.ok && j.ok) render(); else alert('Failed: '+(j.error||'unknown'));
    });

    document.getElementById('bulk-perm-delete').addEventListener('click', async ()=>{
      const ids = collectSelected(); if(ids.length===0) return alert('No items selected'); if(!confirm('Permanently delete '+ids.length+' item(s)?')) return;
      const token = prompt('Enter admin token:'); if(!token) return alert('Admin token required');
      try{
        const resp = await fetch('api/uploads.php', { method:'POST', headers: { 'Content-Type':'application/json','X-Admin-Token': token }, body: JSON.stringify({ action: 'bulk_permanent_delete', ids: ids }) });
        const j = await resp.json(); if(resp.ok && j.ok) render(); else alert('Failed: '+(j.error||'unknown'));
      }catch(e){ alert('Error: '+e.message); }
    });

    // initial
    render();
  </script>
</body>
</html>
