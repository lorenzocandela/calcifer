<style>
    .dash-stat {
        background:var(--surface);
        border:1.5px solid var(--border);
        border-radius:20px;
        padding:16px;
        display:flex;flex-direction:column;gap:6px;
    }
    .score-seg {
        flex:1;height:8px;border-radius:999px;
        background:var(--surface-3);
        transition:background .5s cubic-bezier(.16,1,.3,1);
    }
</style>

<div style="display:flex;flex-direction:column;gap:14px;padding:20px 16px 0;animation:pageIn .26s cubic-bezier(.16,1,.3,1) both">
    <div style="background:#18181b;border-radius:26px;padding:24px;border:1.5px solid rgba(255,255,255,.06);">
        <div style="display:flex;flex-direction:column;gap:20px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px">
                <div style="display:flex;flex-direction:column;gap:5px;padding-right:20px;border-right:1px solid rgba(255,255,255,.08)">
                <div style="font-size:.5625rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.3)">Prestiti ricevuti</div>
                <div id="dash-borrowed" style="font-size:1.575rem;font-weight:900;letter-spacing:-.05em;color:#fff;line-height:1">—</div>
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;padding-left:20px">
                <div style="font-size:.5625rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.3)">Investito</div>
                <div id="dash-invested" style="font-size:1.575rem;font-weight:900;letter-spacing:-.05em;color:#fff;line-height:1">—</div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px" id="dash-stats">
        <div class="dash-stat skeleton" style="height:80px"></div>
        <div class="dash-stat skeleton" style="height:80px"></div>
    </div>

    <div id="dash-score-card" style="background:var(--surface);border:1.5px solid var(--border);border-radius:22px;overflow:hidden;padding:20px;">
        <div class="skeleton" style="height:220px"></div>
    </div>
</div>