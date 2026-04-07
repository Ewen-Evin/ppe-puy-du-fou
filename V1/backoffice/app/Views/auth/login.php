<div class="d-flex align-items-center justify-content-center vh-100">
  <form method="post" action="<?= $url('/login') ?>" class="card shadow-sm p-4" style="min-width:340px">
    <h4 class="mb-3">Connexion gestionnaire</h4>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <div class="mb-2">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required autofocus>
    </div>
    <div class="mb-3">
      <label class="form-label">Mot de passe</label>
      <input type="password" name="mot_de_passe" class="form-control" required>
    </div>
    <button class="btn btn-dark w-100">Se connecter</button>
  </form>
</div>
