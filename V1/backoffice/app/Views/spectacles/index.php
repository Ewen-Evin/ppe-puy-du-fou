<div class="d-flex justify-content-between mb-3">
  <h2>Spectacles</h2>
  <a href="<?= $url('/spectacles/new') ?>" class="btn btn-primary">+ Nouveau</a>
</div>
<table class="table table-striped bg-white shadow-sm">
  <thead><tr><th>#</th><th>Libellé</th><th>Lieu</th><th>Durée</th><th>Attente</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($items as $k => $sp): if (!is_array($sp) || !isset($sp['id_spectacle'])) continue; ?>
    <tr>
      <td><?= (int)$sp['id_spectacle'] ?></td>
      <td><?= htmlspecialchars($sp['libelle']) ?></td>
      <td><?= htmlspecialchars($sp['lieu_nom'] ?? '') ?></td>
      <td><?= htmlspecialchars($sp['duree_spectacle']) ?></td>
      <td><?= htmlspecialchars($sp['duree_attente']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-secondary" href="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/edit') ?>">Éditer</a>
        <form method="post" action="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ?')">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <button class="btn btn-sm btn-outline-danger">Supprimer</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
