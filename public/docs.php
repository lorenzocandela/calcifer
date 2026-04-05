<?php $page = $_GET['p'] ?? 'home'; ?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>CALCIFER — Docs</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,700;0,9..40,900;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
        <?php if ($page === 'swagger'): ?>
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        <?php endif; ?>
        <style>
            :root {
            --red: #E11D48;
            --red-d: #be123c;
            --ink: #0a0a0a;
            --ink-2: #3f3f46;
            --ink-3: #a1a1aa;
            --border: #e4e4e7;
            --surface: #fafafa;
            --mono: 'DM Mono', monospace;
            --sans: 'DM Sans', sans-serif;
            }
            *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
            html { scroll-behavior:smooth; }
            body { font-family:var(--sans); background:#fff; color:var(--ink); min-height:100dvh; }

            /* ── NAV ── */
            nav {
                position: sticky;
                top: 0;
                z-index: 50;
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 0 32px;
                height: 60px;
                background: rgba(255, 255, 255, .85);
                backdrop-filter: blur(20px);
                border-bottom: 1px solid var(--border);
                justify-content: space-between;
            }
            .nav-logo { display:flex; align-items:center; gap:10px; text-decoration:none; }
            .nav-logo img { width:26px; height:26px; object-fit:contain; border-radius:6px; }
            .nav-links { display:flex; align-items:center; gap:4px; margin-left:auto; }
            .nav-link {
            font-size:.8125rem; font-weight:600; color:var(--ink-3);
            padding:6px 12px; border-radius:8px; text-decoration:none;
            transition:color .12s, background .12s;
            }
            .nav-link:hover { color:var(--ink); background:#f4f4f5; }
            .nav-link.active { color:var(--ink); background:#f4f4f5; font-weight:700; }
            .nav-back {
            display:flex; align-items:center; gap:6px;
            font-size:.8125rem; font-weight:600; color:var(--ink-3);
            padding:6px 12px; border-radius:8px; text-decoration:none;
            transition:color .12s, background .12s; margin-left:auto;
            }
            .nav-back:hover { color:var(--ink); background:#f4f4f5; }

            /* ── HOME ── */
            .home-hero {
            max-width:900px; margin:0 auto;
            padding:80px 32px 48px;
            display:flex; flex-direction:column; gap:12px;
            }
            .hero-eyebrow {
            font-size:.6875rem; font-weight:700; letter-spacing:.12em;
            text-transform:uppercase; color:var(--red);
            }
            .hero-title {
            font-size:clamp(2rem,5vw,3.5rem);
            font-weight:900; letter-spacing:-.05em; line-height:1.05;
            color:var(--ink);
            }

            /* ── CARDS GRID ── */
            .cards-grid {
            max-width:900px; margin:0 auto;
            padding:0 32px 80px;
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
            gap:16px;
            }

            .doc-card {
            border:1.5px solid var(--border);
            border-radius:24px;
            padding:28px;
            text-decoration:none;
            color:inherit;
            display:flex; flex-direction:column; gap:20px;
            background:#fff;
            transition:border-color .15s, box-shadow .15s, transform .15s;
            cursor:pointer;
            position:relative; overflow:hidden;
            }
            .doc-card::before {
            content:''; position:absolute; inset:0;
            opacity:0; transition:opacity .15s;
            }
            .doc-card:hover { transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,0,0,.08); }
            .doc-card:hover { border-color:#d4d4d8; }

            .doc-card.swagger::before  { background:radial-gradient(ellipse at top left, #fff0f212, transparent 60%); }
            .doc-card.design::before   { background:radial-gradient(ellipse at top left, #f0f9ff12, transparent 60%); }
            .doc-card.vision::before   { background:radial-gradient(ellipse at top left, #fefce812, transparent 60%); }

            .card-icon {
            width:48px; height:48px; border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            flex-shrink:0;
            }
            .swagger .card-icon { background:#fff0f2; }
            .design .card-icon { background:#f0f9ff; }
            .vision .card-icon { background:#4caf501a; }

            .card-icon svg { width:22px; height:22px; }

            .card-body { flex:1; display:flex; flex-direction:column; gap:6px; }
            .card-title { font-size:1.125rem; font-weight:800; letter-spacing:-.025em; }
            .card-desc { font-size:.875rem; color:var(--ink-3); line-height:1.55; font-weight:400; }

            .card-arrow {
            display:flex; 
            align-items:center; 
            justify-content:space-between;
            margin-top:8px;
            }
            .card-tag {
            font-size:.5625rem; font-weight:700; letter-spacing:.08em;
            text-transform:uppercase; padding:4px 10px; border-radius:999px;
            }
            .swagger .card-tag { background:#fff0f2; color:var(--red); }
            .design .card-tag { background:#f0f9ff; color:#0369a1; }
            .vision .card-tag {
                background: #4caf501a;
                color: #478b4a;
            }

            .arrow-icon {
            width:32px; height:32px; border-radius:50%;
            background:#f4f4f5;
            display:flex; align-items:center; justify-content:center;
            transition:background .12s, transform .12s;
            }
            .doc-card:hover .arrow-icon { background:var(--ink); transform:translateX(2px); }
            .doc-card:hover .arrow-icon svg path { stroke:#fff; }

            /* ── DESIGN SYSTEM PAGE ── */
            .ds-wrap { max-width:860px; margin:0 auto; padding:40px 32px 80px; display:flex; flex-direction:column; gap:56px; }
            .ds-section { display:flex; flex-direction:column; gap:20px; }
            .ds-label {
            font-size:.5625rem; font-weight:700; letter-spacing:.12em;
            text-transform:uppercase; color:var(--ink-3);
            border-bottom:1px solid var(--border); padding-bottom:10px;
            }
            .ds-row { display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap; }
            .ds-title { font-size:1.375rem; font-weight:800; letter-spacing:-.03em; margin-bottom:4px; }
            .ds-sub { font-size:.9375rem; color:var(--ink-3); line-height:1.6; }

            /* colori */
            .swatch {
            display:flex; flex-direction:column; gap:8px;
            width:100px;
            }
            .swatch-color { height:64px; border-radius:14px; border:1px solid rgba(0,0,0,.06); }
            .swatch-name { font-size:.75rem; font-weight:700; color:var(--ink-2); }
            .swatch-val { font-size:.6875rem; font-family:var(--mono); color:var(--ink-3); }

            /* typography */
            .type-row { display:flex; flex-direction:column; gap:4px; }
            .type-spec { font-size:.75rem; font-family:var(--mono); color:var(--ink-3); }

            /* spacing */
            .sp-row { display:flex; align-items:flex-end; gap:8px; }
            .sp-block { background:#f4f4f5; border-radius:4px; display:flex; align-items:flex-end; justify-content:center; padding-bottom:6px; }
            .sp-val { font-size:.625rem; font-family:var(--mono); color:var(--ink-3); }

            /* components preview */
            .comp-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
            .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 22px; border-radius:999px; font-family:var(--sans); font-weight:700; font-size:.875rem; cursor:pointer; border:1.5px solid transparent; transition:all .15s; }
            .btn-primary { background:var(--ink); color:#fff; }
            .btn-accent  { background:var(--red); color:#fff; }
            .btn-ghost   { background:#fff; color:var(--ink); border-color:var(--border); }
            .btn-soft    { background:#f4f4f5; color:var(--ink); }
            .badge { display:inline-flex; align-items:center; padding:5px 12px; border-radius:999px; font-size:.75rem; font-weight:700; }
            .badge-red    { background:#fff0f2; color:var(--red); }
            .badge-green  { background:#f0fdf4; color:#16a34a; }
            .badge-amber  { background:#fffbeb; color:#92400e; }
            .badge-gray   { background:#f4f4f5; color:var(--ink-3); }
            .card-demo { background:#fff; border:1.5px solid var(--border); border-radius:20px; padding:16px 18px; min-width:200px; }

            /* animations */
            .anim-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; }
            .anim-card {
            border:1.5px solid var(--border); border-radius:16px;
            padding:20px 16px; text-align:center;
            display:flex; flex-direction:column; align-items:center; gap:10px;
            cursor:pointer;
            }
            .anim-card:hover .anim-dot { animation-play-state:running; }
            .anim-dot {
            width:36px; height:36px; background:var(--red); border-radius:10px;
            animation-play-state:paused; animation-duration:1s; animation-iteration-count:infinite;
            }
            .anim-name { font-size:.75rem; font-weight:700; color:var(--ink-2); }
            .anim-easing { font-size:.625rem; font-family:var(--mono); color:var(--ink-3); }
            @keyframes anim-pageIn   { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:none; } }
            @keyframes anim-fadeUp   { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:none; } }
            @keyframes anim-scaleIn  { from { opacity:0; transform:scale(.92); } to { opacity:1; transform:scale(1); } }
            @keyframes anim-float    { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
            @keyframes anim-pulse    { 0%,100%{opacity:1} 50%{opacity:.4} }
            @keyframes anim-spin     { to{transform:rotate(360deg)} }
            .dot-pageIn  { animation-name:anim-pageIn;  animation-timing-function:cubic-bezier(.16,1,.3,1); }
            .dot-fadeUp  { animation-name:anim-fadeUp;  animation-timing-function:cubic-bezier(.16,1,.3,1); }
            .dot-scaleIn { animation-name:anim-scaleIn; animation-timing-function:cubic-bezier(.16,1,.3,1); }
            .dot-float   { animation-name:anim-float;   animation-timing-function:ease-in-out; animation-duration:2s !important; }
            .dot-pulse   { animation-name:anim-pulse;   animation-timing-function:ease-in-out; }
            .dot-spin    { animation-name:anim-spin;    animation-timing-function:linear; border-radius:20%; }

            /* radii */
            .radius-row { display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap; }
            .radius-demo { background:#f4f4f5; width:64px; height:64px; display:flex; align-items:center; justify-content:center; }
            .radius-label { font-size:.625rem; font-family:var(--mono); color:var(--ink-3); text-align:center; margin-top:6px; }

            /* ── VISION PAGE ── */
            .vision-wrap { max-width:760px; margin:0 auto; padding:40px 32px 80px; display:flex; flex-direction:column; gap:48px; }
            .vision-hero { display:flex; flex-direction:column; gap:16px; }
            .vision-flame { font-size:64px; line-height:1; }
            .vision-h1 { font-size:clamp(2rem,5vw,3rem); font-weight:900; letter-spacing:-.06em; line-height:1.05; }
            .vision-lead { font-size:1.0625rem; color:var(--ink-3); line-height:1.7; font-weight:400; }
            .vision-section { display:flex; flex-direction:column; gap:14px; }
            .vision-h2 { font-size:1.125rem; font-weight:800; letter-spacing:-.025em; }
            .vision-p { font-size:.9375rem; color:var(--ink-2); line-height:1.75; }
            .vision-divider { height:1px; background:var(--border); }
            .levels-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
            @media(min-width:600px) { .levels-grid { grid-template-columns:repeat(4,1fr); } }
            .level-card {
            border:1.5px solid var(--border); border-radius:18px;
            padding:16px; display:flex; flex-direction:column; gap:8px;
            }
            .level-dot { width:12px; height:12px; border-radius:50%; }
            .level-name { font-size:.875rem; font-weight:800; }
            .level-range { font-size:.6875rem; font-family:var(--mono); color:var(--ink-3); }

            /* ── SHARED ── */
            .page-section-header { padding:32px 32px 0; max-width:860px; margin:0 auto; }
            .page-section-header h1 { font-size:1.75rem; font-weight:900; letter-spacing:-.04em; }
            .page-section-header p { font-size:.9375rem; color:var(--ink-3); margin-top:6px; }

            @media(max-width:600px) {
            nav { padding:0 16px; }
            .home-hero, .cards-grid, .ds-wrap, .vision-wrap { padding-left:16px; padding-right:16px; }
            .cards-grid { grid-template-columns:1fr; }
            }
        </style>
    </head>
    <body>
        <nav>
            <a class="nav-logo" href="/docs.php">
                <img src="/logo_clean.png" alt="Calcifer">
            </a>
            <?php if ($page === 'home'): ?>
                <div class="nav-links">
                <a class="nav-link" href="/?p=dashboard" style="margin-left:8px;background:var(--ink);color:#fff;padding:6px 14px;border-radius:999px">App</a>
                </div>
            <?php else: ?>
                <a class="nav-back" href="/docs.php" style="margin-left:8px;background:var(--ink);color:#fff;padding:6px 14px;border-radius:999px">Docs</a>
            <?php endif; ?>
        </nav>
        <?php if ($page === 'home'): ?>
            <!-- HOME -->
            <div class="home-hero">
                <div class="hero-eyebrow">Calcifer P2P — Documentazione</div>
                <h1 class="hero-title">Tutto quello che<br>serve per capire<br>Calcifer.</h1>
            </div>
            <div class="cards-grid">
                <a class="doc-card swagger" href="/docs.php?p=swagger">
                    <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 9l3 3-3 3M13 15h3M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/>
                    </svg>
                    </div>
                    <div class="card-body">
                    <div class="card-title">API Reference</div>
                    <div class="card-desc">Tutti gli endpoint documentati con Swagger UI. Auth, loans, chats, proposals e altro. Testabili direttamente dal browser.</div>
                    </div>
                    <div class="card-arrow">
                    <span class="card-tag">OpenAPI 3.0</span>
                    <div class="arrow-icon">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7h10M8 3l4 4-4 4" stroke="#09090b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    </div>
                </a>
                <a class="doc-card design" href="/docs.php?p=design">
                    <div class="card-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/>
                    </svg>
                    </div>
                    <div class="card-body">
                    <div class="card-title">Design System</div>
                    <div class="card-desc">Colori, tipografia, spaziature, componenti e animazioni. Il sistema visivo che governa tutta l'interfaccia di Calcifer.</div>
                    </div>
                    <div class="card-arrow">
                    <span class="card-tag">Tokens & Components</span>
                    <div class="arrow-icon">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M2 7h10M8 3l4 4-4 4" stroke="#09090b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    </div>
                </a>
                <a class="doc-card vision" href="/docs.php?p=vision">
                    <div class="card-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#478b4a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 8v4l3 3"/>
                        </svg>
                    </div>
                    <div class="card-body">
                        <div class="card-title">Vision</div>
                        <div class="card-desc">Come è nato Calcifer, perché il fuoco, il sistema di credito gamificato e i valori che guidano ogni scelta di prodotto.</div>
                    </div>
                    <div class="card-arrow">
                        <span class="card-tag">Brand & Product</span>
                        <div class="arrow-icon">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M2 7h10M8 3l4 4-4 4" stroke="#09090b" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        <?php elseif ($page === 'swagger'): ?>
            <!-- SWAGGER -->
            <div id="swagger-wrap"></div>
            <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
            <script>
                const spec = {
                    openapi: '3.0.0',
                    info: { title: 'Calcifer P2P API', version: '1.0.0', description: 'API backend peer-to-peer lending. JWT Bearer auth.' },
                    servers: [{ url: 'https://calcifer.lorenzoo.it/api', description: 'Production' }],
                    components: {
                        securitySchemes: { bearerAuth: { type:'http', scheme:'bearer', bearerFormat:'JWT' } }
                    },
                    tags: [
                        {name:'Auth'},{name:'Users'},{name:'Loans'},{name:'Chats'},
                        {name:'Messages'},{name:'Proposals'},{name:'Notifications'},{name:'Dashboard'},{name:'Avatar'}
                    ],
                    paths: {
                        '/auth.php': { post: { tags:['Auth'], summary:'Login / Registrazione', requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['login','register'],example:'login'}, username:{type:'string'}, email:{type:'string'}, password:{type:'string'} }, required:['action','password'] }}}}, responses:{ 200:{description:'Token JWT + dati utente'}, 401:{description:'Credenziali errate'}, 422:{description:'Parametri mancanti'} } } },
                        '/users.php': { post: { tags:['Users'], summary:'Profilo corrente', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['me'],example:'me'} } }}}}, responses:{ 200:{description:'Dati utente con avatar_url e credit_score'} } } },
                        '/loans.php': { post: { tags:['Loans'], summary:'Feed / Mine / Create / Detail', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['feed','mine','create','detail'],example:'feed'}, amount:{type:'number',example:1500}, reason:{type:'string'}, duration_months:{type:'integer',enum:[3,6,12,18,24,36,48,60]}, id:{type:'integer'} }, required:['action'] }}}}, responses:{ 200:{description:'Lista loan'}, 201:{description:'Loan creato con interest_rate'}, 422:{description:'Validazione fallita'} } } },
                        '/chats.php': { post: { tags:['Chats'], summary:'Create / List / Delete', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['create','list','delete'],example:'list'}, loan_id:{type:'integer'}, amount:{type:'number'}, duration_months:{type:'integer'}, interest_rate:{type:'number'}, chat_id:{type:'integer'} }, required:['action'] }}}}, responses:{ 200:{description:'Lista chat con avatar e unread count'}, 201:{description:'Chat + proposal creati'} } } },
                        '/messages.php': { post: { tags:['Messages'], summary:'Fetch / Send / Mark read', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['fetch','send','mark_read'],example:'fetch'}, chat_id:{type:'integer',example:1}, content:{type:'string'} }, required:['action','chat_id'] }}}}, responses:{ 200:{description:'Messaggi con proposal inline e avatar'}, 201:{description:'Messaggio inviato'} } } },
                        '/proposals.php': { post: { tags:['Proposals'], summary:'Accept / Reject', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['accept','reject'],example:'accept'}, proposal_id:{type:'integer',example:1} }, required:['action','proposal_id'] }}}}, responses:{ 200:{description:'Transazione completata'}, 402:{description:'Fondi insufficienti'}, 403:{description:'Non autorizzato'} } } },
                        '/notifications.php': { post: { tags:['Notifications'], summary:'List / Mark read / Delete all', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['list','mark_read','delete_all'],example:'list'}, notification_id:{type:'integer'}, all:{type:'boolean'} }, required:['action'] }}}}, responses:{ 200:{description:'Lista notifiche con image_url per messaggi'} } } },
                        '/dashboard.php': { post: { tags:['Dashboard'], summary:'Analytics utente', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'application/json':{ schema:{ type:'object', properties:{ action:{type:'string',enum:['analytics'],example:'analytics'} } }}}}, responses:{ 200:{description:'Statistiche howl + calcifer'} } } },
                        '/avatar.php': { post: { tags:['Avatar'], summary:'Upload foto profilo', security:[{bearerAuth:[]}], requestBody:{ required:true, content:{'multipart/form-data':{ schema:{ type:'object', properties:{ avatar:{type:'string',format:'binary'} }, required:['avatar'] }}}}, responses:{ 200:{description:'URL avatar con cache-busting'}, 422:{description:'File non valido'} } } }
                    }
                };
                SwaggerUIBundle({ spec, dom_id:'#swagger-wrap', deepLinking:true, presets:[SwaggerUIBundle.presets.apis], layout:'BaseLayout', defaultModelsExpandDepth:-1, docExpansion:'list', filter:true, tryItOutEnabled:true });
            </script>
        <?php elseif ($page === 'design'): ?>
            <!-- DESIGN SYSTEM -->
            <div class="ds-wrap">

            <div>
                <div style="font-size:.6875rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--red);margin-bottom:10px">Design System</div>
                <h1 style="font-size:2rem;font-weight:900;letter-spacing:-.04em;margin-bottom:8px">Calcifer UI</h1>
                <p style="color:var(--ink-3);font-size:.9375rem;line-height:1.6">Tokens, componenti e animazioni che definiscono l'identità visiva dell'app.</p>
            </div>

            <!-- COLORI -->
            <div class="ds-section">
                <div class="ds-label">Colori</div>
                <div class="ds-row">
                <div class="swatch"><div class="swatch-color" style="background:#0a0a0a"></div><div class="swatch-name">Ink</div><div class="swatch-val">#0a0a0a</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#3f3f46"></div><div class="swatch-name">Ink 2</div><div class="swatch-val">#3f3f46</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#a1a1aa"></div><div class="swatch-name">Ink 3</div><div class="swatch-val">#a1a1aa</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#E11D48"></div><div class="swatch-name">Accent</div><div class="swatch-val">#E11D48</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#be123c"></div><div class="swatch-name">Accent D</div><div class="swatch-val">#be123c</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#fff0f2;border:1px solid #fecdd3"></div><div class="swatch-name">Accent Soft</div><div class="swatch-val">#fff0f2</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#f4f4f5"></div><div class="swatch-name">Surface 3</div><div class="swatch-val">#f4f4f5</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#18181b"></div><div class="swatch-name">Dark</div><div class="swatch-val">#18181b</div></div>
                </div>
                <div class="ds-row" style="margin-top:4px">
                <div class="swatch"><div class="swatch-color" style="background:#16a34a"></div><div class="swatch-name">Green</div><div class="swatch-val">#16a34a</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#f59e0b"></div><div class="swatch-name">Amber</div><div class="swatch-val">#f59e0b</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#ef4444"></div><div class="swatch-name">Red</div><div class="swatch-val">#ef4444</div></div>
                <div class="swatch"><div class="swatch-color" style="background:#ce122d"></div><div class="swatch-name">Score</div><div class="swatch-val">#ce122d</div></div>
                </div>
            </div>

            <!-- TIPOGRAFIA -->
            <div class="ds-section">
                <div class="ds-label">Tipografia — DM Sans</div>
                <div style="display:flex;flex-direction:column;gap:16px">
                <div class="type-row"><div style="font-size:2.5rem;font-weight:900;letter-spacing:-.06em;line-height:1">Display</div><div class="type-spec">2.5rem / 900 / -0.06em</div></div>
                <div class="type-row"><div style="font-size:1.75rem;font-weight:900;letter-spacing:-.04em;line-height:1.1">Heading 1</div><div class="type-spec">1.75rem / 900 / -0.04em</div></div>
                <div class="type-row"><div style="font-size:1.25rem;font-weight:800;letter-spacing:-.03em">Heading 2</div><div class="type-spec">1.25rem / 800 / -0.03em</div></div>
                <div class="type-row"><div style="font-size:1rem;font-weight:800;letter-spacing:-.02em">Heading 3</div><div class="type-spec">1rem / 800 / -0.02em</div></div>
                <div class="type-row"><div style="font-size:.9375rem;font-weight:500;line-height:1.6;color:var(--ink-2)">Body — Il testo principale dell'app usa DM Sans a peso 500, per una leggibilità ottimale su mobile.</div><div class="type-spec">0.9375rem / 500 / 1.6</div></div>
                <div class="type-row"><div style="font-size:.75rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ink-3)">Label uppercase</div><div class="type-spec">0.75rem / 700 / +0.06em / uppercase</div></div>
                <div class="type-row"><div style="font-size:.875rem;font-family:var(--mono);color:var(--ink-2)">Mono — api/endpoint/token</div><div class="type-spec">DM Mono / 0.875rem</div></div>
                </div>
            </div>

            <!-- BORDER RADIUS -->
            <div class="ds-section">
                <div class="ds-label">Border Radius</div>
                <div class="radius-row">
                <?php
                $radii = ['4px'=>'xs','8px'=>'sm','12px'=>'md','16px'=>'lg','20px'=>'xl','24px'=>'2xl','999px'=>'pill'];
                foreach($radii as $val => $name):
                    $size = $val === '999px' ? '64px' : '64px';
                ?>
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px">
                    <div style="background:#f4f4f5;width:64px;height:64px;border-radius:<?= $val ?>;border:1px solid var(--border)"></div>
                    <div style="font-size:.6875rem;font-weight:700;color:var(--ink-2)"><?= $name ?></div>
                    <div style="font-size:.5625rem;font-family:var(--mono);color:var(--ink-3)"><?= $val ?></div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- COMPONENTI -->
            <div class="ds-section">
                <div class="ds-label">Bottoni</div>
                <div class="comp-row">
                <button class="btn btn-primary">Primary</button>
                <button class="btn btn-accent">Accent</button>
                <button class="btn btn-ghost">Ghost</button>
                <button class="btn btn-soft">Soft</button>
                </div>
            </div>

            <div class="ds-section">
                <div class="ds-label">Badge & Status</div>
                <div class="comp-row">
                <span class="badge badge-red">Accent</span>
                <span class="badge badge-green">Attivo</span>
                <span class="badge badge-amber">In attesa</span>
                <span class="badge badge-gray">Chiuso</span>
                </div>
            </div>

            <div class="ds-section">
                <div class="ds-label">Card</div>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                <div class="card-demo">
                    <div style="font-size:.5rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Importo</div>
                    <div style="font-size:1.25rem;font-weight:900;letter-spacing:-.04em">€1.500,00</div>
                </div>
                <div class="card-demo" style="background:#18181b;border-color:#18181b">
                    <div style="font-size:.5rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:4px">Tasso stimato</div>
                    <div style="font-size:1.75rem;font-weight:900;letter-spacing:-.05em;color:#fff">8.50%</div>
                </div>
                </div>
            </div>

            <!-- ANIMAZIONI -->
            <div class="ds-section">
                <div class="ds-label">Animazioni — passa il mouse per vedere</div>
                <div class="anim-grid">
                <div class="anim-card"><div class="anim-dot dot-pageIn" style="border-radius:10px"></div><div class="anim-name">pageIn</div><div class="anim-easing">cubic-bezier(.16,1,.3,1)</div></div>
                <div class="anim-card"><div class="anim-dot dot-fadeUp" style="border-radius:10px"></div><div class="anim-name">fadeUp</div><div class="anim-easing">cubic-bezier(.16,1,.3,1)</div></div>
                <div class="anim-card"><div class="anim-dot dot-scaleIn" style="border-radius:10px"></div><div class="anim-name">scaleIn</div><div class="anim-easing">cubic-bezier(.16,1,.3,1)</div></div>
                <div class="anim-card"><div class="anim-dot dot-float" style="border-radius:10px"></div><div class="anim-name">float</div><div class="anim-easing">ease-in-out / 2s</div></div>
                <div class="anim-card"><div class="anim-dot dot-pulse" style="border-radius:10px"></div><div class="anim-name">pulse</div><div class="anim-easing">ease-in-out</div></div>
                <div class="anim-card"><div class="anim-dot dot-spin"></div><div class="anim-name">spin</div><div class="anim-easing">linear</div></div>
                </div>
            </div>

            <!-- SPAZIATURE -->
            <div class="ds-section">
                <div class="ds-label">Spaziature</div>
                <div class="sp-row">
                <?php
                $spaces = ['4'=>'sp-1','8'=>'sp-2','12'=>'sp-3','16'=>'sp-4','20'=>'sp-5','24'=>'sp-6','32'=>'sp-8','48'=>'sp-12'];
                foreach($spaces as $px => $name):
                ?>
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                    <div style="background:#f4f4f5;border:1px solid var(--border);height:<?= $px ?>px;width:<?= min($px*2,64) ?>px;border-radius:3px"></div>
                    <div style="font-size:.5625rem;font-family:var(--mono);color:var(--ink-3)"><?= $px ?>px</div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            </div>
        <?php elseif ($page === 'vision'): ?>
            <!-- VISION -->
            <div class="vision-wrap">
                <div class="vision-hero">
                    <div class="vision-flame"><img src="logo_icon.png" alt="Calcifer Logo" style="width:70px"></div>
                    <h1 class="vision-h1">Perché Calcifer?</h1>
                    <p class="vision-lead">In breve, un sistema di prestito tra persone che funziona come dovrebbe, diretto, trasparente e con fiducia al centro.</p>
                </div>
                <div class="vision-divider"></div>
                <div class="vision-section">
                    <div class="vision-h2">Il problema</div>
                    <p class="vision-p">Le banche tradizionali hanno processi lenti, tassi spesso poco chiari e una bassa relazione umana con chi chiede un prestito. Le persone che hanno bisogno di liquidità spesso non hanno accesso al credito, o lo ottengono a condizioni sfavorevoli (interessi alti). Dall'altra parte ci sono persone con risorse disponibili che vorrebbero farle fruttare senza affidarsi a prodotti finanziari anonimi.</p>
                    <p class="vision-p">Calcifer nasce per mettere queste due realtà molto semplici in contatto direto, costruendo fiducia attraverso la trasparenza (logica del credit score).</p>
                </div>
                <div class="vision-divider"></div>
                <div class="vision-section">
                    <div class="vision-h2">Il nome e il logo</div>
                    <p class="vision-p">Calcifer è il demone del fuoco nel film dello studio Ghibli "Il castello errante di Howl", un personaggio legato a un patto che lega alla fiducia reciproca Calcifer e Howl. Nel film, Calcifer alimenta il castello, parallelamente nella piattaforma, i "Calcifer" alimentano i prestiti degli "Howl".</p>
                    <p class="vision-p">Il logo e le illustrazioni dei livelli sono una fiamma stilizzata, che riprende il vero Calcifer.</p>
                </div>
                <div class="vision-divider"></div>
                <div class="vision-section">
                    <div class="vision-h2">I due ruoli</div>
                    <p class="vision-p">Ogni utente può essere sia <strong>Howl</strong> che <strong>Calcifer</strong>, non ci sono ruoli fissi.
                </div>
                <div class="vision-divider"></div>
                <div class="vision-section">
                    <div class="vision-h2">CS gamificato</div>
                        <p class="vision-p">Il sistema di CS (credit score) non è un numero casuale, si divide in quattro livelli, ognuno con un nome e un personaggio visivo, viene calcolato in base al percorso nell'app dell'utente, permettendogli di dimostrarsi affidabile.</p>
                        <div class="levels-grid" style="margin-top:8px">
                        <div class="level-card">
                            <div class="level-dot" style="background:#f59e0b"></div>
                            <div class="level-name">Scintilla</div>
                            <div class="level-range">0 – 125 pt</div>
                            <img src="assets/img/1.png" alt="Scintilla" style="width: 50%;margin-top: 18px;">
                        </div>
                        <div class="level-card">
                            <div class="level-dot" style="background:#f97316"></div>
                            <div class="level-name">Fiammella</div>
                            <div class="level-range">126 – 250 pt</div>
                            <img src="assets/img/2.png" alt="Fiammella" style="width: 50%;margin-top: 18px;">
                        </div>
                        <div class="level-card">
                            <div class="level-dot" style="background:#ef4444"></div>
                            <div class="level-name">Fuoco</div>
                            <div class="level-range">251 – 375 pt</div>
                            <img src="assets/img/3.png" alt="Fuoco" style="width: 50%;margin-top: 18px;">
                        </div>
                        <div class="level-card">
                            <div class="level-dot" style="background:#be123c"></div>
                            <div class="level-name">Calcifer</div>
                            <div class="level-range">376 – 500 pt</div>
                            <img src="assets/img/4.png" alt="Calcifer" style="width: 50%;margin-top: 18px;">    
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </body>
</html>