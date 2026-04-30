<h2 class="mb-3">Seances</h2>

<div class="row">
  <!-- Formulaire d'ajout rapide -->
  <div class="col-md-4">
    <form method="post" action="<?= $url('/seances') ?>" class="card p-3 bg-white shadow-sm mb-3">
      <input type="hidden" name="_csrf" value="<?= $csrf ?>">
      <h5>Ajouter une seance</h5>
      <div class="mb-2">
        <label class="form-label">Spectacle</label>
        <select name="id_spectacle" class="form-select" required>
          <option value="">--</option>
          <?php foreach ($spectacles as $sp): if (!is_array($sp) || !isset($sp['id_spectacle'])) continue; ?>
            <option value="<?= (int)$sp['id_spectacle'] ?>"><?= htmlspecialchars($sp['libelle']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">Date</label>
        <input type="date" name="date_seance" class="form-control" required value="<?= htmlspecialchars($date) ?>">
      </div>
      <div class="row">
        <div class="col mb-2">
          <label class="form-label">Debut</label>
          <input type="time" name="heure_debut" class="form-control" step="1" required>
        </div>
        <div class="col mb-2">
          <label class="form-label">Fin</label>
          <input type="time" name="heure_fin" class="form-control" step="1" required>
        </div>
      </div>
      <button class="btn btn-primary w-100">Ajouter</button>
    </form>

    <!-- Filtre par date -->
    <form method="get" action="<?= $url('/seances') ?>" class="card p-3 bg-white shadow-sm">
      <h5>Filtrer</h5>
      <div class="mb-2">
        <label class="form-label">Date</label>
        <input type="date" name="date" value="<?= htmlspecialchars($date) ?>" class="form-control">
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary flex-fill">Filtrer</button>
        <a href="<?= $url('/seances') ?>" class="btn btn-outline-dark flex-fill">Tout voir</a>
      </div>
    </form>
  </div>

  <!-- Liste des seances -->
  <div class="col-md-8">
    <?php
    // Grouper par date
    $byDate = [];
    foreach ($items as $s) {
        if (!is_array($s) || !isset($s['id_seance'])) continue;
        $d = $s['date_seance'] ?? 'Inconnue';
        $byDate[$d][] = $s;
    }
    ksort($byDate);
    ?>

    <?php if (empty($byDate)): ?>
      <div class="alert alert-secondary">
        Aucune seance trouvee<?= $date ? ' pour le ' . htmlspecialchars($date) : '' ?>.
      </div>
    <?php endif; ?>

    <?php foreach ($byDate as $dateGroup => $seances): ?>
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
          <strong><?= htmlspecialchars($dateGroup) ?></strong>
          <span class="badge bg-light text-dark"><?= count($seances) ?> seance<?= count($seances) > 1 ? 's' : '' ?></span>
        </div>
        <table class="table table-striped mb-0">
          <thead><tr><th>Spectacle</th><th>Debut</th><th>Fin</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($seances as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['libelle'] ?? '') ?></td>
              <td><?= htmlspecialchars($s['heure_debut']) ?></td>
              <td><?= htmlspecialchars($s['heure_fin']) ?></td>
              <td class="text-end">
                <form method="post" action="<?= $url('/seances/' . (int)$s['id_seance'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ?')">
                  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                  <button class="btn btn-sm btn-outline-danger">Suppr</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>
</div>
