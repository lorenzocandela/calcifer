import { Auth } from '/js/api.js';
import { toast } from '/js/app.js';

// tab toggle
const tabLogin    = document.getElementById('tab-login');
const tabRegister = document.getElementById('tab-register');
const formLogin   = document.getElementById('form-login');
const formRegister = document.getElementById('form-register');

function showTab(tab) {
    const isLogin = tab === 'login';
    tabLogin.classList.toggle('active', isLogin);
    tabRegister.classList.toggle('active', !isLogin);
    formLogin.style.display    = isLogin ? '' : 'none';
    formRegister.style.display = isLogin ? 'none' : '';
}

tabLogin.addEventListener('click', () => showTab('login'));
tabRegister.addEventListener('click', () => showTab('register'));

// helpers
function setFieldError(id, msg) {
    const field = document.getElementById(`field-${id}`);
    const err   = document.getElementById(`${id}-error`);
    if (!field || !err) return;
    field.classList.toggle('has-error', !!msg);
    err.textContent = msg || '';
}

function setLoading(btn, on) {
    btn.classList.toggle('loading', on);
    btn.disabled = on;
}

// login
async function submitLogin() {
    ['login-email','login-password'].forEach(f => setFieldError(f, ''));
    const email    = document.getElementById('login-email').value.trim();
    const password = document.getElementById('login-password').value;
    let valid = true;
    if (!email)    { setFieldError('login-email', 'Inserisci la tua email'); valid = false; }
    if (!password) { setFieldError('login-password', 'Inserisci la password'); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('login-btn');
    setLoading(btn, true);
    try {
        await Auth.login(email, password);
        window.location.href = '/?p=dashboard';
    } catch (e) {
        setLoading(btn, false);
        toast(e.message || 'Credenziali non valide', 'negative');
    }
}

document.getElementById('login-btn').addEventListener('click', submitLogin);

// login test TODO: da togliere in prod
const testBtn = document.getElementById('test-btn');
if (testBtn) {
    testBtn.addEventListener('click', async () => {
        testBtn.classList.add('loading');
        testBtn.disabled = true;
        try {
            await Auth.login('test@mail.com', 'testtest');
            window.location.href = '/?p=dashboard';
        } catch (e) {
            testBtn.classList.remove('loading');
            testBtn.disabled = false;
            toast('Account test non trovato — registralo prima', 'warning');
        }
    });
}

// register
async function submitRegister() {
    ['reg-username','reg-email','reg-password'].forEach(f => setFieldError(f, ''));
    const username = document.getElementById('reg-username').value.trim();
    const email    = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value;
    let valid = true;
    if (username.length < 3) { setFieldError('reg-username', 'Minimo 3 caratteri'); valid = false; }
    if (!email)               { setFieldError('reg-email', 'Inserisci la tua email'); valid = false; }
    if (password.length < 8)  { setFieldError('reg-password', 'Minimo 8 caratteri'); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('register-btn');
    setLoading(btn, true);
    try {
        await Auth.register(username, email, password);
        await Auth.login(email, password);
        window.location.href = '/?p=dashboard';
    } catch (e) {
        setLoading(btn, false);
        toast(e.message || 'Errore durante la registrazione', 'negative');
    }
}

document.getElementById('register-btn').addEventListener('click', submitRegister);

// in key
document.addEventListener('keydown', e => {
    if (e.key !== 'Enter') return;
    if (formLogin.style.display !== 'none') submitLogin();
    else submitRegister();
});