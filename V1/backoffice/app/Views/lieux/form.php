<?php
$isEdit = !empty($item['id_lieu']);
$action = $isEdit ? $url('/lieux/' . $item['id_lieu'] . '/edit') : $url('/lieux');
?>
<h2><?= $isEdit ? 'Modifier' : 'Nouveau' ?> lieu</h2>
<form method="post" action="<?= $action ?>" class="card p-3 bg-white shadow-sm">
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <div class="mb-2">
    <label class="form-label">Nom</label>
    <input class="form-control" name="nom" required value="<?= htmlspecialchars($item['nom'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Coordonnées GPS (lat,lng)</label>
    <input class="form-control" name="coordonnees_gps" required value="<?= htmlspecialchars($item['coordonnees_gps'] ?? '') ?>">
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
    <a href="<?= $url('/lieux') ?>" class="btn btn-secondary">Annuler</a>
  </div>
</form>
