<?php
$byDate = [];
foreach ($items as $s) {
    if (!is_array($s) || !isset($s['id_seance'])) continue;
    $d = $s['date_seance'] ?? 'Inconnue';
    $byDate[$d][] = $s;
}
ksort($byDate);
$totalSeances = array_sum(array_map('count', $byDate));
?>
<div class="page-head">
  <div class="page-head-left">
    <h1>Séances</h1>
    <p>Planification des représentations par jour</p>
  </div>
</div>

<div class="row g-4">
  <!-- SIDEBAR FORM + FILTER -->
  <div class="col-lg-4">

    <!-- ADD FORM -->
    <div class="bo-card" style="position:sticky;top:calc(var(--tb-h) + 16px)">
      <div class="bo-card-head">
        <div style="display:flex;align-items:center;gap:9px">
          <div style="width:30px;height:30px;border-radius:var(--r-sm);background:#DBEAFE;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          <span class="bo-card-title">Ajouter une séance</span>
        </div>
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
              <div style="font-size:12.5px;color:#78350F;background:var(--warning-l);padding:10px 12px;border-radius:var(--r-sm);border:1px solid #FDE68A;line-height:1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:inline;vertical-align:-2px;margin-right:4px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Aucun jour d'ouverture — <a href="<?= $url('/jours') ?>" style="color:#78350F;font-weight:700;text-decoration:underline">en créer un</a>
              </div>
            <?php else: ?>
            <select class="f-control" id="d_seance" name="date_seance" required>
              <option value="">Sélectionner une date…</option>
              <?php foreach ($jours as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
                <option value="<?= htmlspecialchars($j['id_jours']) ?>" <?= ($date ?? '') === $j['id_jours'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($j['id_jours']) ?>
                  (<?= htmlspecialchars(substr($j['heure_ouverture'],0,5)) ?>–<?= htmlspecialchars(substr($j['heure_fermeture'],0,5)) ?>)
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

      <!-- FILTER -->
      <div style="border-top:1px solid var(--border);padding:14px 18px">
        <div style="font-size:12px;font-weight:700;color:var(--txt3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Filtrer par date</div>
        <form method="get" action="<?= $url('/seances') ?>">
          <div class="f-group" style="margin-bottom:10px">
            <select class="f-control" name="date" onchange="this.form.submit()">
              <option value="">Toutes les dates (<?= $totalSeances ?>)</option>
              <?php foreach ($jours as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
                <option value="<?= htmlspecialchars($j['id_jours']) ?>" <?= ($date ?? '') === $j['id_jours'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($j['id_jours']) ?>
                  <?php if (isset($byDate[$j['id_jours']])): ?>
                    (<?= count($byDate[$j['id_jours']]) ?>)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (!empty($date)): ?>
          <a href="<?= $url('/seances') ?>" class="btn-bo btn-ghost" style="width:100%;justify-content:center;font-size:12.5px">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Effacer le filtre
          </a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <!-- SÉANCES LIST -->
  <div class="col-lg-8">
    <?php if (empty($byDate)): ?>
    <div class="bo-card">
      <div class="empty-st">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <p>Aucune séance<?= $date ? ' pour le <strong>' . htmlspecialchars($date) . '</strong>' : '' ?></p>
        <?php if ($date): ?>
        <a href="<?= $url('/seances') ?>" class="btn-bo btn-ghost btn-sm">Voir toutes les séances</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php foreach ($byDate as $dateGroup => $seances): ?>
    <?php
    $ts = strtotime($dateGroup);
    $dateFr = $ts ? strftime('%A %d %B %Y', $ts) : $dateGroup;
    // Fallback if strftime not available
    if ($ts) {
        $days = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
        $months = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        $d = date('w',$ts); $m = (int)date('n',$ts); $day = (int)date('j',$ts); $yr = date('Y',$ts);
        $dateFr = ucfirst($days[$d]) . ' ' . $day . ' ' . $months[$m] . ' ' . $yr;
    }
    ?>
    <div class="dg">
      <div class="dg-head">
        <span style="display:flex;align-items:center;gap:8px">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <?= htmlspecialchars($dateFr) ?>
          <span style="font-size:11px;color:rgba(255,255,255,.45);font-weight:400"><?= htmlspecialchars($dateGroup) ?></span>
        </span>
        <span class="dg-badge"><?= count($seances) ?> séance<?= count($seances) > 1 ? 's' : '' ?></span>
      </div>
      <table class="bo-table">
        <thead>
          <tr>
            <th>Spectacle</th>
            <th style="width:90px">Début</th>
            <th style="width:90px">Fin</th>
            <th style="width:60px"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($seances as $s): ?>
          <tr>
            <td>
              <span style="font-weight:500;font-size:13.5px"><?= htmlspecialchars($s['libelle'] ?? '—') ?></span>
            </td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:4px;background:var(--success-l);color:#065F46;padding:2px 8px;border-radius:20px;font-size:11.5px;font-weight:600;font-variant-numeric:tabular-nums">
                <?= htmlspecialchars(substr($s['heure_debut'],0,5)) ?>
              </span>
            </td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:4px;background:var(--danger-l);color:#991B1B;padding:2px 8px;border-radius:20px;font-size:11.5px;font-weight:600;font-variant-numeric:tabular-nums">
                <?= htmlspecialchars(substr($s['heure_fin'],0,5)) ?>
              </span>
            </td>
            <td>
              <div class="td-actions">
                <form method="post" action="<?= $url('/seances/' . (int)$s['id_seance'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer cette séance ? Action irréversible.')">
                  <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                  <button type="submit" class="btn-bo btn-danger btn-sm btn-icon" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
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
