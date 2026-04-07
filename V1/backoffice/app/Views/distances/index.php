<h2 class="mb-3">Distances entre lieux</h2>
<div class="row">
  <div class="col-md-5">
    <form method="post" action="<?= $url('/distances') ?>" class="card p-3 bg-white shadow-sm mb-3">
      <input type="hidden" name="_csrf" value="<?= $csrf ?>">
      <h5>Ajouter une arête</h5>
      <div class="mb-2">
        <label class="form-label">Lieu A</label>
        <select name="id_lieu" class="form-select" required>
          <option value="">--</option>
          <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
            <option value="<?= (int)$l['id_lieu'] ?>"><?= htmlspecialchars($l['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">Lieu B</label>
        <select name="id_lieu_1" class="form-select" required>
          <option value="">--</option>
          <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
            <option value="<?= (int)$l['id_lieu'] ?>"><?= htmlspecialchars($l['nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-2">
        <label class="form-label">Distance (mètres)</label>
        <input type="number" min="1" name="distance_metres" class="form-control" required>
      </div>
      <button class="btn btn-primary">Ajouter</button>
    </form>
  </div>
  <div class="col-md-7">
    <table class="table table-sm bg-white shadow-sm">
      <thead><tr><th>De</th><th>Vers</th><th>Distance</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $d): if (!is_array($d) || !isset($d['id_lieu'])) continue; ?>
        <tr>
          <td><?= htmlspecialchars($byId[(int)$d['id_lieu']] ?? $d['id_lieu']) ?></td>
          <td><?= htmlspecialchars($byId[(int)$d['id_lieu_1']] ?? $d['id_lieu_1']) ?></td>
          <td><?= (int)$d['distance_metres'] ?> m</td>
          <td class="text-end">
            <form method="post" action="<?= $url('/distances/' . (int)$d['id_lieu'] . '/' . (int)$d['id_lieu_1'] . '/delete') ?>" onsubmit="return confirm('Supprimer ?')">
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
