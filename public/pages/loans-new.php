<style>
    .dur-chip {
        padding: 5px 18px;
        border-radius: 999px;
        border: 1.5px solid var(--border);
        background: var(--surface);
        font-size: .8rem;
        font-weight: 700;
        color: var(--ink-2);
        cursor: pointer;
        font-family: var(--sans);
        transition: all .15s cubic-bezier(.16, 1, .3, 1);
        -webkit-tap-highlight-color: transparent;
    }
    .dur-chip:active { transform:scale(.95); }
    .dur-chip.active {
        background:var(--ink-1);
        color:#fff;
        border-color:var(--ink-1);
        box-shadow:0 4px 12px rgba(9,9,11,.2);
    }
    .ln-input {
        width:100%;
        background:var(--surface);
        border:1.5px solid var(--border);
        border-radius:16px;
        font-family:var(--sans);
        color:var(--ink-1);
        outline:none;
        transition:border-color .15s, box-shadow .15s;
    }
    .ln-input:focus {
        border-color: none;
        box-shadow: none;
    }
    .ln-label {
        font-size:.875rem;font-weight:700;color:var(--ink-1);
        display:flex;align-items:center;justify-content:space-between;
    }
</style>

<div style="padding:24px 16px 40px;display:flex;flex-direction:column;gap:28px;animation:pageIn .26s cubic-bezier(.16,1,.3,1) both">
    <div id="field-amount">
        <label class="ln-label" style="margin-bottom:10px">Importo richiesto</label>
        <div style="position:relative">
            <span style="position:absolute;left:18px;top:50%;transform:translateY(-50%);font-size:1.375rem;font-weight:800;color:var(--ink-3);pointer-events:none;z-index:1">€</span>
            <input class="ln-input" type="number" id="amount"
                placeholder="1.000"
                min="100" max="50000" inputmode="numeric"
                style="padding:18px 18px 18px 44px;font-size:1.875rem;font-weight:900;letter-spacing:-.05em">
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;font-weight:600;color:var(--ink-3)">
            <span>Min €100</span>
            <span>Max €50.000</span>
        </div>
        <span class="field-error" id="amount-error" style="display:none;font-size:.8125rem;color:var(--red);font-weight:700;margin-top:4px"></span>
    </div>

    <div id="field-duration">
        <label class="ln-label" style="margin-bottom:12px">Durata</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php foreach ([3,6,12,18,24,36,48,60] as $m): ?>
                <button class="dur-chip" data-val="<?= $m ?>"><?= $m ?> mesi</button>
            <?php endforeach; ?>
        </div>
        <input type="hidden" id="duration" value="">
        <span class="field-error" id="duration-error" style="display:none;font-size:.8125rem;color:var(--red);font-weight:700;margin-top:8px"></span>
    </div>

    <div id="field-reason">
        <label class="ln-label" style="margin-bottom:10px">Motivazione<span id="char-count" style="font-weight:600;color:var(--ink-3);font-size:.8125rem;transition:color .2s">0 / 20 min</span></label>
        <textarea class="ln-input" id="reason" rows="4"
        placeholder="Descrivi per cosa utilizzerai il finanziamento..."
        style="padding:16px 18px;resize:none;line-height:1.6;font-size:.9375rem;font-weight:500"></textarea>
        <span class="field-error" id="reason-error" style="display:none;font-size:.8125rem;color:var(--red);font-weight:700;margin-top:4px"></span>
    </div>

    <div id="rate-preview" style="display:none;background:#18181b;border-radius:22px;padding:22px 22px;position:relative;overflow:hidden">
        <div style="position:absolute;right:16px;top:26px;opacity:.06">
            <span class="ms" style="font-size:80px;color:#fff">local_fire_department</span>
        </div>
        <div style="position:relative;z-index:1">
            <div style="font-size:.5625rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.35);margin-bottom:8px">Tasso stimato</div>
            <div id="rate-value" style="font-size:2.75rem;font-weight:900;letter-spacing:-.07em;color:#fff;line-height:1"></div>
            <div style="font-size:.75rem;color:rgba(255,255,255,.3);font-weight:600;margin-top:6px">Calcolato sul tuo credit score</div>
        </div>
    </div>

    <button class="btn btn-primary btn-full btn-lg" id="submit-btn">
        <span class="btn-label">Invia richiesta</span>
    </button>
</div>