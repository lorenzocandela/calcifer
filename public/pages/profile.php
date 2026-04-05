<div style="display:flex;flex-direction:column;gap:14px;padding:20px 16px 0;animation:pageIn .26s cubic-bezier(.16,1,.3,1) both">
    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:8px 0">
        <label for="avatar-upload" style="cursor:pointer;position:relative;display:block">
        <div id="profile-avatar" style="width:76px;height:76px;border-radius:50%;background:#18181b;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:rgba(255,255,255,.7);text-transform:uppercase;overflow:hidden;position:relative;">—</div>
        <div style="position:absolute;bottom:0;left:0;right:0;height:76px;background:rgba(0,0,0,.52);display:flex;align-items:center;justify-content:center;border-radius:100%">
            <span class="ms ms-sm" style="color:#fff;font-size:13px">photo_camera</span>
        </div>
        </label>
        <input type="file" id="avatar-upload" accept="image/*" style="display:none">
        <div style="text-align:center">
        <div id="profile-username" style="font-size:1.375rem;font-weight:800;letter-spacing:-.03em">—</div>
        <div id="profile-email" style="font-size:.875rem;color:var(--ink-2);margin-top:3px;font-weight:500">—</div>
        </div>
    </div>

    <div id="profile-score-card" style="background:var(--surface);border:1.5px solid var(--border);border-radius:22px;overflow:hidden;padding:20px;">
        <div class="skeleton" style="height:220px"></div>
    </div>

    <div style="background: #ffffff54;border: 1.5px solid #d4d4d861;border-radius:22px;overflow:hidden;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-bottom:1px solid #ececee">
            <div style="display:flex;align-items:center;gap:10px;color:var(--ink-2)">
                <span class="ms ms-sm">calendar_today</span>
                <span style="font-size:.9375rem;font-weight:600">Membro dal</span>
            </div>
            <span id="profile-joined" style="font-size:.875rem;font-weight:800;color:var(--ink-1)">—</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 18px">
            <div style="display:flex;align-items:center;gap:10px;color:var(--ink-2)">
                <span class="ms ms-sm">verified_user</span>
                <span style="font-size:.9375rem;font-weight:600">Account</span>
            </div>
            <span style="font-size:.875rem;font-weight:800;padding:4px 10px;border-radius:999px;background:var(--green-soft);color:var(--green)">Attivo</span>
        </div>
    </div>

    <button id="profile-logout-btn" style="width:100%; padding:14px; border-radius:var(--r-pill); border:1.5px solid var(--red-soft); background: var(--red-soft); color:var(--red); font-size:.9375rem; font-weight:800; font-family:var(--sans); cursor:pointer; letter-spacing:-.01em; display:flex; align-items:center; justify-content:center;gap:8px; transition: background .13s;" onmouseenter="this.style.background='var(--red-soft)'" onmouseleave="this.style.background='transparent'">
        <span class="ms ms-sm">logout</span> Esci
    </button>
</div>