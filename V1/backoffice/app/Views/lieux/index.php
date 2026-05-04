<div class="page-head">
  <div>
    <h1>Lieux</h1>
    <p>Spectacles, zones, restaurants, hôtels et allées du parc</p>
  </div>
  <a href="<?= $url('/lieux/new') ?>" class="btn-bo btn-pri">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nouveau lieu
  </a>
</div>

<div class="bo-card">
  <div class="bo-card-head">
    <?php $rows = array_filter($items, fn($i) => is_array($i) && isset($i['id_lieu'])); ?>
    <span class="bo-card-title"><?= count($rows) ?> lieu<?= count($rows) > 1 ? 'x' : '' ?></span>
    <?php
    $types = ['spectacle','zone','restaurant','hotel','accueil','allee'];
    $byType = [];
    foreach ($rows as $l) { $t = $l['type_lieu'] ?? 'zone'; $byType[$t] = ($byType[$t] ?? 0) + 1; }
    ?>
    <div style="display:flex;gap:6px;flex-wrap:wrap">
      <?php foreach ($byType as $t => $n): ?>
        <span class="bo-badge b-<?= $t ?>">
          <?= ucfirst($t) ?> &nbsp;<strong><?= $n ?></strong>
        </span>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="bo-table-wrap">
    <?php if (empty($rows)): ?>
    <div class="empty-st">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <p>Aucun lieu enregistré</p>
    </div>
    <?php else: ?>
    <table class="bo-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom</th>
          <th>Type</th>
          <th>Coordonnées GPS</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $l): ?>
        <tr>
          <td><span style="color:var(--txt3);font-size:12px;font-weight:600"><?= (int)$l['id_lieu'] ?></span></td>
          <td><span style="font-weight:600"><?= htmlspecialchars($l['nom']) ?></span></td>
          <td>
            <?php $t = $l['type_lieu'] ?? 'zone'; ?>
            <span class="bo-badge b-<?= htmlspecialchars($t) ?>"><?= ucfirst(htmlspecialchars($t)) ?></span>
          </td>
          <td>
            <span style="font-family:monospace;font-size:12.5px;color:var(--txt3)">
              <?= htmlspecialchars($l['coordonnees_gps'] ?? '—') ?>
            </span>
          </td>
          <td>
            <div class="td-actions">
              <a href="<?= $url('/lieux/' . $l['id_lieu'] . '/edit') ?>" class="btn-bo btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Éditer
              </a>
              <form method="post" action="<?= $url('/lieux/' . $l['id_lieu'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce lieu ?')">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <button type="submit" class="btn-bo btn-danger btn-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  Suppr.
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
