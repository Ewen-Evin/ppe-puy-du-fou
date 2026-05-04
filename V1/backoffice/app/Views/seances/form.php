<div class="page-head">
  <div>
    <h1>Nouvelle séance</h1>
    <p>Planifier une représentation</p>
  </div>
  <a href="<?= $url('/seances') ?>" class="btn-bo btn-sec">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Retour
  </a>
</div>

<div class="bo-card" style="max-width:560px">
  <div class="bo-card-head">
    <span class="bo-card-title">Créer une séance</span>
  </div>
  <div class="bo-card-body">
    <form method="post" action="<?= $url('/seances') ?>">
      <input type="hidden" name="_csrf" value="<?= $csrf ?>">

      <div class="f-group">
        <label class="f-label" for="id_spectacle">Spectacle <span style="color:var(--danger)">*</span></label>
        <select class="f-control" id="id_spectacle" name="id_spectacle" required>
          <option value="">Sélectionner un spectacle…</option>
          <?php foreach ($spectacles as $sp): if (!is_array($sp) || !isset($sp['id_spectacle'])) continue; ?>
            <option value="<?= (int)$sp['id_spectacle'] ?>"><?= htmlspecialchars($sp['libelle']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="f-group">
        <label class="f-label" for="date_seance">Date <span style="color:var(--danger)">*</span></label>
        <?php if (empty($jours)): ?>
          <div style="font-size:13px;color:var(--warning);background:var(--warning-l);padding:9px 12px;border-radius:var(--r-sm);border:1px solid #FDE68A">
            Aucun jour d'ouverture défini — <a href="<?= $url('/jours') ?>" style="color:var(--warning);font-weight:600">créer d'abord un jour</a>.
          </div>
        <?php else: ?>
        <select class="f-control" id="date_seance" name="date_seance" required>
          <option value="">Sélectionner une date…</option>
          <?php foreach ($jours as $j): if (!is_array($j) || !isset($j['id_jours'])) continue; ?>
            <option value="<?= htmlspecialchars($j['id_jours']) ?>">
              <?= htmlspecialchars($j['id_jours']) ?> (<?= htmlspecialchars(substr($j['heure_ouverture'],0,5)) ?>–<?= htmlspecialchars(substr($j['heure_fermeture'],0,5)) ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>
      </div>

      <div class="row g-3">
        <div class="col-6">
          <div class="f-group">
            <label class="f-label" for="heure_debut">Heure de début <span style="color:var(--danger)">*</span></label>
            <input class="f-control" type="time" id="heure_debut" name="heure_debut" step="60" required>
          </div>
        </div>
        <div class="col-6">
          <div class="f-group">
            <label class="f-label" for="heure_fin">Heure de fin <span style="color:var(--danger)">*</span></label>
            <input class="f-control" type="time" id="heure_fin" name="heure_fin" step="60" required>
          </div>
        </div>
      </div>

      <hr class="bo-div">
      <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
        <a href="<?= $url('/seances') ?>" class="btn-bo btn-sec">Annuler</a>
        <button type="submit" class="btn-bo btn-pri" <?= empty($jours) ? 'disabled' : '' ?>>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          Créer la séance
        </button>
      </div>
    </form>
  </div>
</div>
