<h2 class="mb-3">Jours d'ouverture</h2>
<div class="row">
  <div class="col-md-5">
    <form method="post" action="<?= $url('/jours') ?>" class="card p-3 bg-white shadow-sm mb-3">
      <input type="hidden" name="_csrf" value="<?= $csrf ?>">
      <h5>Ajouter / mettre à jour</h5>
      <div class="mb-2">
        <label class="form-label">Date</label>
        <input type="date" name="id_jours" class="form-control" required>
      </div>
      <div class="row">
        <div class="col mb-2">
          <label class="form-label">Ouverture</label>
          <input type="time" name="heure_ouverture" class="form-control" value="09:30:00" step="1" required>
        </div>
        <div class="col mb-2">
          <label class="form-label">Fermeture</label>
          <input type="time" name="heure_fermeture" class="form-control" value="19:30:00" step="1" required>
        </div>
      </div>
      <button class="btn btn-primary">Enregistrer</button>
    </form>
  </div>
  <div class="col-md-7">
    <table class="table bg-white shadow-sm">
      <thead><tr><th>Date</th><th>Ouverture</th><th>Fermeture</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
        <tr>
          <td><?= htmlspecialchars($j['id_jours']) ?></td>
          <td><?= htmlspecialchars($j['heure_ouverture']) ?></td>
          <td><?= htmlspecialchars($j['heure_fermeture']) ?></td>
          <td class="text-end">
            <form method="post" action="<?= $url('/jours/' . urlencode($j['id_jours']) . '/delete') ?>" onsubmit="return confirm('Supprimer ?')">
              <input type="hidden" name="_csrf" value="<?= $csrf ?>">
              <button class="btn btn-sm btn-outline-danger">Suppr</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
