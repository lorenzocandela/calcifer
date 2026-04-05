import { Messages, Proposals, Notifications, userStore } from '/js/api.js';
import { toast, fmt } from '/js/app.js';

const chatId  = parseInt(new URLSearchParams(location.search).get('id') || '0');
const me      = userStore.get();
const msgsEl  = document.getElementById('chat-msgs');
const inputEl = document.getElementById('chat-input');
const sendBtn = document.getElementById('chat-send');

if (!chatId) location.href = '/?p=messages';

let lastCount = -1;
let polling   = null;

// textarea resize
inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
});

// notifiche
async function syncBadge() {
    try {
        const n = (await Notifications.list()).filter(x => !x.is_read).length;
        document.getElementById('notif-badge-chat')?.classList.toggle('hidden', n === 0);
    } catch(_) {}
}

document.getElementById('notif-btn-chat')?.addEventListener('click', async () => {
    try {
        openNotifSheet(await Notifications.list());
        Notifications.markRead(null, true).catch(() => {});
        document.getElementById('notif-badge-chat')?.classList.add('hidden');
    } catch(_) {}
});

function openNotifSheet(list) {
    document.getElementById('__ns')?.remove();
    const icons = { new_proposal:'handshake', proposal_accepted:'check_circle', proposal_rejected:'cancel', new_message:'chat', loan_funded:'payments' };
    const clr   = { new_proposal:'#f59e0b', proposal_accepted:'#16a34a', proposal_rejected:'#dc2626', new_message:'var(--accent)', loan_funded:'#16a34a' };
    const bgClr = { new_proposal:'#fef3c7', proposal_accepted:'#dcfce7', proposal_rejected:'#fee2e2', new_message:'hsla(351,80%,44%,.1)', loan_funded:'#dcfce7' };
    const unread = list.filter(n => !n.is_read).length;

    const el = document.createElement('div');
    el.id = '__ns';
    el.style.cssText = 'position:fixed;inset:0;z-index:300;display:flex;flex-direction:column;justify-content:flex-end;';
    el.innerHTML = `
        <div id="__ns_bg" style="position:absolute;inset:0;background:rgba(0,0,0,.42);backdrop-filter:blur(6px)"></div>
        <div style="position:relative;background:#fff;border-radius:24px 24px 0 0;max-height:78dvh;display:flex;flex-direction:column;box-shadow:0 -4px 40px rgba(0,0,0,.14)">
        <div style="width:36px;height:4px;background:#e5e7eb;border-radius:99px;margin:12px auto 0;flex-shrink:0"></div>
        <div style="padding:16px 20px 12px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;border-bottom:1px solid #f4f4f5">
            <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.125rem;font-weight:800;letter-spacing:-.03em">Notifiche</span>
            ${unread > 0 ? `<span style="background:var(--accent);color:#fff;font-size:.625rem;font-weight:800;padding:3px 9px;border-radius:999px">${unread} nuove</span>` : ''}
            </div>
            <button id="__ns_x" style="width:28px;height:28px;border-radius:50%;background:#f4f4f5;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center">
            <span class="ms ms-sm" style="font-size:16px;color:#71717a">close</span>
            </button>
        </div>
        <div style="overflow-y:auto;flex:1">
            ${!list.length
            ? `<div style="text-align:center;padding:48px 20px;color:#a1a1aa"><span class="ms" style="font-size:44px">notifications_none</span><p style="margin-top:8px;font-size:.875rem;font-weight:600">Nessuna notifica</p></div>`
            : list.map(n => {
                const ic = icons[n.type] ?? 'notifications';
                const co = clr[n.type]   ?? '#a1a1aa';
                const bg = bgClr[n.type] ?? '#f4f4f5';
                return `
                <div class="__nr" data-link="${n.link}" data-id="${n.id}" style="display:flex;align-items:flex-start;gap:14px;padding:14px 20px;cursor:pointer;border-bottom:1px solid #f9f9f9;background:${n.is_read?'transparent':'rgba(225,29,72,.03)'};transition:background .1s;position:relative">
                    ${!n.is_read ? `<div style="position:absolute;left:7px;top:50%;transform:translateY(-50%);width:5px;height:5px;border-radius:50%;background:var(--accent)"></div>` : ''}
                    <div style="width:44px;height:44px;border-radius:14px;background:${bg};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span class="ms" style="font-size:20px;color:${co}">${ic}</span>
                    </div>
                    <div style="flex:1;min-width:0">
                    <div style="font-size:.9375rem;font-weight:${n.is_read?600:800};color:${n.is_read?'#71717a':'#09090b'}">${n.title}</div>
                    <div style="font-size:.8125rem;color:#71717a;margin-top:3px;line-height:1.4">${n.body}</div>
                    <div style="font-size:.6875rem;color:#a1a1aa;margin-top:6px;font-weight:600">${fmt.relTime(n.created_at)}</div>
                    </div>
                    ${n.link ? `<span class="ms ms-sm" style="color:#d4d4d8;margin-top:4px;flex-shrink:0">chevron_right</span>` : ''}
                </div>`;}).join('')}
        </div>
        </div>`;

    el.querySelector('#__ns_bg').addEventListener('click', () => el.remove());
    el.querySelector('#__ns_x').addEventListener('click', () => el.remove());
    el.querySelectorAll('.__nr').forEach(r => {
        r.addEventListener('click', () => {
            const link = r.dataset.link, id = r.dataset.id;
            if (id) Notifications.markRead(id).catch(() => {});
            el.remove();
            if (link && link !== '#') window.location.href = link;
        });
        r.addEventListener('mouseenter', () => r.style.background = '#f9f9fb');
        r.addEventListener('mouseleave', () => r.style.background = '');
    });
    document.body.appendChild(el);
}

// ammortamento
function calcRepayment(amount, rate, months) {
    const r = (rate / 100) / 12;
    const monthly = r === 0 ? amount / months : amount * (r * Math.pow(1+r,months)) / (Math.pow(1+r,months) - 1);
    return { monthly, total: monthly * months, interest: monthly * months - amount };
}

// load e render
async function load(scroll = true) {
    try {
        const res = await Messages.fetch(chatId);
        if (res.messages.length !== lastCount) {
            lastCount = res.messages.length;
            renderHeader(res.chat);
            renderMessages(res.messages, res.chat);
            if (scroll) scrollBottom();
            Messages.markRead(chatId).catch(() => {});
        }
    } catch(e) {
        msgsEl.innerHTML = `<div style="text-align:center;padding:60px 20px;color:#a1a1aa"><span class="ms" style="font-size:44px">error</span><p style="margin-top:8px;font-weight:600">${e.message}</p></div>`;
    }
}

function renderHeader(chat) {
    const ini  = fmt.initials(chat.other_username);
    const size = 36;
    const base = `width:${size}px;height:${size}px;border-radius:50%;flex-shrink:0;border:1.5px solid rgba(255,255,255,.15);overflow:hidden;position:relative;background:#3f3f46;display:flex;align-items:center;justify-content:center`;
    const txt  = `font-size:${Math.round(size*.18)}px;font-weight:800;color:rgba(255,255,255,.7);text-transform:uppercase`;
    const avatarHtml = chat.other_avatar
        ? `<div style="${base}"><span style="${txt};position:absolute">${ini}</span><img src="${chat.other_avatar}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="${ini}"></div>`
        : `<div style="${base}"><span style="${txt}">${ini}</span></div>`;

    document.getElementById('chat-hd-info').innerHTML = `
      <div style="display:flex;align-items:center;gap:10px;min-width:0">
        ${avatarHtml}
        <div style="min-width:0">
          <div style="font-weight:800;font-size:.9375rem;letter-spacing:-.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">@${chat.other_username}</div>
          <div style="font-size:.6875rem;color:#a1a1aa;font-weight:600;margin-top:1px">${fmt.currency(chat.loan_amount)}</div>
        </div>
      </div>`;
}

function renderMessages(messages, chat) {
    let prevSenderId = null;
    let prevType     = null;

    // pre-processa: segna ultimo messaggio di ogni gruppo
    const enriched = messages.map((m, i) => {
        const next = messages[i + 1];
        return { ...m, isLastOfGroup: !next || next.sender_id !== m.sender_id };
    });

    msgsEl.innerHTML = enriched.map((m, i) => {
        const mine      = m.type !== 'system' && m.type !== 'proposal' && m.sender_id === me?.id;
        const samePrev  = prevSenderId === m.sender_id && prevType === m.type && m.type === 'text';
        const gap       = i === 0 ? '0' : samePrev ? '3px' : '14px';
        prevSenderId    = m.sender_id;
        prevType        = m.type;

        const radius = samePrev
            ? (mine ? '20px 20px 5px 20px' : '20px 12px 12px 5px')
            : (mine ? '20px 20px 5px 20px' : '20px 20px 20px 5px');

        let inner = '';
        if (m.type === 'system')                      inner = renderSystemMsg(m.content);
        else if (m.type === 'proposal' && m.proposal) inner = renderProposal(m, chat);
        else                                           inner = renderBubble(m, mine, radius);

        if (m.type === 'system' || m.type === 'proposal') {
            const justify = m.type === 'system' ? 'center' : 'flex-start';
            return `<div style="display:flex;justify-content:${justify};margin-top:${gap}">${inner}</div>`;
        }

        if (mine) {
            return `<div style="display:flex;justify-content:flex-end;margin-top:${gap}">${inner}</div>`;
        }

        const avatarSlot = m.isLastOfGroup
            ? buildMiniAvatar(chat.other_avatar, chat.other_username)
            : `<div style="width:28px;flex-shrink:0"></div>`;

        return `<div style="display:flex;align-items:flex-end;gap:6px;margin-top:${gap}">
          ${avatarSlot}
          ${inner}
        </div>`;
    }).join('');

    msgsEl.querySelectorAll('[data-accept]').forEach(b =>
        b.addEventListener('click', () => showPayOverlay(parseInt(b.dataset.accept), parseFloat(b.dataset.amount))));
    msgsEl.querySelectorAll('[data-reject]').forEach(b =>
        b.addEventListener('click', () => doReject(parseInt(b.dataset.reject))));
}

function buildMiniAvatar(avatarUrl, username) {
    const ini  = fmt.initials(username);
    const base = `width:28px;height:28px;border-radius:50%;flex-shrink:0;overflow:hidden;position:relative;background:#e4e4e7;display:flex;align-items:center;justify-content:center`;
    const txt  = `font-size:.4375rem;font-weight:800;color:#71717a;text-transform:uppercase`;
    if (avatarUrl) {
        return `<div style="${base}">
          <span style="${txt};position:absolute">${ini}</span>
          <img src="${avatarUrl}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="${ini}">
        </div>`;
    }
    return `<div style="${base}"><span style="${txt}">${ini}</span></div>`;
}

function renderBubble(m, mine, radius = mine ? '20px 20px 5px 20px' : '20px 20px 20px 5px') {
    const style = mine
        ? 'background:#09090b;color:#fff;box-shadow:0 2px 8px rgba(0,0,0,.18);'
        : 'background:#fff;border:1.5px solid #ebebeb;color:#09090b;box-shadow:0 1px 4px rgba(0,0,0,.06);';
    return `
      <div style="display:flex;flex-direction:column;max-width:74%;${mine?'align-items:flex-end':'align-items:flex-start'}">
        <div style="padding:11px 16px;border-radius:${radius};font-size:.9375rem;font-weight:500;line-height:1.5;word-break:break-word;${style}">${escHtml(m.content)}</div>
      </div>`;
}

function renderSystemMsg(content) {
    const isAccepted = content.includes('completato') || content.includes('trasferiti');
    const isRejected = content.includes('rifiutata') || content.includes('rifiutato');

    if (isAccepted) {
        const match  = content.match(/€[\d.,]+/);
        const amount = match ? match[0] : '';
        return `
            <div style="
            display:flex;flex-direction:column;align-items:center;gap:12px;
            background:linear-gradient(135deg,#f0fdf4,#dcfce7);
            border: 1.5px solid #16a34a47;
            border-radius:20px;
            padding:20px 24px;max-width:84%;text-align:center;
            box-shadow:0 2px 12px rgba(22,163,74,.12);
            ">
                <div style="width:48px;height:48px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(22,163,74,.3)">
                    <span class="ms" style="color:#fff;font-size:26px">check_circle</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px">
                    <div style="font-size:.8125rem;font-weight:700;color:#166534;letter-spacing:.02em;text-transform:uppercase">Finanziamento completato</div>
                    ${amount ? `<div style="font-size:1.75rem;font-weight:900;letter-spacing:-.05em;color:#166534;line-height:1">${amount}</div>` : ''}
                    <div style="font-size:.8125rem;color:#16a34a;font-weight:600">trasferiti con successo!</div>
                </div>
            </div>`;
    }
    if (isRejected) {
        return `
            <div style="
            display:flex;align-items:center;gap:10px;
            background:#fafafa;border:1.5px solid #ebebeb;border-radius:999px;
            padding:9px 18px;max-width:84%;
            ">
                <div style="width:24px;height:24px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <span class="ms" style="color:#dc2626;font-size:14px">close</span>
                </div>
                <span style="font-size:.875rem;font-weight:700;color:#71717a">${escHtml(content)}</span>
            </div>`;
    }
    return `
      <div style="background:#fafafa;border:1.5px solid #ebebeb;border-radius:999px;padding:7px 20px;font-size:.8125rem;font-weight:600;color:#71717a;max-width:84%;text-align:center">${escHtml(content)}</div>`;
}

function renderProposal(m, chat) {
    const p      = m.proposal;
    const isHowl = me?.id === chat.user_howl_id;
    const canAct = isHowl && p.status === 'pending';
    const { monthly, total, interest } = calcRepayment(p.amount, p.interest_rate, p.duration_months);
    const capPct = Math.round((p.amount / total) * 100);
    const intPct = 100 - capPct;

    const statusCfg = {
        pending:  { label:'In attesa',  bg:'#fef9c3', color:'#854d0e', dot:'#f59e0b' },
        accepted: { label:'Accettata',  bg:'#dcfce7', color:'#166534', dot:'#16a34a' },
        rejected: { label:'Rifiutata',  bg:'#fee2e2', color:'#991b1b', dot:'#dc2626' },
    };
    const st = statusCfg[p.status] ?? statusCfg.pending;

    return `
      <div style="max-width:min(340px,90%);display:flex;flex-direction:column;gap:4px">
        <div style="background:#fff;border:1.5px solid #e4e4e7;border-radius:20px 20px 20px 4px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.07)">

          <div style="padding:12px 16px;border-bottom:1px solid #f4f4f5;display:flex;align-items:center;justify-content:space-between;background:#fafafa">
            <div style="display:flex;align-items:center;gap:7px">
              <span class="ms ms-sm" style="color:#a1a1aa">handshake</span>
              <span style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa">Proposta</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:${st.bg};padding:4px 10px;border-radius:999px">
              <div style="width:5px;height:5px;border-radius:50%;background:${st.dot}"></div>
              <span style="font-size:.625rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:${st.color}">${st.label}</span>
            </div>
          </div>

          <div style="padding:16px;display:flex;flex-direction:column;gap:14px">

            <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:8px">
              <div>
                <div style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa;margin-bottom:3px">Importo richiesto</div>
                <div style="font-size:1.875rem;font-weight:900;letter-spacing:-.055em;line-height:1;color:#09090b">${fmt.currency(p.amount)}</div>
              </div>
              <div style="text-align:right;padding-bottom:2px">
                <div style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa;margin-bottom:3px">Durata</div>
                <div style="font-size:1.125rem;font-weight:800;letter-spacing:-.03em;color:#09090b">${p.duration_months} mesi</div>
              </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f9f9fb;border-radius:12px;border:1px solid #f0f0f2">
              <div style="display:flex;align-items:center;gap:8px">
                <span class="ms ms-sm" style="color:#a1a1aa">percent</span>
                <span style="font-size:.875rem;font-weight:600;color:#71717a">Tasso </span>
              </div>
              <span style="font-size:1.125rem;font-weight:900;letter-spacing:-.03em;color:var(--accent)">${fmt.percent(p.interest_rate)}</span>
            </div>

            <div style="border-top:1px solid #f4f4f5;padding-top:14px;display:flex;flex-direction:column;gap:12px">
              <div style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa">Piano di rimborso</div>

              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <div style="background:#f9f9fb;border:1px solid #f0f0f2;border-radius:12px;padding:12px 14px">
                  <div style="font-size:.5625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa;margin-bottom:4px">Rata mensile</div>
                  <div style="font-size:1.125rem;font-weight:900;letter-spacing:-.04em;color:#09090b">${fmt.currency(monthly)}</div>
                  <div style="font-size:.6875rem;color:#a1a1aa;margin-top:2px;font-weight:600">× ${p.duration_months}</div>
                </div>
                <div style="background:#f9f9fb;border:1px solid #f0f0f2;border-radius:12px;padding:12px 14px">
                  <div style="font-size:.5625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa;margin-bottom:4px">Totale dovuto</div>
                  <div style="font-size:1.125rem;font-weight:900;letter-spacing:-.04em;color:#09090b">${fmt.currency(total)}</div>
                  <div style="font-size:.6875rem;color:var(--accent);margin-top:2px;font-weight:700">+${fmt.currency(interest)}</div>
                </div>
              </div>

              <div>
                <div style="height:8px;border-radius:999px;overflow:hidden;background:#f4f4f5;display:flex;margin-bottom:7px">
                  <div style="width:${capPct}%;background:#09090b;border-radius:999px 0 0 999px"></div>
                  <div style="width:${intPct}%;background:var(--accent);border-radius:0 999px 999px 0"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.75rem;font-weight:700">
                  <span style="color:#71717a">Capitale <strong style="color:#09090b">${fmt.currency(p.amount)}</strong></span>
                  <span style="color:#71717a">Interessi <strong style="color:var(--accent)">${fmt.currency(interest)}</strong></span>
                </div>
              </div>

              <div style="display:flex;flex-direction:column;gap:8px">
                <div style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa">${p.duration_months} rate mensili</div>
                <div style="overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;padding-bottom:4px">
                  <div style="display:flex;gap:0;width:max-content;padding:4px 2px">
                    ${Array.from({length: p.duration_months}, (_, i) => {
                        const d = new Date();
                        d.setMonth(d.getMonth() + i + 1);
                        const label = d.toLocaleDateString('it-IT', { month:'short' }).replace('.','').toLowerCase();
                        const isFirst = i === 0;
                        return `
                          <div style="display:flex;flex-direction:column;align-items:center;gap:5px;width:40px;flex-shrink:0">
                            <div style="
                              width:26px;height:26px;border-radius:50%;
                              background:${isFirst ? '#09090b' : '#f4f4f5'};
                              border:1.5px solid ${isFirst ? '#09090b' : '#e4e4e7'};
                              display:flex;align-items:center;justify-content:center;
                              font-size:.5rem;font-weight:800;
                              color:${isFirst ? '#fff' : '#a1a1aa'};
                            ">${i + 1}</div>
                            <span style="font-size:.55rem;font-weight:700;color:${isFirst ? '#09090b' : '#a1a1aa'};letter-spacing:.01em;white-space:nowrap">${label}</span>
                          </div>`;
                    }).join('')}
                  </div>
                </div>
              </div>
            </div>

            ${canAct ? `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:2px">
              <button data-reject="${p.id}" style="
                padding:14px 10px;border-radius:14px;
                border:2px solid #fee2e2;background:#fff1f2;
                font-size:.9375rem;font-weight:800;color:#e11d48;
                cursor:pointer;font-family:var(--sans);
                letter-spacing:-.01em;
                transition:all .12s;
              " onmouseenter="this.style.background='#ffe4e8'" onmouseleave="this.style.background='#fff1f2'">
                Rifiuta
              </button>
              <button data-accept="${p.id}" data-amount="${p.amount}" style="
                padding:14px 10px;border-radius:14px;
                border:none;background:#09090b;
                font-size:.9375rem;font-weight:800;color:#fff;
                cursor:pointer;font-family:var(--sans);
                letter-spacing:-.01em;
                box-shadow:0 2px 8px rgba(0,0,0,.2);
                transition:opacity .12s,transform .1s;
              " onmouseenter="this.style.opacity='.82'" onmouseleave="this.style.opacity='1'" onmousedown="this.style.transform='scale(.97)'" onmouseup="this.style.transform='scale(1)'">
                Accetta
              </button>
            </div>` : ''}
          </div>
        </div>
      </div>`;
}

// overlay pagamento 
function showPayOverlay(proposalId, amount) {
    const o = document.createElement('div');
    o.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.52);backdrop-filter:blur(10px);z-index:400;display:flex;align-items:center;justify-content:center;padding:24px';
    o.innerHTML = `
      <div style="background:#fff;border-radius:24px;padding:28px 24px;width:100%;max-width:340px;display:flex;flex-direction:column;gap:22px;box-shadow:0 20px 60px rgba(0,0,0,.2)">
        <div style="display:flex;align-items:center;gap:14px">
          <div style="width:48px;height:48px;border-radius:16px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <span class="ms" style="color:#16a34a;font-size:26px">payments</span>
          </div>
          <div>
            <div style="font-size:1.0625rem;font-weight:800;letter-spacing:-.025em">Conferma pagamento</div>
            <div style="font-size:.8125rem;color:#71717a;margin-top:3px">Simulazione dimostrativa</div>
          </div>
        </div>
        <div style="background:#f9f9fb;border:1px solid #f0f0f2;border-radius:18px;padding:22px;text-align:center">
          <div style="font-size:.625rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#a1a1aa;margin-bottom:8px">Importo da trasferire</div>
          <div style="font-size:2.25rem;font-weight:900;letter-spacing:-.06em;color:#09090b">${fmt.currency(amount)}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
          <button id="__po" style="padding:16px;border-radius:14px;border:none;background:#09090b;color:#fff;font-size:.9375rem;font-weight:800;cursor:pointer;font-family:var(--sans);letter-spacing:-.01em;position:relative"><span class="btn-label">Paga ora</span></button>
          <button id="__pc" style="padding:13px;border-radius:14px;border:1.5px solid #e4e4e7;background:transparent;color:#52525b;font-size:.875rem;font-weight:700;cursor:pointer;font-family:var(--sans)">Annulla</button>
        </div>
      </div>`;

    document.body.appendChild(o);
    o.querySelector('#__pc').addEventListener('click', () => o.remove());
    const ok = o.querySelector('#__po');
    ok.addEventListener('click', async () => {
        ok.classList.add('loading'); ok.disabled = true;
        try {
            await Proposals.accept(proposalId);
            o.remove();
            toast('Finanziamento completato', 'positive');
            lastCount = -1;
            await load(true);
        } catch(e) {
            ok.classList.remove('loading'); ok.disabled = false;
            toast(e.message || 'Errore', 'negative');
        }
    });
}

async function doReject(id) {
    try {
        await Proposals.reject(id);
        lastCount = -1;
        await load(false);
    } catch(e) { toast(e.message || 'Errore', 'negative'); }
}

// send
async function sendMessage() {
    const content = inputEl.value.trim();
    if (!content) return;
    inputEl.value = ''; inputEl.style.height = 'auto';
    try {
        await Messages.send(chatId, content);
        lastCount = -1;
        await load(true);
    } catch(e) { toast(e.message || 'Errore', 'negative'); }
}

sendBtn.addEventListener('click', sendMessage);
inputEl.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });

function scrollBottom() { msgsEl.scrollTop = msgsEl.scrollHeight; }
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

// init
syncBadge();
setInterval(syncBadge, 10000);
load(true);
polling = setInterval(() => load(false), 3000);
window.addEventListener('beforeunload', () => clearInterval(polling));