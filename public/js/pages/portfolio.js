import { Loans, Chats } from '/js/api.js';
import { fmt } from '/js/app.js';

let activeTab = 'requests';

// tabs
document.querySelectorAll('#portfolio-tabs .tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('#portfolio-tabs .tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        activeTab = tab.dataset.tab;
        renderTab();
    });
});

async function renderTab() {
    const el = document.getElementById('portfolio-content');
    el.innerHTML = `<div style="display:flex;flex-direction:column;gap:10px">
      ${[1,2].map(() => `<div class="skeleton" style="height:110px;border-radius:20px"></div>`).join('')}
    </div>`;
    try {
        if (activeTab === 'requests')    await renderRequests(el);
        else                             await renderInvestments(el);
    } catch(e) {
        el.innerHTML = `<div style="text-align:center;padding:48px 16px;color:var(--ink-3)"><span class="ms" style="font-size:40px">error</span><p style="margin-top:8px;font-weight:600">${e.message}</p></div>`;
    }
}

// richieste
async function renderRequests(el) {
    const loans = await Loans.mine();

    if (!loans.length) {
        el.innerHTML = emptyState('request_quote', 'Nessuna richiesta', '/?p=loans-new', '+ Nuova richiesta');
        return;
    }

    el.innerHTML = `<div style="display:flex;flex-direction:column;gap:10px">
      ${loans.map(l => loanCard(l)).join('')}
    </div>`;
}

// investimenti
async function renderInvestments(el) {
    const chats = await Chats.list();

    if (!chats.length) {
        el.innerHTML = emptyState('trending_up', 'Nessun investimento', '/?p=feed', 'Vai alla Bacheca');
        return;
    }

    el.innerHTML = `<div style="display:flex;flex-direction:column;gap:10px">
      ${chats.map(c => investCard(c)).join('')}
    </div>`;
}

// card richiesta
function loanCard(l) {
    const statusCfg = {
        pending:  { label:'In attesa',  bg:'#fef3c7', color:'#92400e' },
        funded:   { label:'Finanziato', bg:'#dcfce7', color:'#166534' },
        active:   { label:'Attivo',     bg:'#dbeafe', color:'#1e40af' },
        closed:   { label:'Chiuso',     bg:'#f4f4f5', color:'#71717a' },
    };
    const st = statusCfg[l.status] ?? statusCfg.pending;

    return `
      <div style="
        background:var(--surface);border:1.5px solid var(--border);
        border-radius:22px;overflow:hidden;
      ">
        <!-- header con stato -->
        <div style="padding:14px 18px 12px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border-bottom:1px solid var(--surface-3)">
          <p style="font-size:.9375rem;color:var(--ink-1);font-weight:600;line-height:1.45;flex:1;margin:0;
            display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${l.reason}</p>
          <span style="font-size:.5625rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;
            padding:5px 11px;border-radius:999px;background:${st.bg};color:${st.color};white-space:nowrap;flex-shrink:0">${st.label}</span>
        </div>
        <!-- metriche -->
        <div style="padding:12px 18px;display:flex;align-items:center;gap:0">
          <div style="flex:1">
            <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Importo</div>
            <div style="font-size:1.0625rem;font-weight:900;letter-spacing:-.03em">${fmt.currency(l.amount)}</div>
          </div>
          <div style="width:1px;height:28px;background:var(--border)"></div>
          <div style="flex:1;padding-left:16px">
            <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Tasso</div>
            <div style="font-size:1.0625rem;font-weight:900;letter-spacing:-.03em;color:var(--accent)">${fmt.percent(l.interest_rate)}</div>
          </div>
          <div style="width:1px;height:28px;background:var(--border)"></div>
          <div style="flex:1;padding-left:16px">
            <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Durata</div>
            <div style="font-size:1.0625rem;font-weight:900;letter-spacing:-.03em">${l.duration_months}m</div>
          </div>
        </div>
      </div>`;
}

// card investimento
function investCard(c) {
    const hasUnread = c.unread > 0;
    const preview   = c.last_message || 'Proposta inviata';
    const ini       = fmt.initials(c.other_username);

    return `
      <a href="/?p=chat&id=${c.id}" style="
        background:var(--surface);border:1.5px solid ${hasUnread ? 'var(--accent)' : 'var(--border)'};
        border-radius:22px;overflow:hidden;
        text-decoration:none;color:inherit;
        display:block;
        transition:border-color .12s,box-shadow .12s;
      " onmouseenter="this.style.boxShadow='0 4px 14px rgba(0,0,0,.08)'" onmouseleave="this.style.boxShadow='none'">

        <!-- header -->
        <div style="padding:14px 18px 12px;display:flex;align-items:center;gap:12px;border-bottom:1px solid var(--surface-3)">
          <div style="
            width:44px;height:44px;border-radius:50%;
            background:var(--surface-3);border:1.5px solid var(--border);
            display:flex;align-items:center;justify-content:center;
            font-size:.6875rem;font-weight:800;color:var(--ink-2);
            text-transform:uppercase;flex-shrink:0;
          ">${ini}</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.9375rem;font-weight:800;letter-spacing:-.02em">${c.other_username}</div>
            <div style="font-size:.8125rem;color:var(--ink-2);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${preview}</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
            <span style="font-size:.6875rem;color:var(--ink-3);font-weight:600">${c.last_msg_at ? fmt.relTime(c.last_msg_at) : ''}</span>
            ${hasUnread ? `<span style="background:var(--accent);color:#fff;font-size:.5625rem;font-weight:800;padding:2px 8px;border-radius:999px">${c.unread} nuovi</span>` : ''}
          </div>
        </div>

        <!-- importo + freccia -->
        <div style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Importo prestito</div>
            <div style="font-size:1.25rem;font-weight:900;letter-spacing:-.04em">${fmt.currency(c.loan_amount)}</div>
          </div>
          <div style="width:34px;height:34px;border-radius:50%;background:var(--surface-3);display:flex;align-items:center;justify-content:center">
            <span class="ms ms-sm" style="color:var(--ink-2)">chevron_right</span>
          </div>
        </div>
      </a>`;
}

function emptyState(icon, text, href, btnText) {
    return `
      <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:22px;padding:40px 20px;text-align:center">
        <div style="width:52px;height:52px;border-radius:16px;background:var(--surface-3);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
          <span class="ms" style="font-size:26px;color:var(--ink-3)">${icon}</span>
        </div>
        <p style="font-size:.9375rem;font-weight:700;color:var(--ink-2);margin:0 0 14px">${text}</p>
        <a href="${href}" style="display:inline-block;padding:10px 22px;background:var(--ink-1);color:#fff;border-radius:999px;font-size:.875rem;font-weight:800;text-decoration:none">${btnText}</a>
      </div>`;
}

renderTab();