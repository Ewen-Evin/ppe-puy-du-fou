<?php $isEdit = !empty($item['id_spectacle']); ?>
<div class="page-head">
  <div class="page-head-left">
    <h1><?= $isEdit ? 'Modifier le spectacle' : 'Nouveau spectacle' ?></h1>
    <p><?= $isEdit ? htmlspecialchars($item['libelle'] ?? '') : 'Ajouter un spectacle au catalogue du parc' ?></p>
  </div>
  <a href="<?= $url('/spectacles') ?>" class="btn-bo btn-sec">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Retour
  </a>
</div>

<div style="max-width:700px">
  <div class="bo-card">
    <div class="bo-card-head">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:var(--r-sm);background:var(--primary-l);display:flex;align-items:center;justify-content:center">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
        </div>
        <span class="bo-card-title"><?= $isEdit ? 'Modifier' : 'Créer' ?> un spectacle</span>
      </div>
    </div>
    <div class="bo-card-body">
      <form method="post" action="<?= $isEdit ? $url('/spectacles/' . $item['id_spectacle'] . '/edit') : $url('/spectacles') ?>">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">

        <div class="f-group">
          <label class="f-label" for="libelle">
            Libellé <span style="color:var(--danger)">*</span>
          </label>
          <input class="f-control" type="text" id="libelle" name="libelle" required autofocus
                 value="<?= htmlspecialchars($item['libelle'] ?? '') ?>"
                 placeholder="ex. Le Signe du Triomphe">
        </div>

        <div class="f-group">
          <label class="f-label" for="description">Description</label>
          <textarea class="f-control" id="description" name="description"
                    placeholder="Description du spectacle, son histoire, ses personnages…"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
        </div>

        <div class="f-group">
          <label class="f-label" for="id_lieu">
            Lieu <span style="color:var(--danger)">*</span>
          </label>
          <select class="f-control" id="id_lieu" name="id_lieu" required>
            <option value="">Sélectionner un lieu…</option>
            <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
              <option value="<?= (int)$l['id_lieu'] ?>"
                <?= (int)($item['id_lieu'] ?? 0) === (int)$l['id_lieu'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['nom']) ?>
                <?php if (!empty($l['type_lieu'])): ?> — <?= ucfirst(htmlspecialchars($l['type_lieu'])) ?><?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="row g-3">
          <div class="col-sm-6">
            <div class="f-group">
              <label class="f-label" for="duree_spectacle">
                Durée du spectacle
                <span style="color:var(--txt3);font-weight:400"> HH:MM:SS</span>
              </label>
              <input class="f-control" type="text" id="duree_spectacle" name="duree_spectacle"
                     value="<?= htmlspecialchars($item['duree_spectacle'] ?? '00:30:00') ?>"
                     placeholder="00:30:00" pattern="\d{2}:\d{2}:\d{2}">
              <div class="f-hint">Durée de la représentation</div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="f-group">
              <label class="f-label" for="duree_attente">
                Durée d'attente
                <span style="color:var(--txt3);font-weight:400"> HH:MM:SS</span>
              </label>
              <input class="f-control" type="text" id="duree_attente" name="duree_attente"
                     value="<?= htmlspecialchars($item['duree_attente'] ?? '00:00:00') ?>"
                     placeholder="00:20:00" pattern="\d{2}:\d{2}:\d{2}">
              <div class="f-hint">Temps d'attente estimé</div>
            </div>
          </div>
        </div>

        <hr class="bo-div">
        <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;align-items:center">
          <a href="<?= $url('/spectacles') ?>" class="btn-bo btn-sec">Annuler</a>
          <button type="submit" class="btn-bo btn-pri">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le spectacle' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
