import { Loans, Chats } from '/js/api.js';
import { toast, fmt } from '/js/app.js';

const list   = document.getElementById('feed-list');
const search = document.getElementById('search-input');
let allLoans = [];
let filter   = 'all';

// load
async function load() {
    list.innerHTML = `
        <div style="display:flex;flex-direction:column;gap:12px">
            ${[1,2,3].map(() => `<div class="skeleton" style="height:170px;border-radius:22px"></div>`).join('')}
        </div>`;
    try {
        allLoans = await Loans.feed();
        render();
    } catch(e) {
        list.innerHTML = `
            <div style="text-align:center;padding:60px 16px;color:var(--ink-3)">
                <span class="ms" style="font-size:44px">error</span>
                <p style="margin-top:8px;font-weight:600">${e.message}</p>
            </div>`;
    }
}

function applyFilter(loans) {
    let out = loans.filter(l => !l.is_mine); // escludi le tue
    if (filter === 'low-rate') out.sort((a,b) => a.interest_rate - b.interest_rate);
    if (filter === 'short')    out.sort((a,b) => a.duration_months - b.duration_months);
    if (filter === 'high')     out.sort((a,b) => b.amount - a.amount);
    const q = search.value.trim().toLowerCase();
    if (q) out = out.filter(l =>
        l.username.toLowerCase().includes(q) ||
        l.reason.toLowerCase().includes(q)
    );
    return out;
}

// render
function render() {
    const loans = applyFilter(allLoans);
    if (!loans.length) {
        list.innerHTML = `
            <div style="background:var(--surface);border:1.5px solid var(--border);border-radius:22px;padding:48px 20px;text-align:center">
                <span class="ms" style="font-size:44px;color:var(--ink-3)">storefront</span>
                <p style="margin-top:10px;font-size:.9375rem;font-weight:700;color:var(--ink-2)">Nessuna richiesta</p>
                <a href="/?p=loans-new" style="display:inline-block;margin-top:14px;padding:10px 22px;background:var(--ink-1);color:#fff;border-radius:999px;font-size:.875rem;font-weight:800;text-decoration:none">+ Crea la tua</a>
            </div>`;
        return;
    }

    list.innerHTML = `<div style="display:flex;flex-direction:column;gap:12px">
      ${loans.map(l => loanCard(l)).join('')}
    </div>`;

    list.querySelectorAll('.feed-card').forEach(card => {
        card.addEventListener('click', () => {
            const loan = allLoans.find(x => x.id === parseInt(card.dataset.id));
            if (loan) openSheet(loan);
        });
    });
}

function avatarEl(l, size = 40) {
    const ini  = fmt.initials(l.username);
    const base = `width:${size}px;height:${size}px;border-radius:50%;flex-shrink:0;border:1.5px solid var(--border);overflow:hidden;position:relative;background:#e4e4e7;display:flex;align-items:center;justify-content:center`;
    const txt  = `font-size:${Math.round(size*.19)}px;font-weight:800;color:#71717a;text-transform:uppercase`;
    if (l.avatar_url) {
        return `
            <div style="${base}">
                <span style="${txt};position:absolute">${ini}</span>
                <img src="${l.avatar_url}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover" alt="${ini}">
            </div>`;
    }
    return `<div style="${base}"><span style="${txt}">${ini}</span></div>`;
}

function scoreColor(s) {
    if (s >= 400) return '#8b5cf6';
    if (s >= 300) return '#f97316';
    if (s >= 200) return '#3b82f6';
    if (s >= 100) return '#f59e0b';
    return '#a1a1aa';
}

function loanCard(l) {
    const rate   = l.interest_rate;
    const rateOk = rate <= 10;
    const sc     = scoreColor(l.credit_score);

    return `
        <div class="feed-card" data-id="${l.id}" style="background:var(--surface);border:1.5px solid var(--border);border-radius:22px;overflow:hidden;cursor:pointer;transition:border-color .14s,box-shadow .14s;" onmouseenter="this.style.borderColor='var(--border-md)';this.style.boxShadow='0 4px 16px rgba(0,0,0,.07)'" onmouseleave="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            <div style="padding:16px 18px 12px;display:flex;align-items:center;gap:12px">
                ${avatarEl(l, 40)}
                <div style="flex:1;min-width:0">
                    <div style="font-size:.9375rem;font-weight:800;letter-spacing:-.02em">${l.username}</div>
                    <div style="display:flex;align-items:center;gap:6px;margin-top:2px">
                        <div style="width:6px;height:6px;border-radius:50%;background:${sc};flex-shrink:0"></div>
                        <span style="font-size:.6875rem;color:var(--ink-3);font-weight:600">CS${l.credit_score}</span>
                    </div>
                </div>
                ${l.is_mine ? `<span style="font-size:.5625rem;font-weight:800;letter-spacing:.05em;text-transform:uppercase;padding:4px 10px;border-radius:999px;background:var(--surface-3);color:var(--ink-3)">Tua</span>` : ''}
            </div>
            <div style="padding:0 18px 14px">
                <p style="font-size:.9375rem;color:var(--ink-2);font-weight:500;line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${l.reason}</p>
            </div>
            <div style="padding:12px 18px;border-top:1px solid var(--surface-3);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)">Importo</span>
                    <span style="font-size:1.125rem;font-weight:900;letter-spacing:-.035em">${fmt.currency(l.amount)}</span>
                </div>
                <div style="width:1px;height:32px;background:var(--border)"></div>
                <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)">Tasso</span>
                    <span style="font-size:1.125rem;font-weight:900;letter-spacing:-.035em;color:${rateOk ? 'var(--green)' : 'var(--accent)'}">${fmt.percent(rate)}</span>
                </div>
                <div style="width:1px;height:32px;background:var(--border)"></div>
                <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)">Durata</span>
                    <span style="font-size:1.125rem;font-weight:900;letter-spacing:-.035em">${l.duration_months}m</span>
                </div>
                ${!l.is_mine ? `
                <div style="margin-left:auto">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--ink-1);display:flex;align-items:center;justify-content:center;">
                        <span class="ms ms-sm" style="color:#fff;font-size:18px">arrow_forward</span>
                    </div>
                </div>` : ''}
            </div>
        </div>`;
}

// bottom sheet proposta
function openSheet(loan) {
    document.getElementById('__feed_sheet')?.remove();
    const isMine = loan.is_mine;
    const el = document.createElement('div');
    el.id = '__feed_sheet';
    el.style.cssText = 'position:fixed;inset:0;z-index:200;display:flex;flex-direction:column;justify-content:flex-end;';
    el.innerHTML = `
        <div id="__fs_bg" style="position:absolute;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px)"></div>
        <div style="position:relative;background:#fff;border-radius:28px 28px 0 0;max-height:88dvh;display:flex;flex-direction:column;box-shadow:0 -4px 40px rgba(0,0,0,.12);animation:fadeUp .22s cubic-bezier(.16,1,.3,1) both;">
            <div style="width:36px;height:4px;background:#e5e7eb;border-radius:99px;margin:12px auto 0;flex-shrink:0"></div>
            <div style="overflow-y:auto;flex:1;padding:20px 20px 32px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:18px">
                    ${avatarEl(loan, 44)}
                    <div>
                        <div style="font-size:1rem;font-weight:800;letter-spacing:-.02em">${loan.username}</div>
                        <div style="font-size:.75rem;color:var(--ink-3);font-weight:600;margin-top:2px">${fmt.date(loan.created_at)}</div>
                    </div>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:6px;background:var(--surface-3);padding:6px 12px;border-radius:999px">
                        <div style="width:6px;height:6px;border-radius:50%;background:${scoreColor(loan.credit_score)}"></div>
                        <span style="font-size:.6875rem;font-weight:700;color:var(--ink-2)">CS${loan.credit_score}</span>
                    </div>
                </div>

                <div style="background:var(--surface-3);border-radius:16px;padding:14px 16px;margin-bottom:18px">
                    <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:6px">Motivazione</div>
                    <p style="font-size:.9375rem;color:var(--ink-1);font-weight:500;line-height:1.55;margin:0">${loan.reason}</p>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:${isMine ? '0' : '20px'}">
                    <div style="background:var(--surface-3);border-radius:14px;padding:12px 14px">
                        <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:5px">Importo</div>
                        <div style="font-size:1.125rem;font-weight:900;letter-spacing:-.04em">${fmt.currency(loan.amount)}</div>
                    </div>
                    <div style="background:var(--surface-3);border-radius:14px;padding:12px 14px">
                        <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:5px">Tasso</div>
                        <div style="font-size:1.125rem;font-weight:900;letter-spacing:-.04em;color:var(--accent)">${fmt.percent(loan.interest_rate)}</div>
                    </div>
                    <div style="background:var(--surface-3);border-radius:14px;padding:12px 14px">
                        <div style="font-size:.5rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:5px">Durata</div>
                        <div style="font-size:1.125rem;font-weight:900;letter-spacing:-.04em">${loan.duration_months} mesi</div>
                    </div>
                </div>

                ${isMine ? '' : `
                <div style="border-top:1.5px solid var(--border);padding-top:18px;display:flex;flex-direction:column;gap:14px">
                    <div style="font-size:.625rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--ink-3)">Personalizza la proposta</div>
                    <div class="field">
                        <label class="field-label" for="p-amount">Importo €</label>
                        <div class="input-wrap">
                        <span class="ms ms-sm">euro</span>
                        <input class="input" type="number" id="p-amount" value="${loan.amount}" min="1">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="field">
                            <label class="field-label" for="p-duration">Mesi</label>
                            <input class="input" type="number" id="p-duration" value="${loan.duration_months}" min="1" max="60">
                        </div>
                        <div class="field">
                            <label class="field-label" for="p-rate">Tasso %</label>
                            <input class="input" type="number" id="p-rate" value="${loan.interest_rate}" step="0.1" min="0.1" max="30">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-full btn-lg" id="__send_prop">
                        <span class="btn-label">Invia proposta</span>
                    </button>
                </div>`}

                <button style="width:100%;margin-top:12px;padding:14px;border-radius:999px;border:1.5px solid var(--border);background:transparent;color:var(--ink-2);font-size:.9375rem;font-weight:700;cursor:pointer;font-family:var(--sans);" id="__close_sheet">Chiudi</button>
            </div>
        </div>`;

    el.querySelector('#__fs_bg').addEventListener('click', () => el.remove());
    el.querySelector('#__close_sheet').addEventListener('click', () => el.remove());

    if (!isMine) {
        el.querySelector('#__send_prop').addEventListener('click', async () => {
            const amount   = parseFloat(document.getElementById('p-amount').value);
            const duration = parseInt(document.getElementById('p-duration').value);
            const rate     = parseFloat(document.getElementById('p-rate').value);
            if (!amount || !duration || !rate) { toast('Compila tutti i campi', 'warning'); return; }
            const btn = el.querySelector('#__send_prop');
            btn.classList.add('loading'); btn.disabled = true;
            try {
                const res = await Chats.create(loan.id, amount, duration, rate);
                el.remove();
                window.location.href = `/?p=chat&id=${res.chat_id}`;
            } catch(err) {
                btn.classList.remove('loading'); btn.disabled = false;
                toast(err.message || 'Errore', 'negative');
            }
        });
    }

    document.body.appendChild(el);
}

// chip filters
document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        filter = chip.dataset.filter;
        render();
    });
});

search.addEventListener('input', render);
load();