<?php
$_isAuth = !empty($_SESSION['user']);
if ($_isAuth) {
    $_cp   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $_base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    if ($_base && str_starts_with($_cp, $_base)) $_cp = substr($_cp, strlen($_base));
    if ($_cp === '' || $_cp === false) $_cp = '/';
    $_active = fn(string $p): string => $p === '/'
        ? ($_cp === '/' ? 'active' : '')
        : (str_starts_with($_cp, $p) ? 'active' : '');
    $_u        = $_SESSION['user'] ?? [];
    $_initials = strtoupper(substr($_u['prenom'] ?? 'G', 0, 1) . substr($_u['nom'] ?? '', 0, 1));
    $_fullname = trim(($_u['prenom'] ?? '') . ' ' . ($_u['nom'] ?? ''));
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($GLOBALS['config']['app_name'] ?? 'Back-office') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root {
  --sb-w: 260px;
  --tb-h: 60px;
  --sb-bg: #0C1220;
  --sb-border: rgba(255,255,255,0.07);
  --sb-txt: #8B95A8;
  --sb-txt-h: #E2E8F0;
  --sb-act-bg: rgba(99,102,241,0.14);
  --sb-act-txt: #A5B4FC;
  --sb-act-bar: #6366F1;
  --primary: #6366F1;
  --primary-d: #4F46E5;
  --primary-l: #EEF2FF;
  --primary-xl: #F5F3FF;
  --gold: #F59E0B;
  --gold-l: #FFFBEB;
  --success: #10B981;
  --success-l: #ECFDF5;
  --danger: #EF4444;
  --danger-l: #FEF2F2;
  --warning: #F59E0B;
  --warning-l: #FFFBEB;
  --bg: #F1F5F9;
  --card: #FFFFFF;
  --border: #E2E8F0;
  --border-h: #CBD5E1;
  --txt1: #0F172A;
  --txt2: #475569;
  --txt3: #94A3B8;
  --sh-xs: 0 1px 2px rgba(0,0,0,.04);
  --sh-sm: 0 1px 4px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
  --sh-md: 0 4px 12px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
  --sh-lg: 0 16px 40px rgba(0,0,0,.1), 0 6px 12px rgba(0,0,0,.06);
  --r-xs: 4px; --r-sm: 7px; --r: 11px; --r-lg: 15px; --r-xl: 20px;
  --t: 180ms cubic-bezier(.4,0,.2,1);
  --font: 'Inter', system-ui, -apple-system, sans-serif;
}
*,*::before,*::after { box-sizing: border-box; }
html, body { height: 100%; }
body { font-family: var(--font); background: var(--bg); color: var(--txt1); -webkit-font-smoothing: antialiased; margin: 0; }

/* ─── SHELL ─── */
.bo-app { display: flex; min-height: 100vh; }

/* ─── SIDEBAR ─── */
.bo-sb {
  width: var(--sb-w); flex-shrink: 0;
  background: var(--sb-bg);
  position: fixed; inset: 0 auto 0 0;
  display: flex; flex-direction: column;
  z-index: 200; overflow: hidden;
  transition: transform var(--t);
}
.sb-logo {
  display: flex; align-items: center; gap: 11px;
  padding: 18px 18px 16px;
  border-bottom: 1px solid var(--sb-border);
  text-decoration: none; flex-shrink: 0;
}
.sb-logo-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(245,158,11,.35);
}
.sb-logo-icon svg { color: #fff; }
.sb-logo-title { color: #F1F5F9; font-size: 14.5px; font-weight: 700; line-height: 1.2; }
.sb-logo-sub   { color: var(--sb-txt); font-size: 11px; }

.sb-nav { flex: 1; padding: 10px 10px; overflow-y: auto; }
.sb-nav::-webkit-scrollbar { width: 3px; }
.sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 3px; }
.sb-section { color: var(--sb-txt); font-size: 10px; font-weight: 600; letter-spacing: .07em;
  text-transform: uppercase; padding: 12px 10px 4px; }

.sb-link {
  display: flex; align-items: center; gap: 9px;
  padding: 8.5px 12px; border-radius: var(--r-sm);
  color: var(--sb-txt); text-decoration: none;
  font-size: 13.5px; font-weight: 500;
  transition: background var(--t), color var(--t);
  position: relative; margin-bottom: 1px;
}
.sb-link:hover { background: rgba(255,255,255,.05); color: var(--sb-txt-h); }
.sb-link.active { background: var(--sb-act-bg); color: var(--sb-act-txt); }
.sb-link.active::before {
  content:''; position:absolute; left:0; top:7px; bottom:7px;
  width:3px; background:var(--sb-act-bar); border-radius:0 3px 3px 0;
}
.sb-link svg { width:16px; height:16px; flex-shrink:0; opacity:.65; transition:opacity var(--t); }
.sb-link:hover svg, .sb-link.active svg { opacity:1; }

.sb-foot {
  border-top: 1px solid var(--sb-border); padding: 14px 12px; flex-shrink: 0;
}
.sb-user { display:flex; align-items:center; gap:9px; margin-bottom:10px; }
.sb-avatar {
  width:32px; height:32px; border-radius:50%; flex-shrink:0;
  background: linear-gradient(135deg, var(--primary), var(--primary-d));
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:12px; font-weight:700;
}
.sb-uname { color:#E2E8F0; font-size:13px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sb-urole { color:var(--sb-txt); font-size:11px; }
.sb-logout {
  display:flex; align-items:center; gap:8px;
  padding:7px 10px; border-radius:var(--r-sm);
  color:#F87171; text-decoration:none; font-size:13px; font-weight:500;
  transition:background var(--t), color var(--t);
}
.sb-logout:hover { background:rgba(239,68,68,.1); color:#FCA5A5; }
.sb-logout svg { width:14px; height:14px; }

/* ─── MAIN ─── */
.bo-main { flex:1; margin-left:var(--sb-w); display:flex; flex-direction:column; min-height:100vh; }

/* ─── TOPBAR ─── */
.bo-top {
  height:var(--tb-h); background:var(--card);
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; padding:0 24px; gap:12px;
  position:sticky; top:0; z-index:100;
}
.top-toggle {
  display:none; border:none; background:none; cursor:pointer;
  padding:6px; border-radius:var(--r-sm); color:var(--txt2);
  transition:background var(--t); line-height:0;
}
.top-toggle:hover { background:var(--bg); }
.top-toggle svg { width:20px; height:20px; }
.top-spacer { flex:1; }
.top-pill {
  display:flex; align-items:center; gap:5px;
  background:#F0FDF4; color:#166534; border:1px solid #BBF7D0;
  padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600;
}
.top-pill::before { content:''; width:6px; height:6px; border-radius:50%; background:#22C55E; }

/* ─── CONTENT ─── */
.bo-content { flex:1; padding:26px 24px; }

/* ─── FLASH ─── */
.bo-flash {
  display:flex; align-items:center; gap:10px;
  padding:11px 16px; border-radius:var(--r); font-size:13.5px; font-weight:500;
  margin-bottom:22px; border:1px solid transparent;
  animation:flashIn .2s ease;
}
@keyframes flashIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.bo-flash svg { width:15px; height:15px; flex-shrink:0; }
.bo-flash-close {
  margin-left:auto; cursor:pointer; background:none; border:none; padding:0;
  opacity:.55; line-height:0; transition:opacity var(--t);
}
.bo-flash-close:hover { opacity:1; }
.bo-flash-close svg { width:13px; height:13px; }
.fl-success { background:var(--success-l); color:#065F46; border-color:#A7F3D0; }
.fl-danger  { background:var(--danger-l);  color:#991B1B; border-color:#FECACA; }
.fl-warning { background:var(--warning-l); color:#78350F; border-color:#FDE68A; }

/* ─── CARDS ─── */
.bo-card { background:var(--card); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--sh-sm); }
.bo-card-head {
  padding:16px 20px 14px;
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.bo-card-title { font-size:14.5px; font-weight:650; color:var(--txt1); }
.bo-card-body  { padding:20px; }

/* ─── STAT CARDS ─── */
.stat-card {
  background:var(--card); border:1px solid var(--border); border-radius:var(--r-lg);
  padding:20px; display:flex; align-items:flex-start; gap:14px;
  box-shadow:var(--sh-sm); transition:box-shadow var(--t), transform var(--t);
  text-decoration:none; color:inherit;
}
.stat-card:hover { box-shadow:var(--sh-md); transform:translateY(-2px); color:inherit; }
.stat-ico {
  width:44px; height:44px; border-radius:var(--r); flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.stat-ico svg { width:21px; height:21px; }
.ico-indigo { background:var(--primary-l); color:var(--primary); }
.ico-gold   { background:var(--gold-l);    color:var(--gold); }
.ico-green  { background:var(--success-l); color:var(--success); }
.ico-blue   { background:#DBEAFE;          color:#2563EB; }
.stat-val   { font-size:30px; font-weight:700; color:var(--txt1); line-height:1.1; }
.stat-lbl   { font-size:12.5px; color:var(--txt2); font-weight:500; margin-top:3px; }

/* ─── TABLE ─── */
.bo-table-wrap { overflow-x:auto; }
.bo-table { width:100%; border-collapse:separate; border-spacing:0; }
.bo-table thead th {
  background:#F8FAFC; padding:9px 16px;
  font-size:10.5px; font-weight:700; color:var(--txt3);
  text-transform:uppercase; letter-spacing:.06em;
  border-bottom:1px solid var(--border); white-space:nowrap;
}
.bo-table thead th:first-child { border-radius:0; }
.bo-table tbody tr { transition:background var(--t); cursor:default; }
.bo-table tbody tr:hover { background:#F8FAFC; }
.bo-table tbody td {
  padding:12px 16px; font-size:13.5px; color:var(--txt1);
  border-bottom:1px solid var(--border); vertical-align:middle;
}
.bo-table tbody tr:last-child td { border-bottom:none; }
.bo-table .td-muted { color:var(--txt3); font-size:12.5px; }
.bo-table .td-actions { display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; }

/* ─── BUTTONS ─── */
.btn-bo {
  display:inline-flex; align-items:center; gap:6px;
  padding:7.5px 15px; border-radius:var(--r-sm);
  font-size:13.5px; font-weight:500; font-family:var(--font);
  cursor:pointer; transition:all var(--t);
  border:1.5px solid transparent; text-decoration:none; line-height:1.4; white-space:nowrap;
}
.btn-bo svg { width:14px; height:14px; flex-shrink:0; }
.btn-bo:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
.btn-pri { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn-pri:hover { background:var(--primary-d); border-color:var(--primary-d); color:#fff; }
.btn-sec { background:var(--bg); color:var(--txt2); border-color:var(--border); }
.btn-sec:hover { background:var(--border); color:var(--txt1); border-color:var(--border-h); }
.btn-danger { background:transparent; color:var(--danger); border-color:#FECACA; }
.btn-danger:hover { background:var(--danger-l); color:var(--danger); }
.btn-ghost { background:transparent; color:var(--txt2); border-color:var(--border); }
.btn-ghost:hover { background:var(--bg); color:var(--txt1); }
.btn-sm  { padding:5px 11px; font-size:12.5px; }
.btn-sm svg { width:12px; height:12px; }

/* ─── FORM ELEMENTS ─── */
.f-group { margin-bottom:17px; }
.f-label {
  display:block; font-size:12.5px; font-weight:600;
  color:var(--txt2); margin-bottom:5px; letter-spacing:.01em;
}
.f-control {
  display:block; width:100%;
  padding:9px 12px; font-size:13.5px; font-family:var(--font);
  color:var(--txt1); background:var(--card);
  border:1.5px solid var(--border); border-radius:var(--r-sm);
  transition:border-color var(--t), box-shadow var(--t); outline:none;
  appearance:none; -webkit-appearance:none;
}
.f-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.f-control::placeholder { color:var(--txt3); }
textarea.f-control { resize:vertical; min-height:86px; line-height:1.5; }
select.f-control { cursor:pointer; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:32px; }

/* ─── BADGES ─── */
.bo-badge {
  display:inline-flex; align-items:center;
  padding:2.5px 8px; border-radius:12px;
  font-size:11px; font-weight:600; white-space:nowrap;
}
.b-spectacle { background:#EDE9FE; color:#5B21B6; }
.b-zone      { background:#D1FAE5; color:#065F46; }
.b-restaurant{ background:#FEF3C7; color:#78350F; }
.b-hotel     { background:#DBEAFE; color:#1E40AF; }
.b-accueil   { background:#FCE7F3; color:#831843; }
.b-allee     { background:#F1F5F9; color:#475569; }

/* ─── PAGE HEADER ─── */
.page-head { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:22px; flex-wrap:wrap; }
.page-head h1 { font-size:21px; font-weight:700; color:var(--txt1); margin:0; }
.page-head p  { font-size:13px; color:var(--txt3); margin:3px 0 0; }

/* ─── DATE GROUP (séances) ─── */
.dg { background:var(--card); border:1px solid var(--border); border-radius:var(--r); margin-bottom:12px; overflow:hidden; box-shadow:var(--sh-xs); }
.dg-head {
  padding:10px 16px; background:var(--sb-bg); color:#E2E8F0;
  display:flex; align-items:center; justify-content:space-between;
  font-size:13px; font-weight:600;
}
.dg-badge {
  background:rgba(255,255,255,.1); color:#CBD5E1;
  padding:2px 8px; border-radius:12px; font-size:11px;
}

/* ─── EMPTY STATE ─── */
.empty-st { text-align:center; padding:48px 24px; color:var(--txt3); }
.empty-st svg { width:38px; height:38px; margin-bottom:12px; opacity:.35; }
.empty-st p { font-size:13.5px; margin:0; }

/* ─── DIVIDER ─── */
.bo-div { border:none; border-top:1px solid var(--border); margin:20px 0; }

/* ─── SIDEBAR OVERLAY ─── */
.sb-overlay {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.55); z-index:199;
  backdrop-filter:blur(2px);
}

/* ─── QUICK LINKS ─── */
.quick-link {
  display:flex; align-items:center; gap:10px;
  padding:13px 16px; border-radius:var(--r);
  border:1.5px solid var(--border); background:var(--card);
  text-decoration:none; color:var(--txt1); font-size:13.5px; font-weight:500;
  transition:all var(--t); box-shadow:var(--sh-xs);
}
.quick-link:hover { border-color:var(--primary); background:var(--primary-xl); color:var(--primary); box-shadow:var(--sh-md); transform:translateY(-1px); }
.quick-link svg { width:18px; height:18px; flex-shrink:0; }
.ql-ico { width:36px; height:36px; border-radius:var(--r-sm); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.quick-link:hover .ql-ico { background:var(--primary-l); }

/* ─── LOGIN ─── */
.login-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; background:linear-gradient(135deg,#080E1A 0%,#0F172A 50%,#0A0F1E 100%); position:relative; overflow:hidden; }
.login-wrap::before { content:''; position:absolute; inset:0; background:radial-gradient(ellipse at 60% 40%,rgba(99,102,241,.12) 0%,transparent 70%); }
.login-card { width:100%; max-width:420px; position:relative; z-index:1; }
.login-logo-wrap { text-align:center; margin-bottom:28px; }
.login-logo-icon { width:54px; height:54px; background:linear-gradient(135deg,#F59E0B,#D97706); border-radius:14px; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; box-shadow:0 8px 24px rgba(245,158,11,.35); }
.login-logo-icon svg { color:#fff; }
.login-logo-title { color:#F1F5F9; font-size:22px; font-weight:700; }
.login-logo-sub   { color:#64748B; font-size:13px; margin-top:3px; }
.login-box { background:#fff; border-radius:var(--r-xl); padding:30px 32px; box-shadow:0 32px 64px rgba(0,0,0,.5), 0 1px 0 rgba(255,255,255,.05) inset; }
.login-title { font-size:17px; font-weight:700; color:var(--txt1); margin-bottom:6px; }
.login-sub   { font-size:13px; color:var(--txt3); margin-bottom:24px; }
.login-err   { display:flex; align-items:center; gap:8px; background:var(--danger-l); color:#991B1B; border:1px solid #FECACA; padding:10px 14px; border-radius:var(--r-sm); font-size:13px; font-weight:500; margin-bottom:18px; }
.login-err svg { width:14px; height:14px; flex-shrink:0; }
.login-btn { width:100%; padding:10px; font-size:14.5px; font-weight:600; background:var(--primary); color:#fff; border:none; border-radius:var(--r-sm); cursor:pointer; transition:background var(--t), transform var(--t); font-family:var(--font); }
.login-btn:hover { background:var(--primary-d); transform:translateY(-1px); }
.login-btn:active { transform:translateY(0); }

/* ─── RESPONSIVE ─── */
@media(max-width:1024px) {
  .bo-sb { transform:translateX(-100%); }
  .bo-sb.open { transform:translateX(0); }
  .sb-overlay.open { display:block; }
  .top-toggle { display:inline-flex !important; }
  .bo-main { margin-left:0; }
}
@media(max-width:640px) {
  .bo-content { padding:16px 14px; }
  .bo-top { padding:0 14px; }
  .page-head { gap:10px; }
  .login-box { padding:24px 20px; }
}
</style>
</head>
<body>
<?php if (!$_isAuth): ?>
<?= $content ?>
<?php else: ?>
<div class="bo-app">
  <div class="sb-overlay" id="sbOverlay"></div>

  <!-- ─── SIDEBAR ─── -->
  <nav class="bo-sb" id="boSb">
    <a href="<?= $url('/') ?>" class="sb-logo">
      <div class="sb-logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div>
        <div class="sb-logo-title">Puy du Fou</div>
        <div class="sb-logo-sub">Back-office</div>
      </div>
    </a>

    <div class="sb-nav">
      <div class="sb-section">Navigation</div>
      <a href="<?= $url('/') ?>" class="sb-link <?= $_active('/') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>

      <div class="sb-section" style="margin-top:8px">Contenu</div>
      <a href="<?= $url('/spectacles') ?>" class="sb-link <?= $_active('/spectacles') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
        Spectacles
      </a>
      <a href="<?= $url('/lieux') ?>" class="sb-link <?= $_active('/lieux') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Lieux
      </a>
      <a href="<?= $url('/seances') ?>" class="sb-link <?= $_active('/seances') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Séances
      </a>

      <div class="sb-section" style="margin-top:8px">Paramètres</div>
      <a href="<?= $url('/distances') ?>" class="sb-link <?= $_active('/distances') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/><path d="M5 10V5l3 3-3 3"/><path d="M19 14v5l-3-3 3-3"/></svg>
        Distances
      </a>
      <a href="<?= $url('/jours') ?>" class="sb-link <?= $_active('/jours') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Jours d'ouverture
      </a>
    </div>

    <div class="sb-foot">
      <div class="sb-user">
        <div class="sb-avatar"><?= htmlspecialchars($_initials) ?></div>
        <div>
          <div class="sb-uname"><?= htmlspecialchars($_fullname) ?></div>
          <div class="sb-urole">Gestionnaire</div>
        </div>
      </div>
      <a href="<?= $url('/logout') ?>" class="sb-logout">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </div>
  </nav>

  <!-- ─── MAIN ─── -->
  <div class="bo-main">
    <header class="bo-top">
      <button class="top-toggle" id="sbToggle" aria-label="Ouvrir le menu" style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="top-spacer"></div>
      <div class="top-pill">Système en ligne</div>
    </header>

    <div class="bo-content">
      <?php if (!empty($flash)):
        $__ft = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_type']);
        $__icons = [
          'success' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
          'danger'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
          'warning' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        ];
      ?>
      <div class="bo-flash fl-<?= htmlspecialchars($__ft) ?>" id="boFlash">
        <?= $__icons[$__ft] ?? $__icons['success'] ?>
        <?= htmlspecialchars($flash) ?>
        <button class="bo-flash-close" onclick="document.getElementById('boFlash').remove()" aria-label="Fermer">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <?php endif; ?>

      <?= $content ?>
    </div>
  </div>
</div>

<script>
(function(){
  var sb=document.getElementById('boSb'),
      ov=document.getElementById('sbOverlay'),
      tg=document.getElementById('sbToggle');
  if(tg){
    tg.addEventListener('click',function(){sb.classList.toggle('open');ov.classList.toggle('open');});
    ov.addEventListener('click',function(){sb.classList.remove('open');ov.classList.remove('open');});
  }
  var fl=document.getElementById('boFlash');
  if(fl){setTimeout(function(){fl.style.transition='opacity .3s';fl.style.opacity='0';setTimeout(function(){fl&&fl.remove();},300);},4500);}
})();
</script>
<?php endif; ?>
</body>
</html>
