const BASE = '/api';

// token & user store
export const token = {
    get:   ()      => localStorage.getItem('cf_token'),
    set:   (t)     => {
        localStorage.setItem('cf_token', t);
        document.cookie = `calcifer_auth=1;max-age=${60 * 60 * 24 * 7};path=/;SameSite=Lax`;
    },
    clear: ()      => {
        localStorage.removeItem('cf_token');
        document.cookie = 'calcifer_auth=;max-age=0;path=/';
    },
};

export const userStore = {
    get:   ()    => JSON.parse(localStorage.getItem('cf_user') || 'null'),
    set:   (u)   => localStorage.setItem('cf_user', JSON.stringify(u)),
    clear: ()    => localStorage.removeItem('cf_user'),
};

// core fetch
async function req(file, body = null, method = 'POST') {
    const headers = { 'Content-Type': 'application/json' };
    const t = token.get();
    if (t) headers['Authorization'] = `Bearer ${t}`;

    const opts = { method, headers };
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(`${BASE}/${file}`, opts);

    if (res.status === 401) {
        Auth.logout();
        return new Promise(() => {});
    }

    const data = await res.json();
    if (!res.ok) throw { status: res.status, message: data.error || 'Errore sconosciuto' };
    return data;
}

// auth
export const Auth = {
    async login(login, password) {
        const data = await req('auth.php', { action: 'login', login, email: login, password });
        token.set(data.token);
        userStore.set(data.user);
        return data;
    },
    async register(username, email, password) {
        return req('auth.php', { action: 'register', username, email, password });
    },
    logout() {
        token.clear();
        userStore.clear();
        window.location.href = '/?p=login';
    },
    isLoggedIn() {
        return !!token.get();
    },
};

// users
export const Users = {
    me() { return req('users.php', { action: 'me' }); },
};

// loans
export const Loans = {
    feed()                                  { return req('loans.php', { action: 'feed' }); },
    mine()                                  { return req('loans.php', { action: 'mine' }); },
    detail(id)                              { return req('loans.php', { action: 'detail', id }); },
    create(amount, reason, duration_months) { return req('loans.php', { action: 'create', amount, reason, duration_months }); },
};

// chats
export const Chats = {
    list()                                              { return req('chats.php', { action: 'list' }); },
    create(loan_id, amount, duration_months, interest_rate) {
        return req('chats.php', { action: 'create', loan_id, amount, duration_months, interest_rate });
    },
    delete(chat_id) { return req('chats.php', { action: 'delete', chat_id }); },
};

// messages
export const Messages = {
    fetch(chat_id)          { return req('messages.php', { action: 'fetch', chat_id }); },
    send(chat_id, content)  { return req('messages.php', { action: 'send', chat_id, content }); },
    markRead(chat_id)       { return req('messages.php', { action: 'mark_read', chat_id }); },
};

// proposals
export const Proposals = {
    accept(proposal_id) { return req('proposals.php', { action: 'accept', proposal_id }); },
    reject(proposal_id) { return req('proposals.php', { action: 'reject', proposal_id }); },
};

// dashboard
export const Dashboard = {
    analytics() { return req('dashboard.php', null, 'GET'); },
};

// notifications
export const Notifications = {
    list()                      { return req('notifications.php', { action: 'list' }); },
    markRead(id, all = false)   { return req('notifications.php', { action: 'mark_read', notification_id: id, all }); },
    deleteAll()                 { return req('notifications.php', { action: 'delete_all' }); },
};