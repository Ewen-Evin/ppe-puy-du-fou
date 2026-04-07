<div class="d-flex justify-content-between mb-3">
  <h2>Séances</h2>
  <a href="<?= $url('/seances/new') ?>" class="btn btn-primary">+ Nouvelle</a>
</div>
<form method="get" action="<?= $url('/seances') ?>" class="mb-3 d-flex gap-2 align-items-end">
  <div>
    <label class="form-label">Date</label>
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
  </div>
  <button class="btn btn-outline-secondary">Filtrer</button>
</form>
<table class="table table-striped bg-white shadow-sm">
  <thead><tr><th>#</th><th>Spectacle</th><th>Début</th><th>Fin</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $s): if (!is_array($s) || !isset($s['id_seance'])) continue; ?>
    <tr>
      <td><?= (int)$s['id_seance'] ?></td>
      <td><?= htmlspecialchars($s['libelle'] ?? '') ?></td>
      <td><?= htmlspecialchars($s['heure_debut']) ?></td>
      <td><?= htmlspecialchars($s['heure_fin']) ?></td>
      <td class="text-end">
        <form method="post" action="<?= $url('/seances/' . (int)$s['id_seance'] . '/delete') ?>" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <button class="btn btn-sm btn-outline-danger">Suppr</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
