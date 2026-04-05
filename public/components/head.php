<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#EEEEF0">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <title>CALCIFER</title>
        <link rel="manifest" href="/manifest.json">
        <?php $v = time(); ?>
        <link rel="stylesheet" href="/css/global.css?v=<?= $v ?>">
        <link rel="stylesheet" href="/css/components.css?v=<?= $v ?>">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block">
    </head>
<body>
    <div id="loader"><div class="loader-ring"></div></div>
    <div id="toast-container"></div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(r => console.log('[SW] registered', r.scope))
                    .catch(e => console.warn('[SW] failed', e));
            });
        }
    </script>