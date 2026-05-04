<?php
$_hour = (int)date('H');
$_greeting = $_hour < 12 ? 'Bonjour' : ($_hour < 18 ? 'Bon après-midi' : 'Bonsoir');
$_firstName = trim(($_SESSION['user']['prenom'] ?? ''));
?>

<!-- WELCOME BANNER -->
<div style="background:linear-gradient(135deg,#4F46E5 0%,#6366F1 50%,#818CF8 100%);border-radius:var(--r-xl);padding:24px 28px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(99,102,241,.3)">
  <div style="position:absolute;right:-20px;top:-30px;width:200px;height:200px;background:rgba(255,255,255,.07);border-radius:50%"></div>
  <div style="position:absolute;right:60px;bottom:-40px;width:140px;height:140px;background:rgba(255,255,255,.05);border-radius:50%"></div>
  <div style="position:relative;z-index:1">
    <p style="color:rgba(255,255,255,.75);font-size:13px;font-weight:500;margin-bottom:4px"><?= $_greeting ?><?= $_firstName ? ', ' . htmlspecialchars($_firstName) : '' ?> —</p>
    <h2 style="color:#fff;font-size:20px;font-weight:750;margin:0 0 8px;letter-spacing:-.3px">Vue d'ensemble du parc</h2>
    <p style="color:rgba(255,255,255,.7);font-size:13px;margin:0">
      <?= date('l j F Y') ?> &nbsp;·&nbsp; <?= (int)($nbSeances ?? 0) ?> séance<?= (int)($nbSeances ?? 0) > 1 ? 's' : '' ?> planifiée<?= (int)($nbSeances ?? 0) > 1 ? 's' : '' ?>
    </p>
  </div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/spectacles') ?>" class="stat-card">
      <div class="stat-ico ico-indigo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-val"><?= (int)$nbSpectacles ?></div>
        <div class="stat-lbl">Spectacles</div>
        <span class="stat-trend trend-neu">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          Actifs
        </span>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/lieux') ?>" class="stat-card">
      <div class="stat-ico ico-gold">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-val"><?= (int)$nbLieux ?></div>
        <div class="stat-lbl">Lieux</div>
        <span class="stat-trend trend-neu">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/></svg>
          Cartographiés
        </span>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/seances') ?>" class="stat-card">
      <div class="stat-ico ico-blue">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-val"><?= (int)($nbSeances ?? 0) ?></div>
        <div class="stat-lbl">Séances</div>
        <span class="stat-trend trend-up">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          Planifiées
        </span>
      </div>
    </a>
  </div>
  <div class="col-sm-6 col-xl-3">
    <a href="<?= $url('/jours') ?>" class="stat-card">
      <div class="stat-ico ico-green">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="stat-body">
        <div class="stat-val"><?= (int)$nbJours ?></div>
        <div class="stat-lbl">Jours d'ouverture</div>
        <span class="stat-trend trend-up">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
          Programmés
        </span>
      </div>
    </a>
  </div>
</div>

<div class="row g-3">
  <!-- QUICK ACTIONS -->
  <div class="col-lg-7">
    <div class="bo-card h-100">
      <div class="bo-card-head">
        <span class="bo-card-title">Actions rapides</span>
        <span style="font-size:12px;color:var(--txt3)">Raccourcis fréquents</span>
      </div>
      <div class="bo-card-body">
        <div class="row g-2">
          <div class="col-sm-6">
            <a href="<?= $url('/spectacles/new') ?>" class="quick-link">
              <div class="ql-ico" style="background:#EEF2FF">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Nouveau spectacle</div>
                <div style="font-size:11.5px;color:var(--txt3)">Ajouter au catalogue</div>
              </div>
            </a>
          </div>
          <div class="col-sm-6">
            <a href="<?= $url('/lieux/new') ?>" class="quick-link">
              <div class="ql-ico" style="background:#FFFBEB">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Nouveau lieu</div>
                <div style="font-size:11.5px;color:var(--txt3)">Cartographier un point</div>
              </div>
            </a>
          </div>
          <div class="col-sm-6">
            <a href="<?= $url('/seances') ?>" class="quick-link">
              <div class="ql-ico" style="background:#DBEAFE">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="12" y1="14" x2="12" y2="18"/><line x1="10" y1="16" x2="14" y2="16"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Planifier une séance</div>
                <div style="font-size:11.5px;color:var(--txt3)">Programmer une représentation</div>
              </div>
            </a>
          </div>
          <div class="col-sm-6">
            <a href="<?= $url('/jours') ?>" class="quick-link">
              <div class="ql-ico" style="background:#ECFDF5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Jours d'ouverture</div>
                <div style="font-size:11.5px;color:var(--txt3)">Gérer le calendrier</div>
              </div>
            </a>
          </div>
          <div class="col-sm-6">
            <a href="<?= $url('/distances') ?>" class="quick-link">
              <div class="ql-ico" style="background:#F0FDF4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Distances</div>
                <div style="font-size:11.5px;color:var(--txt3)">Graphe de parcours</div>
              </div>
            </a>
          </div>
          <div class="col-sm-6">
            <a href="<?= $url('/spectacles') ?>" class="quick-link">
              <div class="ql-ico" style="background:#F5F3FF">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="#7C3AED" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              </div>
              <div>
                <div style="font-size:13.5px;font-weight:600">Tous les spectacles</div>
                <div style="font-size:11.5px;color:var(--txt3)">Voir le catalogue complet</div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- STATUS PANEL -->
  <div class="col-lg-5">
    <div class="bo-card h-100">
      <div class="bo-card-head">
        <span class="bo-card-title">État du système</span>
      </div>
      <div class="bo-card-body" style="display:flex;flex-direction:column;gap:12px">

        <!-- Inventory bars -->
        <?php
        $total = max(1, (int)$nbLieux);
        $pct = min(100, round((int)$nbSpectacles / max(1, $total) * 100));
        ?>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:12.5px;font-weight:600;color:var(--txt2)">Spectacles / Lieux</span>
            <span style="font-size:12px;color:var(--txt3)"><?= (int)$nbSpectacles ?> / <?= (int)$nbLieux ?></span>
          </div>
          <div style="height:6px;background:var(--border);border-radius:10px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--primary),#818CF8);border-radius:10px;transition:width .6s ease"></div>
          </div>
        </div>

        <?php $pctSeances = min(100, (int)($nbSeances ?? 0) > 0 ? 100 : 0); ?>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:12.5px;font-weight:600;color:var(--txt2)">Séances planifiées</span>
            <span style="font-size:12px;color:var(--txt3)"><?= (int)($nbSeances ?? 0) ?></span>
          </div>
          <div style="height:6px;background:var(--border);border-radius:10px;overflow:hidden">
            <div style="height:100%;width:<?= (int)($nbSeances ?? 0) > 0 ? '75' : '0' ?>%;background:linear-gradient(90deg,#2563EB,#60A5FA);border-radius:10px;transition:width .6s ease"></div>
          </div>
        </div>

        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:12.5px;font-weight:600;color:var(--txt2)">Jours d'ouverture</span>
            <span style="font-size:12px;color:var(--txt3)"><?= (int)$nbJours ?></span>
          </div>
          <div style="height:6px;background:var(--border);border-radius:10px;overflow:hidden">
            <div style="height:100%;width:<?= (int)$nbJours > 0 ? min(100, (int)$nbJours * 5) : 0 ?>%;background:linear-gradient(90deg,var(--success),#34D399);border-radius:10px;transition:width .6s ease"></div>
          </div>
        </div>

        <hr class="bo-div" style="margin:4px 0">

        <!-- Status items -->
        <div style="display:flex;flex-direction:column;gap:8px">
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:12.5px;color:var(--txt2);display:flex;align-items:center;gap:7px">
              <span style="width:8px;height:8px;background:var(--success);border-radius:50%;display:inline-block"></span>
              API connectée
            </span>
            <span style="font-size:11px;color:var(--success);font-weight:600;background:var(--success-l);padding:2px 8px;border-radius:20px">OK</span>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:12.5px;color:var(--txt2);display:flex;align-items:center;gap:7px">
              <span style="width:8px;height:8px;background:var(--success);border-radius:50%;display:inline-block"></span>
              Base de données
            </span>
            <span style="font-size:11px;color:var(--success);font-weight:600;background:var(--success-l);padding:2px 8px;border-radius:20px">OK</span>
          </div>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:12.5px;color:var(--txt2);display:flex;align-items:center;gap:7px">
              <span style="width:8px;height:8px;background:var(--gold);border-radius:50%;display:inline-block"></span>
              Environnement
            </span>
            <span style="font-size:11px;color:var(--txt2);font-weight:600;background:var(--border);padding:2px 8px;border-radius:20px">Dev</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
