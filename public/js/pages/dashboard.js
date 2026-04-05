import { Users, Dashboard } from '/js/api.js';
import { fmt } from '/js/app.js';

const page = new URLSearchParams(location.search).get('p') || 'dashboard';
if (page === 'dashboard') initDashboard();
if (page === 'profile')   initProfile();

const LEVELS = [
    {
        max: 125,
        img: '1.png',
        label: 'Scintilla',
        desc: 'La tua prima fiamma',
        color: '#f59e0b',
        bg: '#fef3c7',
        shadow: 'rgba(245,158,11,.28)'
    },
    {
        max: 250,
        img: '2.png',
        label: 'Fiammella',
        desc: 'Stai prendendo fuoco',
        color: '#f97316',
        bg: '#ffedd5',
        shadow: 'rgba(249,115,22,.28)'
    },
    {
        max: 375,
        img: '2.png',
        label: 'Fuoco',
        desc: 'Il sistema si fida di te',
        color: '#ef4444',
        bg: '#fee2e2',
        shadow: 'rgba(239,68,68,.28)'
    },
    {
        max: 500,
        img: '1.png',
        label: 'Calcifer',
        desc: 'Credibilità massima',
        color: '#be123c',
        bg: '#ffe4e6',
        shadow: 'rgba(190,18,60,.28)'
    }
];
const getLevel    = s => LEVELS.find(l => s <= l.max) ?? LEVELS.at(-1);
const getLevelIdx = s => LEVELS.findIndex(l => s <= l.max);

function buildGauge(score, cid, sid) {
    const lvl    = getLevel(score);
    const lvlIdx = getLevelIdx(score);
    const prev   = lvlIdx === 0 ? 0 : LEVELS[lvlIdx - 1].max;
    const pct    = Math.round(((score - prev) / (lvl.max - prev)) * 100);
    const toNext = lvl.max - score;

    const el = document.getElementById(cid);
    if (!el) return;

    el.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <span style="font-size:.6875rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)">Credit Score</span>
        </div>

        <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px">
            <img src="/assets/img/${lvl.img}" class="gauge-mascot"
                style="width:86px;height:86px;flex-shrink:0"/>
            <div style="display:flex;flex-direction:column;gap:3px">
                <div style="font-size:3rem;font-weight:900;letter-spacing:-.07em;line-height:1;color:var(--ink-1)">
                ${score}<span style="font-size:1.0625rem;font-weight:500;color:var(--ink-3);margin-left:2px">/500</span>
                </div>
                <div style="font-size:1rem;font-weight:800;letter-spacing:-.02em;color:${lvl.color}">${lvl.label}</div>
                <div style="font-size:.8125rem;font-weight:500;color:var(--ink-2);margin-top: -6px;opacity: .5;">${lvl.desc}</div>
            </div>
        </div>

        ${lvlIdx < LEVELS.length - 1 ? `
        <div style="background:${lvl.bg};border-radius:16px;padding:14px 16px;margin-bottom:20px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                <span style="font-size:.75rem;font-weight:700;color:${lvl.color};opacity:.7">Mancano ${toNext} pt al prossimo livello!</span>
            </div>
            <div style="height:8px;background:rgba(0,0,0,.08);border-radius:999px;overflow:hidden">
                <div id="${sid}-xpbar"
                    style="height:100%;background:${lvl.color};border-radius:999px;width:0;transition:width 1s cubic-bezier(.22,1,.36,1)">
                </div>
            </div>
            <div style="margin-top:6px;text-align:right;font-size:.6875rem;font-weight:600;color:${lvl.color};opacity:.6">${pct}%</div>
        </div>` : `
        <div style="background:${lvl.bg};border-radius: 8px;padding: 12px 16px;text-align:center;margin-bottom: 30px;">
            <span style="font-size:.9375rem;font-weight:800;color:${lvl.color}">Livello massimo raggiunto!</span>
        </div>`}

        <div class="step-indicator">
            ${LEVELS.map((l, i) => {
                const done   = i < lvlIdx;
                const active = i === lvlIdx;
                let dotStyle = '';
                let inner    = '';
                if (done) {
                    dotStyle = `background:${l.color};color:#fff`;
                    inner    = `<span class="ms" style="font-size:15px;font-weight:700">check</span>`;
                } else if (active) {
                    dotStyle = `background:${l.color};color:#fff;border:2.5px solid #ffffff00;transform:scale(1.2);box-shadow:0 4px 14px ${l.shadow}`;
                    inner    = `<span class="ms" style="font-size:13px">local_fire_department</span>`;
                } else {
                    dotStyle = `background:var(--surface-3);color:var(--ink-3)`;
                    inner    = `<span style="font-size:.625rem;font-weight:900">${i + 1}</span>`;
                }
                return `
                    <div class="step-item">
                        <div class="step-dot ${active ? 'active' : ''} ${done ? 'completed' : ''}" style="${dotStyle}">${inner}</div>
                        <span style="font-size:.5rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:${done || active ? l.color : 'var(--ink-3)'}">${l.label}</span>
                        </div>
                        ${i < LEVELS.length - 1 ? `<div class="step-line"><div class="step-line-fill" data-fill="${done ? 100 : 0}" style="background:${done ? l.color : 'transparent'}"></div></div>` : ''}`;
            }).join('')}
        </div>`;

    requestAnimationFrame(() => {
        const bar = document.getElementById(`${sid}-xpbar`);
        if (bar) setTimeout(() => { bar.style.width = pct + '%'; }, 60);
        el.querySelectorAll('.step-line-fill').forEach(fill => {
            const target = fill.dataset.fill;
            if (target === '100') fill.style.width = '100%';
        });
    });
}

async function initDashboard() {
    try {
        const [user, analytics] = await Promise.all([Users.me(), Dashboard.analytics()]);
        const { howl_stats: h, calcifer_stats: c } = analytics;

        document.getElementById('dash-borrowed').textContent       = fmt.currency(h.total_borrowed);
        document.getElementById('dash-invested').textContent       = fmt.currency(c.total_invested);

        document.getElementById('dash-stats').innerHTML = [
            { 
                icon:'request_quote', 
                label:'Richieste',  
                val: h.total_requests 
            },
            { 
                icon:'savings',
                label:'Operazioni',
                val: c.total_investments 
            },
        ].map(s => `
        <div class="dash-stat">
            <div style="display:flex;align-items:center;gap:7px">
                <span class="ms ms-sm" style="color:var(--ink-3)">${s.icon}</span>
                <span style="font-size:.5625rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)">${s.label}</span>
            </div>
            <div style="font-size:1.5rem;font-weight:900;letter-spacing:-.045em">${s.val}</div>
        </div>`).join('');
        buildGauge(user.credit_score, 'dash-score-card', 'dash-segs');
    } catch(e) { console.error(e); }
}

async function initProfile() {
    try {
        const user = await Users.me();
        document.getElementById('profile-username').textContent = '@' + user.username;
        document.getElementById('profile-email').textContent    = user.email;
        document.getElementById('profile-joined').textContent   = fmt.date(user.created_at);

        setAvatar('profile-avatar', user.avatar_url, fmt.initials(user.username));
        buildGauge(user.credit_score, 'profile-score-card', 'profile-segs');

        // upload handler
        document.getElementById('avatar-upload')?.addEventListener('change', async (e) => {
            const file = e.target.files?.[0];
            if (!file) return;

            const maxMB = 8;
            if (file.size > maxMB * 1024 * 1024) {
                toast(`Immagine troppo grande (max ${maxMB}MB)`, 'negative');
                e.target.value = '';
                return;
            }
            const allowed = ['image/jpeg','image/png','image/webp','image/gif'];
            if (!allowed.includes(file.type)) {
                toast('Formato non supportato (JPG, PNG, WEBP)', 'warning');
                e.target.value = '';
                return;
            }
            const fd = new FormData();
            fd.append('avatar', file);
            try {
                const res  = await fetch('/api/avatar.php', {
                    method: 'POST',
                    credentials: 'include',
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('cf_token') || ''}` },
                    body: fd,
                });
                const data = await res.json();
                if (data.url) {
                    setAvatar('profile-avatar', data.url, fmt.initials(user.username));
                    setAvatar('topbar-avatar', data.url, fmt.initials(user.username));
                    toast('Foto aggiornata', 'positive');
                } else {
                    toast(data.error || 'Errore durante il caricamento', 'negative');
                }
            } catch(err) {
                toast('Errore di rete', 'negative');
                console.error(err);
            }
        });

        document.getElementById('profile-logout-btn')?.addEventListener('click', () => {
            import('/js/api.js').then(({ Auth }) => Auth.logout());
        });
    } catch(e) { console.error(e); }
}

function setAvatar(id, url, initials) {
    const el = document.getElementById(id);
    if (!el) return;
    if (url) {
        el.style.padding = '0';
        el.innerHTML = `<img src="${url}" style="width:100%;height:100%;object-fit:cover;border-radius:50%" onerror="this.parentElement.innerHTML='${initials}'">`;
    } else {
        el.textContent = initials;
    }
}