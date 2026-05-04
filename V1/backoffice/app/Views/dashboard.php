<div class="page-head">
  <div>
    <h1>Tableau de bord</h1>
    <p>Vue d'ensemble du parc Puy du Fou</p>
  </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/spectacles') ?>" class="stat-card">
      <div class="stat-ico ico-indigo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= (int)$nbSpectacles ?></div>
        <div class="stat-lbl">Spectacles</div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/lieux') ?>" class="stat-card">
      <div class="stat-ico ico-gold">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= (int)$nbLieux ?></div>
        <div class="stat-lbl">Lieux</div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/seances') ?>" class="stat-card">
      <div class="stat-ico ico-blue">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= (int)($nbSeances ?? 0) ?></div>
        <div class="stat-lbl">Séances</div>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/jours') ?>" class="stat-card">
      <div class="stat-ico ico-green">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <div class="stat-val"><?= (int)$nbJours ?></div>
        <div class="stat-lbl">Jours d'ouverture</div>
      </div>
    </a>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div class="bo-card">
  <div class="bo-card-head">
    <span class="bo-card-title">Actions rapides</span>
  </div>
  <div class="bo-card-body">
    <div class="row g-3">
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/spectacles/new') ?>" class="quick-link">
          <div class="ql-ico" style="background:#EEF2FF">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          Nouveau spectacle
        </a>
      </div>
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/lieux/new') ?>" class="quick-link">
          <div class="ql-ico" style="background:#FFFBEB">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          Nouveau lieu
        </a>
      </div>
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/seances') ?>" class="quick-link">
          <div class="ql-ico" style="background:#DBEAFE">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          Planifier une séance
        </a>
      </div>
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/jours') ?>" class="quick-link">
          <div class="ql-ico" style="background:#ECFDF5">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </div>
          Gérer les jours
        </a>
      </div>
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/distances') ?>" class="quick-link">
          <div class="ql-ico" style="background:#F0FDF4">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/></svg>
          </div>
          Gérer les distances
        </a>
      </div>
      <div class="col-sm-6 col-md-4">
        <a href="<?= $url('/spectacles') ?>" class="quick-link">
          <div class="ql-ico" style="background:#F5F3FF">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </div>
          Voir tous les spectacles
        </a>
      </div>
    </div>
  </div>
</div>
