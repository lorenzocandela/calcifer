import { Loans, Users } from '/js/api.js';
import { toast } from '/js/app.js';

// field errors
function showErr(id, msg) {
    const el = document.getElementById(`${id}-error`);
    if (!el) return;
    el.textContent = msg;
    el.style.display = msg ? 'block' : 'none';
}
function clearErr() { ['amount','duration','reason'].forEach(id => showErr(id, '')); }

// chip durata
document.querySelectorAll('.dur-chip').forEach(chip => {
    chip.addEventListener('click', () => {
        document.querySelectorAll('.dur-chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        document.getElementById('duration').value = chip.dataset.val;
        showErr('duration', '');
    });
});

// char counter
document.getElementById('reason')?.addEventListener('input', () => {
    const n = document.getElementById('reason').value.length;
    const cc = document.getElementById('char-count');
    if (cc) { cc.textContent = `${n} / 20 min`; cc.style.color = n >= 20 ? 'var(--green)' : 'var(--ink-3)'; }
    if (n >= 20) showErr('reason', '');
});

// tasso stimato
async function loadRate() {
    try {
        const user = await Users.me();
        const rate = Math.max(3, 15 - user.credit_score / 20).toFixed(2);
        const rv = document.getElementById('rate-value');
        const rp = document.getElementById('rate-preview');
        if (rv) rv.textContent = `${rate}%`;
        if (rp) rp.style.display = '';
    } catch(_) {}
}
loadRate();

// submit
document.getElementById('submit-btn')?.addEventListener('click', async () => {
    clearErr();

    const amountEl   = document.getElementById('amount');
    const durEl      = document.getElementById('duration');
    const reasonEl   = document.getElementById('reason');

    if (!amountEl || !durEl || !reasonEl) {
        toast('Errore interno — ricarica la pagina', 'negative');
        return;
    }

    const amount   = parseFloat(amountEl.value);
    const duration = parseInt(durEl.value);
    const reason   = reasonEl.value.trim();

    console.log('submit →', { amount, duration, reason });

    let ok = true;
    if (isNaN(amount) || amount < 100 || amount > 50000) {
        showErr('amount', 'Importo tra €100 e €50.000');
        ok = false;
    }
    if (!duration || isNaN(duration)) {
        showErr('duration', 'Seleziona una durata');
        ok = false;
    }
    if (reason.length < 20) {
        showErr('reason', `Minimo 20 caratteri (ora ${reason.length})`);
        ok = false;
    }
    if (!ok) {
        toast('Compila tutti i campi', 'warning');
        return;
    }

    const btn = document.getElementById('submit-btn');
    btn.classList.add('loading'); btn.disabled = true;
    try {
        const res = await Loans.create(amount, reason, duration);
        toast(`Richiesta inviata — tasso ${res.interest_rate}%`, 'positive');
        setTimeout(() => { window.location.href = '/?p=feed'; }, 1400);
    } catch(e) {
        console.error('create error', e);
        btn.classList.remove('loading'); btn.disabled = false;
        toast(e.message || 'Errore durante l\'invio', 'negative');
    }
});