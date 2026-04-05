const VERSION   = 'v1.0.2';
const CACHE     = `calcifer-${VERSION}`;
const API_PATHS = ['/api/'];

const PRECACHE = [
    '/manifest.json',
    '/css/global.css',
    '/css/components.css',
    '/js/app.js',
    '/js/api.js',
    '/logo_clean.png',
];

// install banner
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(cache => {
            return Promise.allSettled(
                PRECACHE.map(url =>
                    cache.add(new Request(url, { cache: 'reload' })).catch(() => {})
                )
            );
        }).then(() => self.skipWaiting())
    );
});

// act
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => k !== CACHE).map(k => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// fetch
self.addEventListener('fetch', e => {
    const { request } = e;
    const url = new URL(request.url);

    if (url.origin !== self.location.origin) return;

    if (API_PATHS.some(p => url.pathname.startsWith(p))) {
        e.respondWith(
            fetch(request).catch(() =>
                new Response(JSON.stringify({ error: 'Offline' }), {
                    status: 503,
                    headers: { 'Content-Type': 'application/json' },
                })
            )
        );
        return;
    }

    if (url.pathname.match(/\.(png|jpg|jpeg|webp|gif|svg|ico)$/)) {
        e.respondWith(
            caches.open(CACHE).then(cache =>
                cache.match(request).then(cached => {
                    if (cached) return cached;
                    return fetch(request).then(res => {
                        if (res.ok) cache.put(request, res.clone());
                        return res;
                    }).catch(() => new Response('', { status: 404 }));
                })
            )
        );
        return;
    }

    if (url.pathname.match(/\.(js|css)$/)) {
        e.respondWith(
            caches.open(CACHE).then(cache =>
                cache.match(request).then(cached => {
                    const fetchPromise = fetch(request).then(res => {
                        if (res.ok) cache.put(request, res.clone());
                        return res;
                    }).catch(() => cached);
                    return cached || fetchPromise;
                })
            )
        );
        return;
    }

    if (request.mode === 'navigate') {
        e.respondWith(
            fetch(request)
                .then(res => {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(request, clone));
                    return res;
                })
                .catch(() =>
                    caches.match('/').then(cached => cached || offlinePage())
                )
        );
        return;
    }

    e.respondWith(
        caches.match(request).then(cached => cached || fetch(request).catch(() => new Response('', { status: 404 })))
    );
});

function offlinePage() {
    return new Response(`<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>CALCIFER — Offline</title>
        <style>
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family:-apple-system,'DM Sans',sans-serif;background:#f4f4f5;min-height:100dvh;
            display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;padding:24px;text-align:center}
            h1{font-size:1.25rem;font-weight:800;color:#09090b;letter-spacing:-.03em}
            p{font-size:.9375rem;color:#71717a;font-weight:500;max-width:260px;line-height:1.5}
            button{margin-top:8px;padding:14px 28px;border-radius:999px;background:#09090b;color:#fff;
            font-size:.9375rem;font-weight:800;border:none;cursor:pointer;font-family:inherit}
        </style>
    </head>
    <body>
        <h1>Sei offline</h1>
        <p>Controlla la connessione e riprova.</p>
        <button onclick="location.reload()">Riprova</button>
    </body>
</html>`, { headers: { 'Content-Type': 'text/html' } });
}