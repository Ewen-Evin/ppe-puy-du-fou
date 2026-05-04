<div class="page-head">
  <div>
    <h1>Spectacles</h1>
    <p>Gestion des spectacles du parc</p>
  </div>
  <a href="<?= $url('/spectacles/new') ?>" class="btn-bo btn-pri">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nouveau spectacle
  </a>
</div>

<div class="bo-card">
  <div class="bo-card-head">
    <span class="bo-card-title">
      <?= count(array_filter($items, fn($i) => is_array($i) && isset($i['id_spectacle']))) ?> spectacle<?= count(array_filter($items, fn($i) => is_array($i) && isset($i['id_spectacle']))) > 1 ? 's' : '' ?>
    </span>
  </div>
  <div class="bo-table-wrap">
    <?php $rows = array_filter($items, fn($i) => is_array($i) && isset($i['id_spectacle'])); ?>
    <?php if (empty($rows)): ?>
    <div class="empty-st">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
      <p>Aucun spectacle enregistré</p>
    </div>
    <?php else: ?>
    <table class="bo-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Libellé</th>
          <th>Description</th>
          <th>Lieu</th>
          <th>Durée</th>
          <th>Attente</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $sp): ?>
        <tr>
          <td><span style="color:var(--txt3);font-size:12px;font-weight:600"><?= (int)$sp['id_spectacle'] ?></span></td>
          <td><span style="font-weight:600"><?= htmlspecialchars($sp['libelle']) ?></span></td>
          <td class="td-muted" style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($sp['description'] ?? '') ?>">
            <?= htmlspecialchars(mb_strimwidth($sp['description'] ?? '—', 0, 60, '…')) ?>
          </td>
          <td>
            <span style="display:flex;align-items:center;gap:5px;font-size:13px">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="var(--txt3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($sp['lieu_nom'] ?? '—') ?>
            </span>
          </td>
          <td><span style="font-family:monospace;font-size:13px"><?= htmlspecialchars(substr($sp['duree_spectacle'], 0, 5)) ?></span></td>
          <td><span style="font-family:monospace;font-size:13px;color:var(--txt3)"><?= htmlspecialchars(substr($sp['duree_attente'], 0, 5)) ?></span></td>
          <td>
            <div class="td-actions">
              <a href="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/edit') ?>" class="btn-bo btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Éditer
              </a>
              <form method="post" action="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce spectacle ?')">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <button type="submit" class="btn-bo btn-danger btn-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  Supprimer
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
