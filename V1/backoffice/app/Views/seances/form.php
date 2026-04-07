<h2>Nouvelle séance</h2>
<form method="post" action="<?= $url('/seances') ?>" class="card p-3 bg-white shadow-sm">
  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
  <div class="mb-2">
    <label class="form-label">Spectacle</label>
    <select name="id_spectacle" class="form-select" required>
      <option value="">--</option>
      <?php foreach ($spectacles as $sp): if (!is_array($sp) || !isset($sp['id_spectacle'])) continue; ?>
        <option value="<?= (int)$sp['id_spectacle'] ?>"><?= htmlspecialchars($sp['libelle']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="row">
    <div class="col-md-4 mb-2">
      <label class="form-label">Date</label>
      <input type="date" name="date_seance" class="form-control" required>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Heure début</label>
      <input type="time" name="heure_debut" class="form-control" step="1" required>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label">Heure fin</label>
      <input type="time" name="heure_fin" class="form-control" step="1" required>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary">Créer</button>
    <a href="<?= $url('/seances') ?>" class="btn btn-secondary">Annuler</a>
  </div>
</form>
