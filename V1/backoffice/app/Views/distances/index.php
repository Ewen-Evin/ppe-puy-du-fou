<div class="page-head">
  <div>
    <h1>Distances</h1>
    <p>Graphe de distances entre les lieux du parc (algorithme de parcours)</p>
  </div>
</div>

<div class="row g-4">
  <!-- FORMULAIRE -->
  <div class="col-lg-4">
    <div class="bo-card" style="position:sticky;top:calc(var(--tb-h) + 16px)">
      <div class="bo-card-head">
        <span class="bo-card-title">Ajouter une arête</span>
      </div>
      <div class="bo-card-body">
        <form method="post" action="<?= $url('/distances') ?>">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <div class="f-group">
            <label class="f-label" for="id_lieu_a">Lieu A <span style="color:var(--danger)">*</span></label>
            <select class="f-control" id="id_lieu_a" name="id_lieu" required>
              <option value="">Sélectionner…</option>
              <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
                <option value="<?= (int)$l['id_lieu'] ?>">[<?= (int)$l['id_lieu'] ?>] <?= htmlspecialchars($l['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="f-group">
            <label class="f-label" for="id_lieu_b">Lieu B <span style="color:var(--danger)">*</span></label>
            <select class="f-control" id="id_lieu_b" name="id_lieu_1" required>
              <option value="">Sélectionner…</option>
              <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
                <option value="<?= (int)$l['id_lieu'] ?>">[<?= (int)$l['id_lieu'] ?>] <?= htmlspecialchars($l['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="f-group">
            <label class="f-label" for="dist_m">Distance (mètres) <span style="color:var(--danger)">*</span></label>
            <input class="f-control" type="number" id="dist_m" name="distance_metres" min="1" required placeholder="ex. 180">
          </div>
          <button type="submit" class="btn-bo btn-pri" style="width:100%">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter l'arête
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- TABLEAU -->
  <div class="col-lg-8">
    <div class="bo-card">
      <div class="bo-card-head">
        <?php $rows = array_filter($items, fn($d) => is_array($d) && isset($d['id_lieu'])); ?>
        <span class="bo-card-title"><?= count($rows) ?> arête<?= count($rows) > 1 ? 's' : '' ?></span>
        <span style="font-size:12px;color:var(--txt3)">Graphe directionnel</span>
      </div>
      <div class="bo-table-wrap">
        <?php if (empty($rows)): ?>
        <div class="empty-st">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/></svg>
          <p>Aucune distance enregistrée</p>
        </div>
        <?php else: ?>
        <table class="bo-table">
          <thead>
            <tr>
              <th>De</th>
              <th></th>
              <th>Vers</th>
              <th>Distance</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $d): ?>
            <tr>
              <td>
                <span style="font-weight:500;font-size:13px"><?= htmlspecialchars($byId[(int)$d['id_lieu']] ?? '#' . $d['id_lieu']) ?></span>
                <span style="font-size:11px;color:var(--txt3);margin-left:4px">#<?= (int)$d['id_lieu'] ?></span>
              </td>
              <td style="color:var(--txt3);font-size:16px">→</td>
              <td>
                <span style="font-weight:500;font-size:13px"><?= htmlspecialchars($byId[(int)$d['id_lieu_1']] ?? '#' . $d['id_lieu_1']) ?></span>
                <span style="font-size:11px;color:var(--txt3);margin-left:4px">#<?= (int)$d['id_lieu_1'] ?></span>
              </td>
              <td>
                <span style="font-family:monospace;font-size:13px;font-weight:600;color:var(--primary)"><?= (int)$d['distance_metres'] ?><span style="color:var(--txt3);font-weight:400;font-size:11px">m</span></span>
              </td>
              <td>
                <div class="td-actions">
                  <form method="post" action="<?= $url('/distances/' . (int)$d['id_lieu'] . '/' . (int)$d['id_lieu_1'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer cette arête ?')">
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
  </div>
</div>
