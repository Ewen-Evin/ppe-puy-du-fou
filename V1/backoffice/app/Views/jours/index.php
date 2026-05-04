<?php
$rows = array_filter($items, fn($j) => is_array($j) && isset($j['id_jours']));
$today = date('Y-m-d');

// Group by month for mini calendar
$byMonth = [];
foreach ($rows as $j) {
    $m = substr($j['id_jours'], 0, 7);
    $byMonth[$m][] = $j['id_jours'];
}
ksort($byMonth);
$months_fr = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
?>
<div class="page-head">
  <div class="page-head-left">
    <h1>Jours d'ouverture</h1>
    <p>Dates d'exploitation du parc et horaires d'accès</p>
  </div>
</div>

<div class="row g-4">
  <!-- FORM STICKY -->
  <div class="col-lg-4">
    <div style="position:sticky;top:calc(var(--tb-h) + 16px);display:flex;flex-direction:column;gap:12px">

      <div class="bo-card">
        <div class="bo-card-head">
          <div style="display:flex;align-items:center;gap:9px">
            <div style="width:30px;height:30px;border-radius:var(--r-sm);background:var(--success-l);display:flex;align-items:center;justify-content:center">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/></svg>
            </div>
            <span class="bo-card-title">Ajouter / Modifier</span>
          </div>
        </div>
        <div class="bo-card-body">
          <form method="post" action="<?= $url('/jours') ?>">
            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
            <div class="f-group">
              <label class="f-label" for="j_date">Date <span style="color:var(--danger)">*</span></label>
              <input class="f-control" type="date" id="j_date" name="id_jours" required>
              <div class="f-hint">Si la date existe déjà, les horaires seront mis à jour.</div>
            </div>
            <div class="row g-2">
              <div class="col-6">
                <div class="f-group" style="margin-bottom:0">
                  <label class="f-label" for="h_ouv">
                    <span style="display:inline-flex;align-items:center;gap:4px">
                      <span style="width:7px;height:7px;background:var(--success);border-radius:50%;display:inline-block"></span>
                      Ouverture
                    </span>
                  </label>
                  <input class="f-control" type="time" id="h_ouv" name="heure_ouverture" value="09:30" step="60" required>
                </div>
              </div>
              <div class="col-6">
                <div class="f-group" style="margin-bottom:0">
                  <label class="f-label" for="h_ferm">
                    <span style="display:inline-flex;align-items:center;gap:4px">
                      <span style="width:7px;height:7px;background:var(--danger);border-radius:50%;display:inline-block"></span>
                      Fermeture
                    </span>
                  </label>
                  <input class="f-control" type="time" id="h_ferm" name="heure_fermeture" value="19:30" step="60" required>
                </div>
              </div>
            </div>
            <div style="margin-top:18px">
              <button type="submit" class="btn-bo btn-pri" style="width:100%">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Enregistrer
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- MINI CALENDAR PREVIEW -->
      <?php if (!empty($byMonth)): ?>
      <?php $firstMonth = array_key_first($byMonth); $parts = explode('-', $firstMonth); ?>
      <div class="bo-card">
        <div class="bo-card-head">
          <span class="bo-card-title" style="font-size:13px">Aperçu — <?= $months_fr[$parts[1]] ?? '' ?> <?= $parts[0] ?></span>
          <span style="font-size:11.5px;color:var(--txt3)"><?= count($byMonth[$firstMonth]) ?> jour<?= count($byMonth[$firstMonth]) > 1 ? 's' : '' ?></span>
        </div>
        <div class="bo-card-body" style="padding:14px">
          <?php
          $yr = (int)$parts[0]; $mo = (int)$parts[1];
          $firstDay = date('N', mktime(0,0,0,$mo,1,$yr)); // 1=Mon
          $daysInMonth = date('t', mktime(0,0,0,$mo,1,$yr));
          $openDays = $byMonth[$firstMonth];
          ?>
          <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;text-align:center">
            <?php foreach (['L','M','M','J','V','S','D'] as $d): ?>
            <div style="font-size:9.5px;font-weight:700;color:var(--txt3);padding:2px 0"><?= $d ?></div>
            <?php endforeach; ?>
            <?php for ($i = 1; $i < $firstDay; $i++): ?>
            <div></div>
            <?php endfor; ?>
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
            <?php $dateStr = sprintf('%04d-%02d-%02d', $yr, $mo, $d); $isOpen = in_array($dateStr, $openDays); $isToday = $dateStr === $today; ?>
            <div style="
              width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;
              border-radius:50%;font-size:11px;font-weight:<?= $isOpen ? '700' : '400' ?>;
              background:<?= $isOpen ? 'var(--success)' : 'transparent' ?>;
              color:<?= $isOpen ? '#fff' : ($isToday ? 'var(--primary)' : 'var(--txt3)') ?>;
              outline:<?= $isToday && !$isOpen ? '2px solid var(--primary)' : 'none' ?>;
              outline-offset:1px;
            "><?= $d ?></div>
            <?php endfor; ?>
          </div>
          <div style="display:flex;align-items:center;gap:12px;margin-top:10px;font-size:11.5px;color:var(--txt3)">
            <span style="display:flex;align-items:center;gap:4px"><span style="width:8px;height:8px;background:var(--success);border-radius:50%"></span>Ouvert</span>
            <span style="display:flex;align-items:center;gap:4px"><span style="width:8px;height:8px;border-radius:50%;border:2px solid var(--primary)"></span>Aujourd'hui</span>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- TABLE -->
  <div class="col-lg-8">
    <div class="bo-card">
      <div class="bo-card-head">
        <span class="bo-card-title"><?= count($rows) ?> jour<?= count($rows) > 1 ? 's' : '' ?> d'ouverture</span>
        <?php if (!empty($rows)): ?>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt3)">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <?php
          $totalMins = 0;
          foreach ($rows as $j) {
              $h1 = explode(':', $j['heure_ouverture']); $h2 = explode(':', $j['heure_fermeture']);
              $totalMins += ((int)$h2[0]*60+(int)$h2[1]) - ((int)$h1[0]*60+(int)$h1[1]);
          }
          $avg = count($rows) > 0 ? round($totalMins/count($rows)) : 0;
          ?>
          Moy. <?= intdiv($avg,60) ?>h<?= str_pad($avg%60,2,'0',STR_PAD_LEFT) ?>/jour
        </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($rows)): ?>
      <div class="table-search-wrap">
        <div class="table-search">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="joursSearch" placeholder="Filtrer par date (ex. 2025-06)…" autocomplete="off">
        </div>
      </div>
      <?php endif; ?>

      <div class="bo-table-wrap">
        <?php if (empty($rows)): ?>
        <div class="empty-st">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <p>Aucun jour d'ouverture défini</p>
          <p style="font-size:12px;margin-top:-8px">Utilisez le formulaire ci-contre pour en ajouter.</p>
        </div>
        <?php else: ?>
        <table class="bo-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Ouverture</th>
              <th>Fermeture</th>
              <th>Durée</th>
              <th style="width:70px"></th>
            </tr>
          </thead>
          <tbody id="joursBody">
          <?php foreach ($rows as $j):
            $h1 = explode(':', $j['heure_ouverture']);
            $h2 = explode(':', $j['heure_fermeture']);
            $dur = ((int)$h2[0]*60+(int)$h2[1]) - ((int)$h1[0]*60+(int)$h1[1]);
            $isToday = $j['id_jours'] === $today;
            $isPast  = $j['id_jours'] < $today;
          ?>
            <tr class="jour-row" data-search="<?= htmlspecialchars($j['id_jours']) ?>">
              <td>
                <span style="font-weight:600;font-size:13.5px<?= $isPast ? ';opacity:.5' : '' ?>">
                  <?= htmlspecialchars($j['id_jours']) ?>
                </span>
                <?php if ($isToday): ?>
                <span style="margin-left:6px;font-size:10px;font-weight:700;background:var(--primary);color:#fff;padding:1px 7px;border-radius:20px;vertical-align:middle">Aujourd'hui</span>
                <?php endif; ?>
              </td>
              <td>
                <span style="font-family:monospace;font-size:13px;color:var(--success);font-weight:600<?= $isPast ? ';opacity:.5' : '' ?>">
                  <?= htmlspecialchars(substr($j['heure_ouverture'],0,5)) ?>
                </span>
              </td>
              <td>
                <span style="font-family:monospace;font-size:13px;color:var(--danger);font-weight:600<?= $isPast ? ';opacity:.5' : '' ?>">
                  <?= htmlspecialchars(substr($j['heure_fermeture'],0,5)) ?>
                </span>
              </td>
              <td>
                <span style="font-size:12px;color:var(--txt2);background:var(--bg);padding:2px 8px;border-radius:20px;font-weight:500">
                  <?= intdiv($dur,60) ?>h<?= str_pad($dur%60,2,'0',STR_PAD_LEFT) ?>
                </span>
              </td>
              <td>
                <div class="td-actions">
                  <form method="post" action="<?= $url('/jours/' . urlencode($j['id_jours']) . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer le jour d\'ouverture du <?= htmlspecialchars($j['id_jours']) ?> ?')">
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
        <div id="joursNoResults" style="display:none">
          <div class="empty-st">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <p>Aucun résultat</p>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var inp=document.getElementById('joursSearch');
  if(!inp)return;
  var rows=document.querySelectorAll('.jour-row'),
      noRes=document.getElementById('joursNoResults');
  inp.addEventListener('input',function(){
    var q=this.value.toLowerCase().trim();
    var vis=0;
    rows.forEach(function(r){var m=!q||r.dataset.search.includes(q);r.style.display=m?'':'none';if(m)vis++;});
    if(noRes)noRes.style.display=vis===0?'block':'none';
  });
})();
</script>
