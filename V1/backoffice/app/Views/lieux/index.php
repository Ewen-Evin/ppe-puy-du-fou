<?php
$rows = array_filter($items, fn($i) => is_array($i) && isset($i['id_lieu']));
$types = ['spectacle' => 'Spectacle', 'zone' => 'Zone', 'restaurant' => 'Restaurant', 'hotel' => 'Hôtel', 'accueil' => 'Accueil', 'allee' => 'Allée'];
$byType = [];
foreach ($rows as $l) { $t = $l['type_lieu'] ?? 'zone'; $byType[$t] = ($byType[$t] ?? 0) + 1; }
?>
<div class="page-head">
  <div class="page-head-left">
    <h1>Lieux</h1>
    <p>Spectacles, zones, restaurants, hôtels et allées du parc</p>
  </div>
  <a href="<?= $url('/lieux/new') ?>" class="btn-bo btn-pri">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nouveau lieu
  </a>
</div>

<div class="bo-card">
  <div class="bo-card-head" style="flex-wrap:wrap;gap:10px">
    <span class="bo-card-title"><?= count($rows) ?> lieu<?= count($rows) > 1 ? 'x' : '' ?></span>
    <div class="filter-tabs" id="ltabs">
      <div class="ftab active" data-filter="all">
        Tous
        <span class="count"><?= count($rows) ?></span>
      </div>
      <?php foreach ($byType as $t => $n): ?>
      <div class="ftab" data-filter="<?= htmlspecialchars($t) ?>">
        <?= htmlspecialchars($types[$t] ?? ucfirst($t)) ?>
        <span class="count"><?= $n ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if (!empty($rows)): ?>
  <div class="table-search-wrap">
    <div class="table-search">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="lieuSearch" placeholder="Rechercher un lieu…" autocomplete="off">
      <span class="search-count" id="lieuCount"></span>
    </div>
  </div>
  <?php endif; ?>

  <div class="bo-table-wrap">
    <?php if (empty($rows)): ?>
    <div class="empty-st">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <p>Aucun lieu enregistré</p>
      <a href="<?= $url('/lieux/new') ?>" class="btn-bo btn-pri btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Créer le premier lieu
      </a>
    </div>
    <?php else: ?>
    <table class="bo-table">
      <thead>
        <tr>
          <th style="width:48px">#</th>
          <th>Nom</th>
          <th>Type</th>
          <th>Coordonnées GPS</th>
          <th style="width:120px"></th>
        </tr>
      </thead>
      <tbody id="lieuBody">
      <?php foreach ($rows as $l): $t = $l['type_lieu'] ?? 'zone'; ?>
        <tr class="lieu-row" data-type="<?= htmlspecialchars($t) ?>" data-search="<?= htmlspecialchars(strtolower($l['nom'] . ' ' . $t)) ?>">
          <td>
            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:var(--bg);border-radius:6px;font-size:11px;font-weight:700;color:var(--txt3)">
              <?= (int)$l['id_lieu'] ?>
            </span>
          </td>
          <td><span style="font-weight:600;font-size:13.5px"><?= htmlspecialchars($l['nom']) ?></span></td>
          <td>
            <span class="bo-badge b-<?= htmlspecialchars($t) ?>">
              <?= htmlspecialchars($types[$t] ?? ucfirst($t)) ?>
            </span>
          </td>
          <td>
            <?php if (!empty($l['coordonnees_gps'])): ?>
            <span style="font-family:monospace;font-size:12px;color:var(--txt3);background:var(--bg);padding:2px 8px;border-radius:var(--r-xs)">
              <?= htmlspecialchars($l['coordonnees_gps']) ?>
            </span>
            <?php else: ?>
            <span class="td-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="td-actions">
              <a href="<?= $url('/lieux/' . $l['id_lieu'] . '/edit') ?>" class="btn-bo btn-ghost btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Éditer
              </a>
              <form method="post" action="<?= $url('/lieux/' . $l['id_lieu'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce lieu ? Cette action est irréversible.')">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <button type="submit" class="btn-bo btn-danger btn-sm" title="Supprimer">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <div id="lieuNoResults" style="display:none">
      <div class="empty-st">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p>Aucun résultat</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var rows=document.querySelectorAll('.lieu-row'),
      tabs=document.querySelectorAll('.ftab'),
      inp=document.getElementById('lieuSearch'),
      noRes=document.getElementById('lieuNoResults'),
      cnt=document.getElementById('lieuCount');
  var activeFilter='all';

  function update(){
    var q=(inp?inp.value.toLowerCase().trim():'');
    var vis=0;
    rows.forEach(function(r){
      var typeOk=activeFilter==='all'||r.dataset.type===activeFilter;
      var searchOk=!q||r.dataset.search.includes(q);
      var show=typeOk&&searchOk;
      r.style.display=show?'':'none';
      if(show)vis++;
    });
    if(noRes) noRes.style.display=vis===0?'block':'none';
    if(cnt) cnt.textContent=q?vis+' résultat'+(vis>1?'s':''):'';
  }

  tabs.forEach(function(tab){
    tab.addEventListener('click',function(){
      tabs.forEach(function(t){t.classList.remove('active');});
      tab.classList.add('active');
      activeFilter=tab.dataset.filter;
      update();
    });
  });

  if(inp) inp.addEventListener('input',update);
})();
</script>
