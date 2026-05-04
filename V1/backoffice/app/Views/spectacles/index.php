<?php $rows = array_filter($items, fn($i) => is_array($i) && isset($i['id_spectacle'])); ?>
<div class="page-head">
  <div class="page-head-left">
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
    <span class="bo-card-title" id="spCount">
      <?= count($rows) ?> spectacle<?= count($rows) > 1 ? 's' : '' ?>
    </span>
  </div>

  <?php if (!empty($rows)): ?>
  <div class="table-search-wrap">
    <div class="table-search">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="spSearch" placeholder="Rechercher un spectacle…" autocomplete="off">
      <span class="search-count" id="spSearchCount"></span>
    </div>
  </div>
  <?php endif; ?>

  <div class="bo-table-wrap">
    <?php if (empty($rows)): ?>
    <div class="empty-st">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
      <p>Aucun spectacle enregistré</p>
      <a href="<?= $url('/spectacles/new') ?>" class="btn-bo btn-pri btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Créer le premier spectacle
      </a>
    </div>
    <?php else: ?>
    <table class="bo-table" id="spTable">
      <thead>
        <tr>
          <th style="width:48px">#</th>
          <th>Libellé</th>
          <th>Description</th>
          <th>Lieu</th>
          <th>Durée</th>
          <th>Attente</th>
          <th style="width:120px"></th>
        </tr>
      </thead>
      <tbody id="spBody">
      <?php foreach ($rows as $sp): ?>
        <tr class="sp-row" data-search="<?= htmlspecialchars(strtolower($sp['libelle'] . ' ' . ($sp['description'] ?? '') . ' ' . ($sp['lieu_nom'] ?? ''))) ?>">
          <td>
            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:var(--bg);border-radius:6px;font-size:11px;font-weight:700;color:var(--txt3)">
              <?= (int)$sp['id_spectacle'] ?>
            </span>
          </td>
          <td>
            <span style="font-weight:600;font-size:13.5px"><?= htmlspecialchars($sp['libelle']) ?></span>
          </td>
          <td class="td-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($sp['description'] ?? '') ?>">
            <?= htmlspecialchars(mb_strimwidth($sp['description'] ?? '—', 0, 55, '…')) ?>
          </td>
          <td>
            <?php if (!empty($sp['lieu_nom'])): ?>
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:13px">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" stroke="var(--txt3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($sp['lieu_nom']) ?>
            </span>
            <?php else: ?>
            <span class="td-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:4px;background:var(--primary-l);color:var(--primary);padding:2px 8px;border-radius:20px;font-size:11.5px;font-weight:600;font-variant-numeric:tabular-nums">
              <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= htmlspecialchars(substr($sp['duree_spectacle'], 0, 5)) ?>
            </span>
          </td>
          <td>
            <span style="font-family:monospace;font-size:12.5px;color:var(--txt3)"><?= htmlspecialchars(substr($sp['duree_attente'], 0, 5)) ?></span>
          </td>
          <td>
            <div class="td-actions">
              <a href="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/edit') ?>" class="btn-bo btn-ghost btn-sm" title="Modifier">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Éditer
              </a>
              <form method="post" action="<?= $url('/spectacles/' . $sp['id_spectacle'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer le spectacle « <?= htmlspecialchars($sp['libelle']) ?> » ?')">
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
    <!-- No results row -->
    <div id="spNoResults" style="display:none">
      <div class="empty-st">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <p>Aucun résultat pour cette recherche</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function(){
  var inp=document.getElementById('spSearch');
  if(!inp)return;
  var rows=document.querySelectorAll('.sp-row'),
      noRes=document.getElementById('spNoResults'),
      cnt=document.getElementById('spSearchCount'),
      total=rows.length;
  inp.addEventListener('input',function(){
    var q=this.value.toLowerCase().trim();
    var vis=0;
    rows.forEach(function(r){
      var match=!q||r.dataset.search.includes(q);
      r.style.display=match?'':'none';
      if(match)vis++;
    });
    noRes.style.display=vis===0?'block':'none';
    cnt.textContent=q?vis+'/'+total+' résultat'+(vis>1?'s':''):'';
  });
})();
</script>
