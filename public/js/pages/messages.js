import { Chats } from '/js/api.js';
import { fmt, toast } from '/js/app.js';

const list   = document.getElementById('chat-list');
const search = document.getElementById('search-input');
let allChats = [];

async function load() {
    list.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:1px">
        ${[1,2,3].map(() => `
          <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#fff">
            <div class="skeleton" style="width:46px;height:46px;border-radius:50%;flex-shrink:0"></div>
            <div style="flex:1;display:flex;flex-direction:column;gap:7px">
              <div class="skeleton" style="height:13px;width:55%;border-radius:6px"></div>
              <div class="skeleton" style="height:11px;width:78%;border-radius:6px"></div>
            </div>
          </div>`).join('')}
      </div>`;

    try {
        allChats = await Chats.list();
        render();
    } catch(e) {
        list.innerHTML = `<div style="display:flex;flex-direction:column;align-items:center;padding:60px 20px;color:#a1a1aa;text-align:center">
          <span class="ms" style="font-size:44px">forum</span>
          <p style="margin-top:8px;font-size:.875rem;font-weight:600">${e.message}</p>
        </div>`;
    }
}

function render() {
    const q = search.value.trim().toLowerCase();
    const chats = q ? allChats.filter(c => c.other_username.toLowerCase().includes(q)) : allChats;

    if (!chats.length) {
        list.innerHTML = `
          <div style="display:flex;flex-direction:column;align-items:center;padding:60px 20px;color:#a1a1aa;text-align:center">
            <span class="ms" style="font-size:48px">forum</span>
            <p style="margin-top:10px;font-size:.9375rem;font-weight:700;color:#71717a">Nessuna conversazione</p>
            <p style="margin-top:4px;font-size:.8125rem;color:#a1a1aa">Vai alla <a href="/?p=feed" style="color:var(--accent);font-weight:700">Bacheca</a> per iniziare</p>
          </div>`;
        return;
    }

    list.innerHTML = `<div style="display:flex;flex-direction:column;gap:6px">
      ${chats.map((c, i) => buildRow(c, i, chats.length)).join('')}
    </div>`;

    list.querySelectorAll('.chat-row').forEach(row => bindSwipe(row));
}

function chatAvatar(url, ini, size) {
    const base = `width:${size}px;height:${size}px;border-radius:50%;flex-shrink:0;border:1.5px solid var(--border);overflow:hidden;position:relative;background:var(--surface-3);display:flex;align-items:center;justify-content:center`;
    const txt  = `font-size:${Math.round(size*.17)}px;font-weight:800;color:#71717a;text-transform:uppercase`;
    if (url) {
        return `<div style="${base}">
          <span style="${txt};position:absolute">${ini}</span>
          <img src="${url}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="${ini}">
        </div>`;
    }
    return `<div style="${base}"><span style="${txt}">${ini}</span></div>`;
}

function buildRow(c, i, total) {
    const preview = c.last_message || 'Proposta inviata';
    const initial = fmt.initials(c.other_username);

    return `
      <div class="chat-row" data-id="${c.id}" style="position:relative;overflow:hidden;border-radius:20px;background:#fff;border:1.5px solid var(--border)">
        <!-- delete bg -->
        <div class="chat-delete-bg" style="
          position:absolute;right:0;top:0;bottom:0;
          width:80px;
          background:#ef4444;
          display:flex;align-items:center;justify-content:center;
          transform:translateX(100%);
          transition:transform .2s;
          border-radius:0 18px 18px 0;
        ">
          <span class="ms" style="color:#fff;font-size:22px">delete</span>
        </div>

        <a href="/?p=chat&id=${c.id}" class="chat-row-inner" style="
          display:flex;align-items:center;gap:14px;
          padding:14px 16px;
          background:#fff;
          text-decoration:none;color:inherit;
          transition:background .1s;
          position:relative;z-index:1;
          transform:translateX(0);
          will-change:transform;
          border-radius:18px;
        ">
          ${chatAvatar(c.other_avatar, initial, 46)}

          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:baseline;justify-content:space-between;gap:8px">
              <span style="font-size:.9375rem;font-weight:800;letter-spacing:-.02em;color:#09090b">${c.other_username}</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:3px;gap:8px">
              <span style="font-size:.8125rem;color:#71717a;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1">${preview}</span>
              ${c.unread > 0 ? `<span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:var(--accent);color:#fff;font-size:.625rem;font-weight:800;flex-shrink:0">${c.unread}</span>` : ''}
            </div>
            <div style="margin-top:4px;font-size:.6875rem;color:#a1a1aa;font-weight:600">${fmt.currency(c.loan_amount)}</div>
          </div>
        </a>
      </div>`;
}

// swipe to delete TODO: da testare in prod
function bindSwipe(row) {
    const inner  = row.querySelector('.chat-row-inner');
    const bg     = row.querySelector('.chat-delete-bg');
    const chatId = parseInt(row.dataset.id);

    let startX = 0, curX = 0, swiping = false, revealed = false;
    const THRESHOLD = 60;

    function onStart(x) {
        startX = x; curX = 0; swiping = true;
        inner.style.transition = 'none';
    }
    function onMove(x) {
        if (!swiping) return;
        curX = startX - x;
        if (curX < 0) curX = 0;
        const tx = Math.min(curX, 80);
        inner.style.transform = `translateX(-${tx}px)`;
        bg.style.transform    = `translateX(${100 - (tx / 80 * 100)}%)`;
    }
    function onEnd() {
        if (!swiping) return;
        swiping = false;
        inner.style.transition = 'transform .2s var(--ease,cubic-bezier(.25,1,.5,1))';
        bg.style.transition    = 'transform .2s';

        if (curX >= THRESHOLD) {
            inner.style.transform = 'translateX(-80px)';
            bg.style.transform    = 'translateX(0)';
            revealed = true;
        } else {
            inner.style.transform = 'translateX(0)';
            bg.style.transform    = 'translateX(100%)';
            revealed = false;
        }
    }

    inner.addEventListener('touchstart', e => onStart(e.touches[0].clientX), { passive: true });
    inner.addEventListener('touchmove',  e => onMove(e.touches[0].clientX),  { passive: true });
    inner.addEventListener('touchend',   onEnd);

    bg.addEventListener('click', async () => {
        row.style.transition = 'opacity .2s, max-height .3s';
        row.style.opacity    = '0';
        row.style.maxHeight  = row.offsetHeight + 'px';
        requestAnimationFrame(() => { row.style.maxHeight = '0'; row.style.overflow = 'hidden'; });

        try {
            await Chats.delete(chatId);
            allChats = allChats.filter(c => c.id !== chatId);
            setTimeout(() => render(), 300);
        } catch(e) {
            toast(e.message || 'Errore', 'negative');
            row.style.opacity = '1'; row.style.maxHeight = '';
        }
    });

    document.addEventListener('click', e => {
        if (revealed && !row.contains(e.target)) {
            inner.style.transition = 'transform .2s';
            bg.style.transition    = 'transform .2s';
            inner.style.transform  = 'translateX(0)';
            bg.style.transform     = 'translateX(100%)';
            revealed = false;
        }
    });

    inner.addEventListener('click', e => {
        if (revealed) { e.preventDefault(); }
    });
}

search.addEventListener('input', render);
load();

// polling ogni 5s
setInterval(async () => {
    try {
        const fresh = await Chats.list();
        // confronta per rilevare cambiamenti (unread, last_message)
        const changed = fresh.some((c, i) => {
            const old = allChats[i];
            return !old || old.unread !== c.unread || old.last_message !== c.last_message;
        }) || fresh.length !== allChats.length;

        if (changed) {
            allChats = fresh;
            render();
        }
    } catch(_) {}
}, 5000);