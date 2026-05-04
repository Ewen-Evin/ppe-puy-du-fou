<div class="page-head">
  <div>
    <h1>Séances</h1>
    <p>Planification des représentations par jour</p>
  </div>
</div>

<div class="row g-4">
  <!-- FORMULAIRE AJOUT -->
  <div class="col-lg-4">
    <div class="bo-card" style="position:sticky;top:calc(var(--tb-h) + 16px)">
      <div class="bo-card-head">
        <span class="bo-card-title">Ajouter une séance</span>
      </div>
      <div class="bo-card-body">
        <form method="post" action="<?= $url('/seances') ?>">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <div class="f-group">
            <label class="f-label" for="id_sp">Spectacle <span style="color:var(--danger)">*</span></label>
            <select class="f-control" id="id_sp" name="id_spectacle" required>
              <option value="">Sélectionner…</option>
              <?php foreach ($spectacles as $sp): if (!is_array($sp) || !isset($sp['id_spectacle'])) continue; ?>
                <option value="<?= (int)$sp['id_spectacle'] ?>"><?= htmlspecialchars($sp['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="f-group">
            <label class="f-label" for="d_seance">Date <span style="color:var(--danger)">*</span></label>
            <?php if (empty($jours)): ?>
              <div style="font-size:13px;color:var(--warning);background:var(--warning-l);padding:9px 12px;border-radius:var(--r-sm);border:1px solid #FDE68A">
                Aucun jour d'ouverture défini — créez-en d'abord dans <a href="<?= $url('/jours') ?>" style="color:var(--warning);font-weight:600">Jours d'ouverture</a>.
              </div>
            <?php else: ?>
            <select class="f-control" id="d_seance" name="date_seance" required>
              <option value="">Sélectionner une date…</option>
              <?php foreach ($jours as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
                <option value="<?= htmlspecialchars($j['id_jours']) ?>" <?= ($date ?? '') === $j['id_jours'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($j['id_jours']) ?> (<?= htmlspecialchars(substr($j['heure_ouverture'],0,5)) ?>–<?= htmlspecialchars(substr($j['heure_fermeture'],0,5)) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php endif; ?>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="f-group" style="margin-bottom:0">
                <label class="f-label" for="h_deb">Début <span style="color:var(--danger)">*</span></label>
                <input class="f-control" type="time" id="h_deb" name="heure_debut" step="60" required>
              </div>
            </div>
            <div class="col-6">
              <div class="f-group" style="margin-bottom:0">
                <label class="f-label" for="h_fin">Fin <span style="color:var(--danger)">*</span></label>
                <input class="f-control" type="time" id="h_fin" name="heure_fin" step="60" required>
              </div>
            </div>
          </div>
          <div style="margin-top:18px">
            <button type="submit" class="btn-bo btn-pri" style="width:100%" <?= empty($jours) ? 'disabled' : '' ?>>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              Ajouter la séance
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- FILTRE -->
    <div class="bo-card" style="margin-top:16px">
      <div class="bo-card-head"><span class="bo-card-title">Filtrer par date</span></div>
      <div class="bo-card-body">
        <form method="get" action="<?= $url('/seances') ?>">
          <div class="f-group" style="margin-bottom:12px">
            <label class="f-label" for="f_date">Date</label>
            <select class="f-control" id="f_date" name="date">
              <option value="">Toutes les dates</option>
              <?php foreach ($jours as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
                <option value="<?= htmlspecialchars($j['id_jours']) ?>" <?= ($date ?? '') === $j['id_jours'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($j['id_jours']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display:flex;gap:8px">
            <button type="submit" class="btn-bo btn-sec" style="flex:1">Filtrer</button>
            <a href="<?= $url('/seances') ?>" class="btn-bo btn-ghost" style="flex:1;justify-content:center">Tout voir</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- LISTE SÉANCES -->
  <div class="col-lg-8">
    <?php
    $byDate = [];
    foreach ($items as $s) {
        if (!is_array($s) || !isset($s['id_seance'])) continue;
        $d = $s['date_seance'] ?? 'Inconnue';
        $byDate[$d][] = $s;
    }
    ksort($byDate);
    ?>
    <?php if (empty($byDate)): ?>
    <div class="bo-card">
      <div class="empty-st">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p>Aucune séance<?= $date ? ' pour le ' . htmlspecialchars($date) : '' ?></p>
      </div>
    </div>
    <?php endif; ?>
    <?php foreach ($byDate as $dateGroup => $seances): ?>
    <div class="dg">
      <div class="dg-head">
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-right:6px;vertical-align:-1px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <?= htmlspecialchars($dateGroup) ?>
        </span>
        <span class="dg-badge"><?= count($seances) ?> séance<?= count($seances) > 1 ? 's' : '' ?></span>
      </div>
      <table class="bo-table">
        <thead>
          <tr><th>Spectacle</th><th>Début</th><th>Fin</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($seances as $s): ?>
          <tr>
            <td><span style="font-weight:500"><?= htmlspecialchars($s['libelle'] ?? '—') ?></span></td>
            <td><span style="font-family:monospace;font-size:13px"><?= htmlspecialchars(substr($s['heure_debut'],0,5)) ?></span></td>
            <td><span style="font-family:monospace;font-size:13px;color:var(--txt3)"><?= htmlspecialchars(substr($s['heure_fin'],0,5)) ?></span></td>
            <td>
              <div class="td-actions">
                <form method="post" action="<?= $url('/seances/' . (int)$s['id_seance'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer cette séance ?')">
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
    </div>
    <?php endforeach; ?>
  </div>
</div>
