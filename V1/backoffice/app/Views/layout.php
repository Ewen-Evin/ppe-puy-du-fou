<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($GLOBALS['config']['app_name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #f6f7fb; }
  .navbar-brand { font-weight: 700; }
  .sidebar a { display:block; padding:.5rem .75rem; border-radius:.375rem; color:#333; text-decoration:none; }
  .sidebar a:hover, .sidebar a.active { background:#e7eaf3; }
  main { padding: 1.5rem; }
</style>
</head>
<body>
<?php if (!empty($_SESSION['user'])): ?>
<nav class="navbar navbar-dark bg-dark px-3">
  <a class="navbar-brand" href="<?= $url('/') ?>">Puy du Fou - Back-office</a>
  <div class="text-light">
    <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?>
    &middot; <a href="<?= $url('/logout') ?>" class="text-warning">Déconnexion</a>
  </div>
</nav>
<div class="container-fluid">
  <div class="row">
    <aside class="col-md-2 sidebar bg-white py-3 vh-100 border-end">
      <a href="<?= $url('/') ?>">Tableau de bord</a>
      <a href="<?= $url('/spectacles') ?>">Spectacles</a>
      <a href="<?= $url('/lieux') ?>">Lieux</a>
      <a href="<?= $url('/distances') ?>">Distances</a>
      <a href="<?= $url('/seances') ?>">Séances</a>
      <a href="<?= $url('/jours') ?>">Jours d'ouverture</a>
    </aside>
    <main class="col-md-10">
      <?php if (!empty($flash)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($flash) ?></div>
      <?php endif; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<?php else: ?>
  <?= $content ?>
<?php endif; ?>
</body>
</html>
