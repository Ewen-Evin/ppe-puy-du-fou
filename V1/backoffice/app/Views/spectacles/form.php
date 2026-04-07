<?php
$isEdit = !empty($item['id_spectacle']);
$action = $isEdit ? $url('/spectacles/' . $item['id_spectacle'] . '/edit') : $url('/spectacles');
?>
<h2><?= $isEdit ? 'Modifier' : 'Nouveau' ?> spectacle</h2>
<form method="post" action="<?= $action ?>" class="card p-3 bg-white shadow-sm">
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <div class="mb-2">
    <label class="form-label">Libellé</label>
    <input class="form-control" name="libelle" required value="<?= htmlspecialchars($item['libelle'] ?? '') ?>">
  </div>
  <div class="mb-2">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
  </div>
  <div class="row">
    <div class="col-md-4 mb-2">
      <label class="form-label">Durée spectacle (HH:MM:SS)</label>
      <input class="form-control" name="duree_spectacle" value="<?= htmlspecialchars($item['duree_spectacle'] ?? '00:30:00') ?>">
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Durée attente (HH:MM:SS)</label>
      <input class="form-control" name="duree_attente" value="<?= htmlspecialchars($item['duree_attente'] ?? '00:00:00') ?>">
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Lieu</label>
      <select name="id_lieu" class="form-select" required>
        <option value="">--</option>
        <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
          <option value="<?= (int)$l['id_lieu'] ?>" <?= (int)($item['id_lieu'] ?? 0) === (int)$l['id_lieu'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($l['nom']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
    <a href="<?= $url('/spectacles') ?>" class="btn btn-secondary">Annuler</a>
  </div>
</form>
