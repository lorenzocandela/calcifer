import { Auth, userStore, Notifications } from '/js/api.js';

// loader
export function showLoader() {
    const el = document.getElementById('loader');
    if (!el) return;
    el.style.opacity = '1';
    el.style.visibility = 'visible';
    el.style.pointerEvents = 'auto';
}
export function hideLoader() {
    const el = document.getElementById('loader');
    if (!el) return;
    el.style.opacity = '0';
    el.style.visibility = 'hidden';
    el.style.pointerEvents = 'none';
}

hideLoader();

// toast
export function toast(message, type = 'default', duration = 3200) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { positive: 'check_circle', negative: 'error', warning: 'warning', default: 'info' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `<span class="ms ms-sm">${icons[type] ?? 'info'}</span>${message}`;
    container.appendChild(el);

    setTimeout(() => {
        el.style.animation = 'toastIn .22s var(--spring) reverse both';
        setTimeout(() => el.remove(), 220);
    }, duration);
}

// fmt
export const fmt = {
    currency: (n) => new Intl.NumberFormat('it-IT', { style: 'currency', currency: 'EUR' }).format(n),
    percent:  (n) => `${parseFloat(n).toFixed(2)}%`,
    date:     (d) => new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d)),
    dateShort:(d) => new Intl.DateTimeFormat('it-IT', { day: '2-digit', month: 'short' }).format(new Date(d)),
    initials: (s) => (s || '?').slice(0, 2).toUpperCase(),
    relTime:  (d) => {
        const diff = Date.now() - new Date(d).getTime();
        const m = Math.floor(diff / 60000);
        if (m < 1)  return 'ora';
        if (m < 60) return `${m}m fa`;
        const h = Math.floor(m / 60);
        if (h < 24) return `${h}h fa`;
        return fmt.dateShort(d);
    },
};

// nav
function syncNav() {
    const p = new URLSearchParams(location.search).get('p') || 'dashboard';
    document.querySelectorAll('[data-page]').forEach(el => {
        el.classList.toggle('active', el.dataset.page === p);
    });
}

// prfile
function populateUser() {
    const user = userStore.get();
    if (!user) return;
    const initials = fmt.initials(user.username);

    const sideAvatar = document.getElementById('sidebar-avatar');
    const topAvatar  = document.getElementById('topbar-avatar');
    const sideUser   = document.getElementById('sidebar-username');

    if (sideUser) sideUser.textContent = user.username;

    if (sideAvatar) sideAvatar.textContent = initials;

    if (topAvatar) {
        if (user.avatar_url) {
            topAvatar.style.padding = '0';
            topAvatar.style.overflow = 'hidden';
            topAvatar.innerHTML = `<img src="${user.avatar_url}?v=${Date.now()}" style="width:100%;height:100%;object-fit:cover;border-radius:50%" onerror="this.parentElement.textContent='${initials}'">`;
        } else {
            topAvatar.textContent = initials;
        }
    }

    if (!user.avatar_url) {
        import('./api.js').then(({ Users }) => {
            Users.me().then(fresh => {
                if (fresh.avatar_url && topAvatar) {
                    userStore.set(fresh);
                    topAvatar.style.padding = '0';
                    topAvatar.style.overflow = 'hidden';
                    topAvatar.innerHTML = `<img src="${fresh.avatar_url}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
                }
            }).catch(() => {});
        });
    }
}

function bindLogout() {
    document.getElementById('sidebar-logout')?.addEventListener('click', () => Auth.logout());
}

// notifications
let notifModal = null;

function renderNotifModal(notifications) {
    if (notifModal) notifModal.remove();

    const unread = notifications.filter(n => !n.is_read).length;

    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML = `
      <div class="modal" style="gap:0;padding:0;overflow:hidden;max-height:82dvh">

        <div style="flex-shrink:0">
          <div style="width:36px;height:4px;background:#e5e7eb;border-radius:99px;margin:12px auto 0"></div>
          <div style="padding:14px 18px 12px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:8px">
              <span style="font-size:1rem;font-weight:800;letter-spacing:-.025em">Notifiche</span>
              ${unread > 0 ? `<span style="background:var(--accent);color:#fff;font-size:.5625rem;font-weight:800;padding:2px 7px;border-radius:999px">${unread}</span>` : ''}
            </div>
            <div style="display:flex;align-items:center;gap:8px">
              ${notifications.length > 0 ? `<button id="clear-all-notif" style="font-size:.8125rem;font-weight:700;color:var(--ink-3);background:none;border:none;cursor:pointer;padding:4px 8px;border-radius:8px;font-family:var(--sans);transition:color .12s" onmouseenter="this.style.color='var(--accent)'" onmouseleave="this.style.color='var(--ink-3)'">Cancella tutto</button>` : ''}
              <button id="close-notif-btn" style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--surface-3);color:var(--ink-2);cursor:pointer;border:none">
                <span class="ms ms-sm" style="font-size:16px">close</span>
              </button>
            </div>
          </div>
        </div>

        <div style="overflow-y:auto;flex:1;padding:0 12px 16px;display:flex;flex-direction:column;gap:8px" id="notif-list-inner">
          ${notifications.length === 0
            ? `<div style="display:flex;flex-direction:column;align-items:center;padding:48px 20px;color:var(--ink-3);text-align:center">
                 <span class="ms" style="font-size:44px">notifications_none</span>
                 <p style="margin-top:8px;font-size:.875rem;font-weight:600">Tutto letto</p>
               </div>`
            : notifications.map(n => notifCard(n)).join('')}
        </div>
      </div>`;

    backdrop.addEventListener('click', e => { if (e.target === backdrop) closeNotifModal(); });
    backdrop.querySelector('#close-notif-btn').addEventListener('click', closeNotifModal);

    backdrop.querySelector('#clear-all-notif')?.addEventListener('click', async () => {
        try {
            await Notifications.deleteAll();
            const cards = [...backdrop.querySelectorAll('.notif-card')];
            cards.forEach((c, i) => {
                setTimeout(() => {
                    c.style.transition = 'opacity .18s, transform .18s';
                    c.style.opacity = '0'; c.style.transform = 'translateX(16px)';
                    setTimeout(() => c.remove(), 200);
                }, i * 35);
            });
            setTimeout(() => {
                const inner = backdrop.querySelector('#notif-list-inner');
                if (inner) inner.innerHTML = `<div style="display:flex;flex-direction:column;align-items:center;padding:48px 20px;color:var(--ink-3);text-align:center"><span class="ms" style="font-size:44px">notifications_none</span><p style="margin-top:8px;font-size:.875rem;font-weight:600">Tutto cancellato</p></div>`;
                const badge = document.getElementById('notif-badge');
                if (badge) badge.classList.add('hidden');
            }, cards.length * 35 + 250);
        } catch(_) {}
    });

    backdrop.querySelectorAll('.notif-card').forEach(card => {
        card.addEventListener('click', () => {
            const link = card.dataset.link;
            const id   = card.dataset.id;
            if (id) Notifications.markRead(id).catch(() => {});
            closeNotifModal();
            if (link && link !== '#') window.location.href = link;
        });
    });

    document.body.appendChild(backdrop);
    notifModal = backdrop;
}

function notifCard(n) {
    const CFG = {
        new_message:       { icon:'chat',      bg:'#f8fafc', border:'#e2e8f0', iconBg:'var(--accent)',  iconColor:'#fff' },
        new_proposal:      { icon:'handshake', bg:'#fffbeb', border:'#fef3c7', iconBg:'#f59e0b',        iconColor:'#fff' },
        proposal_accepted: { icon:'check',     bg:'#f0fdf4', border:'#dcfce7', iconBg:'#16a34a',        iconColor:'#fff' },
        proposal_rejected: { icon:'close',     bg:'#fff5f5', border:'#fee2e2', iconBg:'#ef4444',        iconColor:'#fff' },
        loan_funded:       { icon:'payments',  bg:'#f0fdf4', border:'#dcfce7', iconBg:'#16a34a',        iconColor:'#fff' },
    }[n.type] ?? { icon:'notifications', bg:'#f8fafc', border:'#e2e8f0', iconBg:'#6b7280', iconColor:'#fff' };

    const isMsg = n.type === 'new_message' && n.image_url;

    const leftEl = isMsg
        ? `<div style="width:42px;height:42px;border-radius:50%;overflow:hidden;position:relative;background:#e4e4e7;flex-shrink:0;display:flex;align-items:center;justify-content:center">
             <span style="font-size:.625rem;font-weight:800;color:#71717a;text-transform:uppercase;position:absolute">${fmt.initials(n.title)}</span>
             <img src="${n.image_url}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="">
           </div>`
        : `<div style="width:42px;height:42px;border-radius:14px;flex-shrink:0;background:${CFG.iconBg};display:flex;align-items:center;justify-content:center">
             <span class="ms ms-sm" style="color:${CFG.iconColor};font-size:20px">${CFG.icon}</span>
           </div>`;

    return `
      <div class="notif-card" data-link="${n.link || ''}" data-id="${n.id}" style="
        background:${n.is_read ? '#f3f3f352' : CFG.bg};
        border:1.5px solid ${n.is_read ? '#f0f0f0' : CFG.border};
        border-radius:18px;padding:14px;
        cursor:pointer;position:relative;
        transition:transform .12s,box-shadow .12s;
      " onmouseenter="this.style.transform='scale(1.008)';this.style.boxShadow='0 2px 12px rgba(0,0,0,.07)'" onmouseleave="this.style.transform='';this.style.boxShadow=''">

        <div style="display:flex;align-items:flex-start;gap:12px">
          ${leftEl}
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px;margin-bottom:3px">
              <span style="font-size:.9375rem;font-weight:${n.is_read ? 600 : 800};color:${n.is_read ? '#71717a' : '#09090b'};letter-spacing:-.01em;line-height:1.3">${n.title}</span>
            </div>
            <div style="font-size:.8125rem;color:#71717a;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${n.body}</div>
          </div>
        </div>

        ${!n.is_read ? `<div style="position:absolute;top:12px;right:12px;width:7px;height:7px;border-radius:50%;background:${CFG.iconBg}"></div>` : ''}
      </div>`;
}

function closeNotifModal() {
    notifModal?.remove();
    notifModal = null;
}

function notifIcon(type) {
    const map = {
        new_proposal:       'handshake',
        proposal_accepted:  'check_circle',
        proposal_rejected:  'cancel',
        new_message:        'chat',
        loan_funded:        'payments',
    };
    return map[type] ?? 'notifications';
}

async function pollNotifications() {
    try {
        const data   = await Notifications.list();
        const unread = data.filter(n => !n.is_read).length;
        const badge  = document.getElementById('notif-badge');
        if (badge) badge.classList.toggle('hidden', unread === 0);

        const topBadge = document.getElementById('topbar-notif-count');
        if (topBadge) topBadge.textContent = unread > 0 ? unread : '';
    } catch (_) {}
}

function bindNotifications() {
    const btn = document.getElementById('notif-btn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        try {
            const data = await Notifications.list();
            renderNotifModal(data);
            Notifications.markRead(null, true).catch(() => {});
            const badge = document.getElementById('notif-badge');
            if (badge) badge.classList.add('hidden');
        } catch (_) {}
    });

    pollNotifications();
    setInterval(pollNotifications, 10000);
}

// init
function init() {
    syncNav();
    populateUser();
    bindLogout();
    bindNotifications();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

// PWA install banner TODO: test iOS per ora android only
let _deferredPrompt = null;
 
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    _deferredPrompt = e;
    showInstallBanner();
});
 
function showInstallBanner() {
    if (document.getElementById('__pwa_banner')) return;
    const p = new URLSearchParams(location.search).get('p') || 'dashboard';
    if (!['dashboard','feed'].includes(p)) return;
 
    const el = document.createElement('div');
    el.id = '__pwa_banner';
    el.style.cssText = `
        position: fixed;
        bottom: 60px;
        left: 16px;
        right: 16px;
        z-index: 180;
        background: rgb(33 33 33);
        color: rgb(255, 255, 255);
        border-radius: 20px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: rgba(0, 0, 0, 0.28) 0px 8px 32px;
        animation: 0.3s cubic-bezier(0.16, 1, 0.3, 1) 0s 1 normal both running fadeUp;
    `;
    el.innerHTML = `
        <div style="width:44px;height:44px;border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <img src="/logo_clean.png?v=${Date.now()}" style="width: 43px;height: auto;border-radius: 10px;">
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:.9375rem;font-weight:800;letter-spacing:-.02em">Installa Calcifer</div>
            <div style="font-size:.75rem;color:rgba(255,255,255,.5);font-weight:500;margin-top:2px">Accesso rapido dalla home</div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
            <button id="__pwa_install" style="padding:9px 16px;border-radius:999px;background:rgba(255,255,255,.12);color:#fff;border:none;font-size:.8125rem;font-weight:700;cursor:pointer;font-family:var(--sans)">Installa</button>
            <button id="__pwa_dismiss" style="width:28px;height:28px;border-radius:50%;background:transparent;color:rgba(255,255,255,.4);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
                <span class="ms ms-sm" style="font-size:16px">close</span>
            </button>
        </div>`;
 
    document.body.appendChild(el);
 
    document.getElementById('__pwa_install').addEventListener('click', async () => {
        if (!_deferredPrompt) return;
        _deferredPrompt.prompt();
        const { outcome } = await _deferredPrompt.userChoice;
        _deferredPrompt = null;
        el.remove();
    });
 
    document.getElementById('__pwa_dismiss').addEventListener('click', () => {
        el.style.animation = 'none';
        el.style.opacity = '0';
        el.style.transform = 'translateY(8px)';
        el.style.transition = 'opacity .2s, transform .2s';
        setTimeout(() => el.remove(), 200);
    });
}