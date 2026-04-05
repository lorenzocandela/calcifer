<div id="app">

<!-- sidebar desktop -->
<nav id="sidebar">
    <div style="display:flex;flex-direction:column;gap:2px;">
        <a href="/?p=dashboard" class="sidebar-item" data-page="dashboard">
            <span class="ms">home</span> Dashboard
        </a>
        <a href="/?p=portfolio" class="sidebar-item" data-page="portfolio">
            <span class="ms">account_balance_wallet</span> Wallet
        </a>
        <a href="/?p=loans-new" class="sidebar-item" data-page="loans-new">
            <span class="ms">add_circle</span> Nuova richiesta
        </a>
        <a href="/?p=feed" class="sidebar-item" data-page="feed">
            <span class="ms">storefront</span> Bacheca
        </a>
        <a href="/?p=messages" class="sidebar-item" data-page="messages">
            <span class="ms">comic_bubble</span> Chat
        </a>
    </div>

    <div class="sidebar-user">
        <a href="/?p=profile" class="sidebar-item" data-page="profile">
            <div class="avatar avatar-md" id="sidebar-avatar"></div>
            <span id="sidebar-username" class="truncate"></span>
        </a>
        <button class="sidebar-item" id="sidebar-logout" style="color:var(--ink-3)">
            <span class="ms ms-sm">logout</span> Esci
        </button>
    </div>
</nav>

<!-- bottom nav mobile -->
<nav id="bottom-nav">
    <a href="/?p=dashboard" class="nav-item" data-page="dashboard">
        <span class="ms">home</span>
    </a>
    <a href="/?p=portfolio" class="nav-item" data-page="portfolio">
        <span class="ms">account_balance_wallet</span>
    </a>
    <a href="/?p=loans-new" class="nav-cta" data-page="loans-new">
        <span class="ms">add</span>
    </a>
    <a href="/?p=feed" class="nav-item" data-page="feed">
        <span class="ms">storefront</span>
    </a>
    <a href="/?p=messages" class="nav-item" data-page="messages">
        <span class="ms">comic_bubble</span>
    </a>
</nav>