<div class="page-section">
    <h1 class="page-title">Bacheca</h1>
    <p class="page-sub">Richieste di finanziamento attive.</p>
</div>

<div class="page-section" style="padding-top:0;display:flex;flex-direction:column;gap:14px">
    <div class="search-wrap">
        <span class="ms">search</span>
        <input class="search-input" type="search" id="search-input" placeholder="Cerca per utente o motivazione...">
    </div>
    <div class="chip-row" id="chip-row">
        <button class="chip active" data-filter="all">Tutti</button>
        <button class="chip" data-filter="low-rate">Tasso basso</button>
        <button class="chip" data-filter="short">Breve durata</button>
        <button class="chip" data-filter="high">Alto importo</button>
    </div>
</div>

<div class="page-section" style="padding-top:0">
    <div id="feed-list" style="display:flex;flex-direction:column;gap:12px">
        <div class="skeleton" style="height:180px;border-radius:20px"></div>
        <div class="skeleton" style="height:180px;border-radius:20px"></div>
        <div class="skeleton" style="height:180px;border-radius:20px"></div>
    </div>
</div>

<div class="modal-backdrop hidden" id="proposal-modal">
    <div class="modal">
        <div class="modal-handle"></div>
        <div id="modal-content"></div>
    </div>
</div>