<div class="login-wrap">
  <div class="login-orbs">
    <div class="login-orb orb1"></div>
    <div class="login-orb orb2"></div>
    <div class="login-orb orb3"></div>
  </div>

  <!-- Subtle grid overlay -->
  <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:40px 40px;pointer-events:none"></div>

  <div class="login-card">
    <div class="login-logo-wrap">
      <div class="login-logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div class="login-logo-title">Puy du Fou</div>
      <div class="login-logo-sub">Espace Gestionnaire</div>
    </div>

    <div class="login-box">
      <div class="login-title">Connexion</div>
      <div class="login-sub">Accès réservé aux gestionnaires du parc.</div>

      <?php if (!empty($error)): ?>
      <div class="login-err">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="post" action="<?= $url('/login') ?>">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">

        <div class="f-group">
          <label class="f-label" for="l-email">Adresse e-mail</label>
          <div style="position:relative">
            <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--txt3);line-height:0;pointer-events:none">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
            <input class="f-control" type="email" id="l-email" name="email" style="padding-left:36px"
                   placeholder="admin@puydufou.fr" required autocomplete="email" autofocus>
          </div>
        </div>

        <div class="f-group" style="margin-bottom:22px">
          <label class="f-label" for="l-pwd">Mot de passe</label>
          <div class="pw-wrap" style="position:relative">
            <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--txt3);line-height:0;pointer-events:none">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input class="f-control" type="password" id="l-pwd" name="mot_de_passe" style="padding-left:36px;padding-right:42px"
                   placeholder="••••••••" required autocomplete="current-password">
            <button type="button" class="pw-toggle" id="pwToggle" aria-label="Afficher le mot de passe" tabindex="-1">
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
        </div>

        <button type="submit" class="login-btn">
          Se connecter
        </button>
      </form>
    </div>

    <p style="text-align:center;margin-top:20px;font-size:12px;color:#374151;opacity:.5">
      &copy; <?= date('Y') ?> Puy du Fou — Backoffice v1.0
    </p>
  </div>
</div>

<script>
(function(){
  var btn=document.getElementById('pwToggle'),
      inp=document.getElementById('l-pwd'),
      ico=document.getElementById('eyeIcon');
  var EYE='<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
      EYEOFF='<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
  if(!btn)return;
  var vis=false;
  btn.addEventListener('click',function(){
    vis=!vis;
    inp.type=vis?'text':'password';
    ico.innerHTML=vis?EYEOFF:EYE;
  });
})();
</script>
