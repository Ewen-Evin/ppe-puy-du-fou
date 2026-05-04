<div class="page-head">
  <div>
    <h1>Jours d'ouverture</h1>
    <p>Dates d'exploitation du parc et horaires d'accès</p>
  </div>
</div>

<div class="row g-4">
  <!-- FORMULAIRE -->
  <div class="col-lg-4">
    <div class="bo-card" style="position:sticky;top:calc(var(--tb-h) + 16px)">
      <div class="bo-card-head">
        <span class="bo-card-title">Ajouter / Modifier</span>
      </div>
      <div class="bo-card-body">
        <form method="post" action="<?= $url('/jours') ?>">
          <input type="hidden" name="_csrf" value="<?= $csrf ?>">
          <div class="f-group">
            <label class="f-label" for="j_date">Date <span style="color:var(--danger)">*</span></label>
            <input class="f-control" type="date" id="j_date" name="id_jours" required>
            <div style="font-size:11.5px;color:var(--txt3);margin-top:4px">Si la date existe déjà, les horaires seront mis à jour.</div>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <div class="f-group" style="margin-bottom:0">
                <label class="f-label" for="h_ouv">Ouverture <span style="color:var(--danger)">*</span></label>
                <input class="f-control" type="time" id="h_ouv" name="heure_ouverture" value="09:30" step="60" required>
              </div>
            </div>
            <div class="col-6">
              <div class="f-group" style="margin-bottom:0">
                <label class="f-label" for="h_ferm">Fermeture <span style="color:var(--danger)">*</span></label>
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
  </div>

  <!-- TABLEAU -->
  <div class="col-lg-8">
    <div class="bo-card">
      <div class="bo-card-head">
        <?php $rows = array_filter($items, fn($j) => is_array($j) && isset($j['id_jours'])); ?>
        <span class="bo-card-title"><?= count($rows) ?> jour<?= count($rows) > 1 ? 's' : '' ?> d'ouverture</span>
      </div>
      <div class="bo-table-wrap">
        <?php if (empty($rows)): ?>
        <div class="empty-st">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <p>Aucun jour d'ouverture défini</p>
        </div>
        <?php else: ?>
        <table class="bo-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Ouverture</th>
              <th>Fermeture</th>
              <th>Durée</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $j): ?>
            <?php
            $h1 = explode(':', $j['heure_ouverture']);
            $h2 = explode(':', $j['heure_fermeture']);
            $dur = ((int)$h2[0] * 60 + (int)$h2[1]) - ((int)$h1[0] * 60 + (int)$h1[1]);
            ?>
            <tr>
              <td>
                <span style="font-weight:600"><?= htmlspecialchars($j['id_jours']) ?></span>
              </td>
              <td>
                <span style="font-family:monospace;font-size:13px;color:var(--success);font-weight:600">
                  <?= htmlspecialchars(substr($j['heure_ouverture'],0,5)) ?>
                </span>
              </td>
              <td>
                <span style="font-family:monospace;font-size:13px;color:var(--danger);font-weight:600">
                  <?= htmlspecialchars(substr($j['heure_fermeture'],0,5)) ?>
                </span>
              </td>
              <td>
                <span style="font-size:12.5px;color:var(--txt3)"><?= intdiv($dur,60) ?>h<?= str_pad($dur%60,2,'0',STR_PAD_LEFT) ?></span>
              </td>
              <td>
                <div class="td-actions">
                  <form method="post" action="<?= $url('/jours/' . urlencode($j['id_jours']) . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce jour d\'ouverture ?')">
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
