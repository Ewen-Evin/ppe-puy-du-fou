<div class="d-flex justify-content-between mb-3">
  <h2>Lieux</h2>
  <a href="<?= $url('/lieux/new') ?>" class="btn btn-primary">+ Nouveau</a>
</div>
<table class="table table-striped bg-white shadow-sm">
  <thead><tr><th>#</th><th>Nom</th><th>GPS</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
    <tr>
      <td><?= (int)$l['id_lieu'] ?></td>
      <td><?= htmlspecialchars($l['nom']) ?></td>
      <td><?= htmlspecialchars($l['coordonnees_gps']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-secondary" href="<?= $url('/lieux/' . $l['id_lieu'] . '/edit') ?>">Éditer</a>
        <form method="post" action="<?= $url('/lieux/' . $l['id_lieu'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <button class="btn btn-sm btn-outline-danger">Supprimer</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
