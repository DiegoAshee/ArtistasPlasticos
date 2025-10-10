(function(){
  // Helper: safe dataset retrieval
  const getDataset = (el, key, fallback = null) => (el && el.dataset && el.dataset[key]) ? el.dataset[key] : fallback;

  // Core actions (use endpoints from data attributes when available)
  function sendJson(endpoint, body = null, options = {}){
    const fetchOptions = Object.assign({ method: body ? 'POST' : 'GET', headers: {'Content-Type':'application/json'}, credentials: 'same-origin', body: body ? JSON.stringify(body) : undefined }, options);
    return fetch(endpoint, fetchOptions).then(r => r.json().catch(() => ({success: r.ok}))).catch(()=>({success:false}));
  }

  function beaconPost(endpoint, payloadObj){
    if(!endpoint) return false;
    const payload = JSON.stringify(payloadObj || {});
    if (navigator && typeof navigator.sendBeacon === 'function') {
      try {
        const blob = new Blob([payload], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
        return true;
      } catch (e) {
        // fallthrough
      }
    }
    // fallback non-blocking
    try{ fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/json'}, body: payload, keepalive:true, credentials:'same-origin'}).catch(()=>{}); }catch(e){}
    return true;
  }

  document.addEventListener('DOMContentLoaded', function(){
    // ----- Topbar dropdown behavior -----
    const notificationBell = document.getElementById('notificationBell');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationContainer = document.querySelector('.notification-container');

    const endpointMarkReadTop = getDataset(notificationContainer, 'endpointMarkRead');
    const endpointMarkAllTop = getDataset(notificationContainer, 'endpointMarkAllRead');

    if(notificationBell && notificationDropdown){
      notificationBell.addEventListener('click', function(e){
        e.stopPropagation();
        notificationDropdown.classList.toggle('show');
        const userDropdown = document.getElementById('userDropdown');
        if(userDropdown) userDropdown.classList.remove('show');
      });

      document.addEventListener('click', function(e){
        if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
          notificationDropdown.classList.remove('show');
        }
      });

      notificationBell.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          notificationDropdown.classList.toggle('show');
        }
      });

      // Delegación: manejar clicks en notification-item anchors para marcar como leídas
      notificationDropdown.addEventListener('click', function(e){
        const item = e.target.closest('.notification-item');
        if(!item) return;
        const nid = item.getAttribute('data-id');
        if(!nid) return;
        // mark as read (use beacon if possible) but don't block navigation
        if(endpointMarkReadTop){
          beaconPost(endpointMarkReadTop, { id: nid });
        }
      });

      // Mark all handler in dropdown
      const markAllBtn = notificationDropdown.querySelector('.mark-all-read');
      if(markAllBtn){
        markAllBtn.addEventListener('click', function(e){
          e.preventDefault();
          // optimistic UI
          notificationDropdown.querySelectorAll('.notification-item.unread').forEach(it=>{ it.classList.remove('unread'); const ind = it.querySelector('.unread-indicator'); if(ind) ind.remove(); });
          const badge = document.querySelector('.notification-badge'); if(badge) badge.remove();
          if(endpointMarkAllTop){ beaconPost(endpointMarkAllTop, {}); }
          // hide button
          markAllBtn.style.display = 'none';
        });
      }
    }

    // ----- Notifications page behavior (table) -----
    const pageActions = document.querySelector('.page-actions');
    const tableContainer = document.querySelector('.table-container');

    const endpointMark = pageActions ? (pageActions.dataset.endpointMarkRead || endpointMarkReadTop) : endpointMarkReadTop;
    const endpointDelete = pageActions ? pageActions.dataset.endpointDelete : null;
    const endpointMarkAll = pageActions ? pageActions.dataset.endpointMarkAll : (pageActions && pageActions.dataset.endpointMarkAllRead) || null;

    // helper ajax functions for this page
    async function markRead(id){
      if(!endpointMark) return {success:false};
      try{ const res = await sendJson(endpointMark, { id }); return res; } catch(e){ return {success:false}; }
    }
    async function deleteNotif(id){ if(!endpointDelete) return {success:false}; try{ const r = await sendJson(endpointDelete, { id }); return r; }catch(e){return {success:false}} }
    async function markAll(){ if(!endpointMarkAll) return {success:false}; try{ const r = await sendJson(endpointMarkAll); return r; }catch(e){return {success:false}} }

    // Delegación para botones en la tabla
    document.addEventListener('click', async function(e){
      const markBtn = e.target.closest('button.mark-read-btn');
      if(markBtn){
        const id = markBtn.getAttribute('data-id');
        markBtn.disabled = true;
        const r = await markRead(id);
        markBtn.disabled = false;
        if(r.success){ const tr = markBtn.closest('tr'); if(tr){ tr.classList.remove('unread'); const status = tr.querySelector('.status'); if(status) status.textContent = 'Sí'; markBtn.remove(); } } else alert('Error marcando como leída');
        return;
      }

      const delBtn = e.target.closest('button.delete-btn');
      if(delBtn){
        const id = delBtn.getAttribute('data-id');
        if(!confirm('¿Eliminar esta notificación?')) return;
        delBtn.disabled = true;
        const r = await deleteNotif(id);
        delBtn.disabled = false;
        if(r.success){ const tr = delBtn.closest('tr'); if(tr) tr.remove(); } else alert('Error eliminando notificación');
        return;
      }
    });

    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if(markAllReadBtn){
      markAllReadBtn.addEventListener('click', async ()=>{
        if(!confirm('Marcar todas las notificaciones como leídas?')) return;
        markAllReadBtn.disabled = true;
        const r = await markAll();
        markAllReadBtn.disabled = false;
        if(r.success){ document.querySelectorAll('tr.unread').forEach(tr=>{ tr.classList.remove('unread'); const m = tr.querySelector('button.mark-read-btn'); if(m) m.remove(); const status = tr.querySelector('.status'); if(status) status.textContent = 'Sí'; }); } else alert('Error al marcar todas como leídas');
      });
    }

    // Focus from topbar (notifications page)
    (function handleFocusAndOverlay(){
      try{
        const params = new URLSearchParams(window.location.search);
        const focus = params.get('focus');
        if(focus){ const row = document.querySelector(`tr[data-id="${focus}"]`); if(row){ row.style.transition='background-color .6s ease'; row.style.backgroundColor='#fff3cd'; row.scrollIntoView({behavior:'smooth', block:'center'}); setTimeout(()=>row.style.backgroundColor='',3000); } }

        // Safety: ocultar overlay en escritorio si está visible
        if(window.innerWidth>=1025){ const ov = document.getElementById('overlay'); if(ov){ ov.classList.remove('show'); ov.style.display='none'; ov.style.opacity='0'; ov.style.visibility='hidden'; ov.style.pointerEvents='none'; } }
      }catch(e){}
    })();

  });
})();
