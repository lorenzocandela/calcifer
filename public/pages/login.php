<div class="auth-shell">

  <div style="display:flex;flex-direction:column;align-items:center;gap:var(--sp-6)">
    <img src="/logo_icon.png" alt="Calcifer" style="height:52px;width:auto;">

    <div class="tabs" style="width:100%">
      <button class="tab active" id="tab-login">Accedi</button>
      <button class="tab" id="tab-register">Registrati</button>
    </div>
  </div>

  <div id="form-login" class="auth-form">
    <div class="field" id="field-login-email">
      <label class="field-label" for="login-email">Username o Email</label>
      <div class="input-wrap">
        <span class="ms ms-sm">person</span>
        <input class="input" type="text" id="login-email" placeholder="username o email" autocomplete="username" autocapitalize="none">
      </div>
      <span class="field-error" id="login-email-error"></span>
    </div>
    <div class="field" id="field-login-password">
      <label class="field-label" for="login-password">Password</label>
      <div class="input-wrap">
        <span class="ms ms-sm">lock</span>
        <input class="input" type="password" id="login-password" placeholder="••••••••" autocomplete="current-password">
      </div>
      <span class="field-error" id="login-password-error"></span>
    </div>
    <button class="btn btn-primary btn-full btn-lg" id="login-btn" style="margin-top:var(--sp-2)">
      <span class="btn-label">Accedi</span>
    </button>
    <button class="btn btn-soft btn-full btn-sm" id="test-btn" type="button">
      Entra come test
    </button>
  </div>

  <div id="form-register" class="auth-form" style="display:none">
    <div class="field" id="field-reg-username">
      <label class="field-label" for="reg-username">Username</label>
      <div class="input-wrap">
        <span class="ms ms-sm">alternate_email</span>
        <input class="input" type="text" id="reg-username" placeholder="il_tuo_nome" autocomplete="username" autocapitalize="none">
      </div>
      <span class="field-error" id="reg-username-error"></span>
    </div>
    <div class="field" id="field-reg-email">
      <label class="field-label" for="reg-email">Email</label>
      <div class="input-wrap">
        <span class="ms ms-sm">mail</span>
        <input class="input" type="email" id="reg-email" placeholder="tu@email.com" autocomplete="email" inputmode="email">
      </div>
      <span class="field-error" id="reg-email-error"></span>
    </div>
    <div class="field" id="field-reg-password">
      <label class="field-label" for="reg-password">Password</label>
      <div class="input-wrap">
        <span class="ms ms-sm">lock</span>
        <input class="input" type="password" id="reg-password" placeholder="min. 8 caratteri" autocomplete="new-password">
      </div>
      <span class="field-error" id="reg-password-error"></span>
    </div>
    <button class="btn btn-primary btn-full btn-lg" id="register-btn" style="margin-top:var(--sp-2)">
      <span class="btn-label">Crea account</span>
    </button>
  </div>

</div>