<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo-wrap">
      <div class="login-logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
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
          <input class="f-control" type="email" id="l-email" name="email"
                 placeholder="admin@puydufou.fr" required autocomplete="email" autofocus>
        </div>
        <div class="f-group" style="margin-bottom:24px">
          <label class="f-label" for="l-pwd">Mot de passe</label>
          <input class="f-control" type="password" id="l-pwd" name="mot_de_passe"
                 placeholder="••••••••" required autocomplete="current-password">
        </div>
        <button type="submit" class="login-btn">Se connecter</button>
      </form>
    </div>
  </div>
</div>
