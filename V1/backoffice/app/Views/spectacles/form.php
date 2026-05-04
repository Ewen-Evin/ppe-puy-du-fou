<?php $isEdit = !empty($item['id_spectacle']); ?>
<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Modifier le spectacle' : 'Nouveau spectacle' ?></h1>
    <p><?= $isEdit ? htmlspecialchars($item['libelle'] ?? '') : 'Créer un nouveau spectacle dans le parc' ?></p>
  </div>
  <a href="<?= $url('/spectacles') ?>" class="btn-bo btn-sec">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Retour
  </a>
</div>

<div class="bo-card" style="max-width:680px">
  <div class="bo-card-head">
    <span class="bo-card-title"><?= $isEdit ? 'Modifier' : 'Créer' ?> un spectacle</span>
  </div>
  <div class="bo-card-body">
    <form method="post" action="<?= $isEdit ? $url('/spectacles/' . $item['id_spectacle'] . '/edit') : $url('/spectacles') ?>">
      <input type="hidden" name="_csrf" value="<?= $csrf ?>">

      <div class="f-group">
        <label class="f-label" for="libelle">Libellé <span style="color:var(--danger)">*</span></label>
        <input class="f-control" type="text" id="libelle" name="libelle" required
               value="<?= htmlspecialchars($item['libelle'] ?? '') ?>"
               placeholder="ex. Le Signe du Triomphe">
      </div>

      <div class="f-group">
        <label class="f-label" for="description">Description</label>
        <textarea class="f-control" id="description" name="description"
                  placeholder="Description du spectacle…"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
      </div>

      <div class="row g-3">
        <div class="col-sm-6">
          <div class="f-group">
            <label class="f-label" for="duree_spectacle">Durée spectacle <span style="color:var(--txt3);font-weight:400">(HH:MM:SS)</span></label>
            <input class="f-control" type="text" id="duree_spectacle" name="duree_spectacle"
                   value="<?= htmlspecialchars($item['duree_spectacle'] ?? '00:30:00') ?>"
                   placeholder="00:30:00" pattern="\d{2}:\d{2}:\d{2}">
          </div>
        </div>
        <div class="col-sm-6">
          <div class="f-group">
            <label class="f-label" for="duree_attente">Durée d'attente <span style="color:var(--txt3);font-weight:400">(HH:MM:SS)</span></label>
            <input class="f-control" type="text" id="duree_attente" name="duree_attente"
                   value="<?= htmlspecialchars($item['duree_attente'] ?? '00:00:00') ?>"
                   placeholder="00:20:00" pattern="\d{2}:\d{2}:\d{2}">
          </div>
        </div>
      </div>

      <div class="f-group">
        <label class="f-label" for="id_lieu">Lieu <span style="color:var(--danger)">*</span></label>
        <select class="f-control" id="id_lieu" name="id_lieu" required>
          <option value="">Sélectionner un lieu…</option>
          <?php foreach ($lieux as $l): if (!is_array($l) || !isset($l['id_lieu'])) continue; ?>
            <option value="<?= (int)$l['id_lieu'] ?>"
              <?= (int)($item['id_lieu'] ?? 0) === (int)$l['id_lieu'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($l['nom']) ?>
              <?php if (!empty($l['type_lieu'])): ?>(<?= htmlspecialchars($l['type_lieu']) ?>)<?php endif; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <hr class="bo-div">
      <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
        <a href="<?= $url('/spectacles') ?>" class="btn-bo btn-sec">Annuler</a>
        <button type="submit" class="btn-bo btn-pri">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          <?= $isEdit ? 'Enregistrer' : 'Créer le spectacle' ?>
        </button>
      </div>
    </form>
  </div>
</div>
