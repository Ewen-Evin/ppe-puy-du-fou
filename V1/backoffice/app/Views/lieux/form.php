<?php $isEdit = !empty($item['id_lieu']); ?>
<div class="page-head">
  <div class="page-head-left">
    <h1><?= $isEdit ? 'Modifier le lieu' : 'Nouveau lieu' ?></h1>
    <p><?= $isEdit ? htmlspecialchars($item['nom'] ?? '') : 'Ajouter un point d\'intérêt au parc' ?></p>
  </div>
  <a href="<?= $url('/lieux') ?>" class="btn-bo btn-sec">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Retour
  </a>
</div>

<div style="max-width:600px">
  <div class="bo-card">
    <div class="bo-card-head">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:34px;height:34px;border-radius:var(--r-sm);background:var(--gold-l);display:flex;align-items:center;justify-content:center">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="var(--gold)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <span class="bo-card-title"><?= $isEdit ? 'Modifier' : 'Créer' ?> un lieu</span>
      </div>
    </div>
    <div class="bo-card-body">
      <form method="post" action="<?= $isEdit ? $url('/lieux/' . $item['id_lieu'] . '/edit') : $url('/lieux') ?>">
        <input type="hidden" name="_csrf" value="<?= $csrf ?>">

        <div class="f-group">
          <label class="f-label" for="nom">Nom <span style="color:var(--danger)">*</span></label>
          <input class="f-control" type="text" id="nom" name="nom" required autofocus
                 value="<?= htmlspecialchars($item['nom'] ?? '') ?>"
                 placeholder="ex. Stadium Gallo-Romain">
        </div>

        <div class="f-group">
          <label class="f-label" for="type_lieu">Type de lieu <span style="color:var(--danger)">*</span></label>
          <select class="f-control" id="type_lieu" name="type_lieu" required>
            <?php foreach (['spectacle' => 'Spectacle', 'zone' => 'Zone', 'restaurant' => 'Restaurant', 'hotel' => 'Hôtel', 'accueil' => 'Accueil', 'allee' => 'Allée'] as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($item['type_lieu'] ?? 'zone') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="f-group">
          <label class="f-label" for="coordonnees_gps">
            Coordonnées GPS <span style="color:var(--danger)">*</span>
          </label>
          <input class="f-control" type="text" id="coordonnees_gps" name="coordonnees_gps" required
                 value="<?= htmlspecialchars($item['coordonnees_gps'] ?? '') ?>"
                 placeholder="46.8888,-0.9328">
          <div class="f-hint">Format : latitude,longitude — ex. 46.8888,-0.9328</div>
        </div>

        <hr class="bo-div">
        <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
          <a href="<?= $url('/lieux') ?>" class="btn-bo btn-sec">Annuler</a>
          <button type="submit" class="btn-bo btn-pri">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le lieu' ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
