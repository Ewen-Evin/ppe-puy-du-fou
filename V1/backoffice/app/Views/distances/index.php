<?php
$rows = array_filter($items, fn($d) => is_array($d) && isset($d['id_lieu']));

// Detect bidirectional edges
$edgeSet = [];
foreach ($rows as $d) {
    $edgeSet[$d['id_lieu'].'-'.$d['id_lieu_1']] = true;
}
?>
<div class="page-head">
  <div class="page-head-left">
    <h1>Distances</h1>
    <p>Graphe de distances entre les lieux du parc</p>
  </div>
</div>

<!-- STATS MINI -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="bo-card" style="padding:16px 18px;display:flex;align-items:center;gap:12px">
      <div class="stat-ico ico-blue" style="width:36px;height:36px">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/></svg>
      </div>
      <div>
        <div style="font-size:22px;font-weight:750;color:var(--txt1)"><?= count($rows) ?></div>
        <div style="font-size:12px;color:var(--txt2)">Arêtes</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="bo-card" style="padding:16px 18px;display:flex;align-items:center;gap:12px">
      <div class="stat-ico ico-gold" style="width:36px;height:36px">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div>
        <div style="font-size:22px;font-weight:750;color:var(--txt1)"><?= count($lieux) ?></div>
        <div style="font-size:12px;color:var(--txt2)">Lieux</div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="bo-card" style="padding:16px 18px;display:flex;align-items:center;gap:12px">
      <div class="stat-ico ico-indigo" style="width:36px;height:36px">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div>
        <?php
        $totalDist = 0; $distCount = 0;
        foreach ($rows as $_d) { if (isset($_d['distance_metres'])) { $totalDist += (int)$_d['distance_metres']; $distCount++; } }
        $avg = $distCount > 0 ? round($totalDist / $distCount) : 0;
        ?>
        <div style="font-size:22px;font-weight:750;color:var(--txt1)"><?= $avg ?><span style="font-size:13px;font-weight:500;color:var(--txt3)">m</span></div>
        <div style="font-size:12px;color:var(--txt2)">Distance moyenne</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- FORM -->
  <div class="col-lg-4">
    <div class="bo-card" style="position:sticky;top:calc(var(--tb-h) + 16px)">
      <div class="bo-card-head">
        <div style="display:flex;align-items:center;gap:9px">
          <div style="width:30px;height:30px;border-radius:var(--r-sm);background:#DBEAFE;display:flex;align-items:center;justify-content:center">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          <span class="bo-card-title">Ajouter une arête</span>
        </div>
      </div>
      <div class="bo-card-body">
        <form method="post" action="<?= $url('/distances') ?>">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">

          <div class="f-group">
            <label class="f-label" for="id_lieu_a">
              <span style="display:inline-flex;align-items:center;gap:5px">
                <span style="width:16px;height:16px;background:var(--primary);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff">A</span>
                Lieu de départ <span style="color:var(--danger)">*</span>
              </span>
            </label>
            <select class="f-control" id="id_lieu_a" name="id_lieu" required>
              <option value="">Sélectionner…</option>
              <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
                <option value="<?= (int)$l['id_lieu'] ?>"><?= htmlspecialchars($l['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:flex;justify-content:center;margin:-4px 0 8px;color:var(--txt3)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
          </div>

          <div class="f-group">
            <label class="f-label" for="id_lieu_b">
              <span style="display:inline-flex;align-items:center;gap:5px">
                <span style="width:16px;height:16px;background:var(--gold);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff">B</span>
                Lieu de destination <span style="color:var(--danger)">*</span>
              </span>
            </label>
            <select class="f-control" id="id_lieu_b" name="id_lieu_1" required>
              <option value="">Sélectionner…</option>
              <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
                <option value="<?= (int)$l['id_lieu'] ?>"><?= htmlspecialchars($l['nom']) ?></option>
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

  <!-- TABLE -->
  <div class="col-lg-8">
    <div class="bo-card">
      <div class="bo-card-head">
        <span class="bo-card-title"><?= count($rows) ?> arête<?= count($rows) > 1 ? 's' : '' ?></span>
        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--txt3)">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/><path d="M5 10V7l3 3-3 3"/><path d="M19 14v3l-3-3 3-3"/></svg>
          Graphe directionnel
        </div>
      </div>

      <?php if (!empty($rows)): ?>
      <div class="table-search-wrap">
        <div class="table-search">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="distSearch" placeholder="Rechercher un lieu…" autocomplete="off">
          <span class="search-count" id="distCount"></span>
        </div>
      </div>
      <?php endif; ?>

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
              <th style="width:30px"></th>
              <th>Vers</th>
              <th style="width:100px">Distance</th>
              <th style="width:90px"></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $d):
            $nameA = $byId[(int)$d['id_lieu']]  ?? '#'.$d['id_lieu'];
            $nameB = $byId[(int)$d['id_lieu_1']] ?? '#'.$d['id_lieu_1'];
            $isBidi = isset($edgeSet[$d['id_lieu_1'].'-'.$d['id_lieu']]);
            $dist = (int)$d['distance_metres'];
            $distColor = $dist < 100 ? 'var(--success)' : ($dist < 300 ? 'var(--primary)' : 'var(--warning)');
          ?>
            <tr class="dist-row" data-search="<?= htmlspecialchars(strtolower($nameA . ' ' . $nameB)) ?>">
              <td>
                <div style="display:flex;align-items:center;gap:7px">
                  <span style="width:22px;height:22px;background:var(--primary);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;flex-shrink:0">A</span>
                  <div>
                    <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($nameA) ?></div>
                    <div style="font-size:11px;color:var(--txt3)">#<?= (int)$d['id_lieu'] ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span style="color:var(--txt3);font-size:16px;display:block;text-align:center">
                  <?= $isBidi ? '⇄' : '→' ?>
                </span>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:7px">
                  <span style="width:22px;height:22px;background:var(--gold);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;flex-shrink:0">B</span>
                  <div>
                    <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($nameB) ?></div>
                    <div style="font-size:11px;color:var(--txt3)">#<?= (int)$d['id_lieu_1'] ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-family:monospace;font-size:14px;font-weight:700;color:<?= $distColor ?>">
                  <?= $dist ?>
                  <span style="color:var(--txt3);font-weight:400;font-size:11px">m</span>
                </span>
              </td>
              <td>
                <div class="td-actions">
                  <form method="post" action="<?= $url('/distances/' . (int)$d['id_lieu'] . '/' . (int)$d['id_lieu_1'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer l\'arête <?= htmlspecialchars($nameA) ?> → <?= htmlspecialchars($nameB) ?> ?')">
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
        <div id="distNoResults" style="display:none">
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
  var inp=document.getElementById('distSearch');
  if(!inp)return;
  var rows=document.querySelectorAll('.dist-row'),
      noRes=document.getElementById('distNoResults'),
      cnt=document.getElementById('distCount');
  inp.addEventListener('input',function(){
    var q=this.value.toLowerCase().trim(),vis=0;
    rows.forEach(function(r){var m=!q||r.dataset.search.includes(q);r.style.display=m?'':'none';if(m)vis++;});
    if(noRes)noRes.style.display=vis===0?'block':'none';
    if(cnt)cnt.textContent=q?vis+' résultat'+(vis>1?'s':''):'';
  });
})();
</script>
