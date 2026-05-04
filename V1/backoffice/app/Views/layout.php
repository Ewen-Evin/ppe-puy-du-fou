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

    // Breadcrumbs
    $_bcs = [];
    if (preg_match('#^/spectacles/\d+/edit$#', $_cp)) {
        $_bcs = [['Spectacles', '/spectacles'], ['Modifier', null]];
    } elseif ($_cp === '/spectacles/new') {
        $_bcs = [['Spectacles', '/spectacles'], ['Nouveau spectacle', null]];
    } elseif ($_cp === '/spectacles') {
        $_bcs = [['Spectacles', null]];
    } elseif (preg_match('#^/lieux/\d+/edit$#', $_cp)) {
        $_bcs = [['Lieux', '/lieux'], ['Modifier', null]];
    } elseif ($_cp === '/lieux/new') {
        $_bcs = [['Lieux', '/lieux'], ['Nouveau lieu', null]];
    } elseif ($_cp === '/lieux') {
        $_bcs = [['Lieux', null]];
    } elseif ($_cp === '/seances') {
        $_bcs = [['Séances', null]];
    } elseif ($_cp === '/jours') {
        $_bcs = [["Jours d'ouverture", null]];
    } elseif ($_cp === '/distances') {
        $_bcs = [['Distances', null]];
    }

    $_pageTitle = $_bcs ? end($_bcs)[0] : 'Tableau de bord';
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($_pageTitle ?? 'Back-office') ?> — Puy du Fou</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" rel="stylesheet">
<style>
:root {
  --sb-w: 256px;
  --sb-w-mini: 62px;
  --tb-h: 58px;
  --sb-bg: #0A1020;
  --sb-bg2: #0F172A;
  --sb-border: rgba(255,255,255,0.06);
  --sb-txt: #7B8899;
  --sb-txt-h: #E2E8F0;
  --sb-act-bg: rgba(99,102,241,0.16);
  --sb-act-txt: #A5B4FC;
  --sb-act-bar: #6366F1;
  --primary: #6366F1;
  --primary-d: #4F46E5;
  --primary-l: #EEF2FF;
  --primary-xl: #F5F3FF;
  --gold: #F59E0B;
  --gold-d: #D97706;
  --gold-l: #FFFBEB;
  --success: #10B981;
  --success-l: #ECFDF5;
  --danger: #EF4444;
  --danger-l: #FEF2F2;
  --warning: #F59E0B;
  --warning-l: #FFFBEB;
  --bg: #F0F4FA;
  --card: #FFFFFF;
  --border: #E2E8F0;
  --border-h: #CBD5E1;
  --txt1: #0F172A;
  --txt2: #475569;
  --txt3: #94A3B8;
  --sh-xs: 0 1px 2px rgba(0,0,0,.04);
  --sh-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.03);
  --sh-md: 0 4px 16px rgba(0,0,0,.08), 0 2px 4px rgba(0,0,0,.04);
  --sh-lg: 0 20px 48px rgba(0,0,0,.12), 0 8px 16px rgba(0,0,0,.06);
  --r-xs: 4px; --r-sm: 8px; --r: 12px; --r-lg: 16px; --r-xl: 22px;
  --t: 200ms cubic-bezier(.4,0,.2,1);
  --t-fast: 120ms cubic-bezier(.4,0,.2,1);
  --font: 'Inter', system-ui, -apple-system, sans-serif;
}
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; }
body { font-family: var(--font); background: var(--bg); color: var(--txt1); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
a { color: inherit; }

/* ─── PROGRESS BAR ─── */
.pg-bar { position:fixed; top:0; left:0; right:0; height:2.5px; z-index:9999; background:linear-gradient(90deg,var(--primary),#818CF8,#A78BFA); transform:scaleX(0); transform-origin:left; animation:pgIn .4s ease .05s forwards; pointer-events:none; }
@keyframes pgIn { from{transform:scaleX(0)} to{transform:scaleX(1)} }

/* ─── SHELL ─── */
.bo-app { display: flex; min-height: 100vh; }

/* ─── SIDEBAR ─── */
.bo-sb {
  width: var(--sb-w); flex-shrink: 0;
  background: var(--sb-bg);
  position: fixed; inset: 0 auto 0 0;
  display: flex; flex-direction: column;
  z-index: 200;
  transition: width var(--t), transform var(--t);
  overflow: hidden;
  border-right: 1px solid var(--sb-border);
}
.bo-sb.mini { width: var(--sb-w-mini); }

/* Logo */
.sb-logo {
  display: flex; align-items: center; gap: 10px;
  padding: 16px 14px;
  border-bottom: 1px solid var(--sb-border);
  text-decoration: none; flex-shrink: 0; overflow: hidden;
}
.sb-logo-icon {
  width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
  background: linear-gradient(135deg, var(--gold) 0%, var(--gold-d) 100%);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 14px rgba(245,158,11,.4);
}
.sb-logo-icon svg { color: #fff; }
.sb-logo-text { overflow: hidden; white-space: nowrap; }
.sb-logo-title { color: #F1F5F9; font-size: 14px; font-weight: 700; line-height: 1.2; }
.sb-logo-sub   { color: var(--sb-txt); font-size: 10.5px; margin-top: 1px; }

/* Nav */
.sb-nav { flex: 1; padding: 8px; overflow-y: auto; overflow-x: hidden; }
.sb-nav::-webkit-scrollbar { width: 3px; }
.sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 3px; }

.sb-section {
  color: var(--sb-txt); font-size: 9.5px; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; padding: 14px 10px 5px; white-space: nowrap; overflow: hidden;
}
.bo-sb.mini .sb-section { opacity: 0; height: 0; padding: 0; margin: 0; }

.sb-link {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 10px; border-radius: var(--r-sm);
  color: var(--sb-txt); text-decoration: none;
  font-size: 13.5px; font-weight: 500;
  transition: background var(--t), color var(--t);
  position: relative; margin-bottom: 2px; white-space: nowrap; overflow: hidden;
}
.sb-link:hover { background: rgba(255,255,255,.06); color: var(--sb-txt-h); }
.sb-link.active { background: var(--sb-act-bg); color: var(--sb-act-txt); }
.sb-link.active::before {
  content:''; position:absolute; left:0; top:6px; bottom:6px;
  width:3px; background:var(--sb-act-bar); border-radius:0 3px 3px 0;
}
.sb-link-icon { width:18px; height:18px; flex-shrink:0; transition:opacity var(--t); }
.sb-link svg.sb-link-icon { opacity:.6; }
.sb-link:hover svg.sb-link-icon, .sb-link.active svg.sb-link-icon { opacity:1; }
.sb-link-txt { overflow: hidden; }

/* Mini sidebar tooltips */
.bo-sb.mini .sb-link { justify-content: center; padding: 9px; }
.bo-sb.mini .sb-link-txt { display:none; }
.bo-sb.mini .sb-link::after {
  content: attr(data-tip);
  position: absolute; left: calc(var(--sb-w-mini) + 8px); top: 50%;
  transform: translateY(-50%);
  background: var(--txt1); color: #fff;
  padding: 4px 10px; border-radius: var(--r-xs);
  font-size: 12px; font-weight: 500; white-space: nowrap;
  pointer-events: none; opacity: 0;
  transition: opacity var(--t-fast), left var(--t-fast);
  z-index: 9000;
  box-shadow: var(--sh-md);
}
.bo-sb.mini .sb-link:hover::after { opacity: 1; left: calc(var(--sb-w-mini) + 12px); }

/* Sidebar toggle button */
.sb-toggle-btn {
  display: flex; align-items: center; justify-content: flex-end;
  padding: 10px 14px;
  border-top: 1px solid var(--sb-border); flex-shrink: 0;
}
.sb-toggle-btn button {
  background: none; border: none; cursor: pointer;
  color: var(--sb-txt); padding: 5px; border-radius: var(--r-xs);
  transition: color var(--t), background var(--t); line-height: 0;
}
.sb-toggle-btn button:hover { color: var(--sb-txt-h); background: rgba(255,255,255,.06); }
.sb-toggle-btn button svg { width: 16px; height: 16px; transition: transform var(--t); }
.bo-sb.mini .sb-toggle-btn button svg { transform: rotate(180deg); }
.bo-sb.mini .sb-toggle-btn { justify-content: center; padding: 10px 0; }

/* Footer */
.sb-foot {
  border-top: 1px solid var(--sb-border); padding: 12px 10px; flex-shrink: 0; overflow: hidden;
}
.sb-user { display:flex; align-items:center; gap:8px; margin-bottom:8px; min-width:0; overflow:hidden; }
.sb-avatar {
  width:30px; height:30px; border-radius:50%; flex-shrink:0;
  background: linear-gradient(135deg, var(--primary), var(--primary-d));
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:11px; font-weight:700; letter-spacing:.5px;
}
.sb-uinfo { overflow: hidden; white-space: nowrap; }
.sb-uname { color:#E2E8F0; font-size:12.5px; font-weight:600; overflow:hidden; text-overflow:ellipsis; }
.sb-urole { color:var(--sb-txt); font-size:10.5px; margin-top:1px; }
.sb-logout {
  display:flex; align-items:center; gap:7px;
  padding:6.5px 8px; border-radius:var(--r-xs);
  color:#F87171; text-decoration:none; font-size:12.5px; font-weight:500;
  transition:background var(--t), color var(--t); white-space:nowrap;
}
.sb-logout:hover { background:rgba(239,68,68,.1); color:#FCA5A5; }
.sb-logout svg { width:14px; height:14px; flex-shrink:0; }
.bo-sb.mini .sb-foot { padding: 10px 0; }
.bo-sb.mini .sb-user { display: none; }
.bo-sb.mini .sb-logout { justify-content: center; padding: 9px; font-size: 0; }
.bo-sb.mini .sb-logout svg { font-size: initial; }

/* ─── MAIN ─── */
.bo-main { flex:1; margin-left:var(--sb-w); display:flex; flex-direction:column; min-height:100vh; transition:margin-left var(--t); }
.bo-app.mini .bo-main { margin-left: var(--sb-w-mini); }

/* ─── TOPBAR ─── */
.bo-top {
  height: var(--tb-h); background: rgba(255,255,255,.9);
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 20px; gap: 10px;
  position: sticky; top: 0; z-index: 100;
}
.top-toggle {
  display:none; border:none; background:none; cursor:pointer;
  padding:7px; border-radius:var(--r-sm); color:var(--txt2);
  transition:background var(--t); line-height:0; flex-shrink:0;
}
.top-toggle:hover { background:var(--bg); }
.top-toggle svg { width:20px; height:20px; }

/* Breadcrumbs */
.bo-bc { display:flex; align-items:center; gap:5px; flex:1; min-width:0; }
.bc-home { color:var(--txt3); line-height:0; flex-shrink:0; }
.bc-home svg { width:15px; height:15px; }
.bc-home:hover { color:var(--txt2); }
.bc-sep { color:var(--txt3); }
.bc-sep svg { width:12px; height:12px; display:block; }
.bc-item { font-size:13px; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bc-item a { color:var(--txt3); text-decoration:none; transition:color var(--t); }
.bc-item a:hover { color:var(--primary); }
.bc-item.current { color:var(--txt1); font-weight:600; }

/* Topbar right */
.top-right { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.top-clock { font-size:12px; font-weight:500; color:var(--txt3); font-variant-numeric:tabular-nums; }
.top-pill {
  display:flex; align-items:center; gap:5px;
  background:#F0FDF4; color:#166534; border:1px solid #BBF7D0;
  padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600;
}
.top-pill::before { content:''; width:6px; height:6px; border-radius:50%; background:#22C55E; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }

.top-user { position:relative; }
.top-user-btn {
  display:flex; align-items:center; gap:6px; background:none; border:none; cursor:pointer;
  padding:5px 8px; border-radius:var(--r); transition:background var(--t);
}
.top-user-btn:hover { background:var(--bg); }
.top-avatar {
  width:28px; height:28px; border-radius:50%;
  background:linear-gradient(135deg, var(--primary), var(--primary-d));
  display:flex; align-items:center; justify-content:center;
  color:#fff; font-size:11px; font-weight:700; flex-shrink:0;
}
.top-uname { font-size:13px; font-weight:600; color:var(--txt1); }
.top-chev { width:14px; height:14px; color:var(--txt3); transition:transform var(--t); }
.top-user.open .top-chev { transform:rotate(180deg); }

.user-drop {
  position:absolute; top:calc(100% + 6px); right:0;
  min-width:180px; background:var(--card); border:1px solid var(--border);
  border-radius:var(--r); box-shadow:var(--sh-lg); overflow:hidden;
  opacity:0; transform:translateY(-6px) scale(.97); pointer-events:none;
  transition:opacity var(--t-fast), transform var(--t-fast);
  z-index:500;
}
.top-user.open .user-drop { opacity:1; transform:translateY(0) scale(1); pointer-events:all; }
.user-drop-head { padding:12px 14px; border-bottom:1px solid var(--border); }
.user-drop-name { font-size:13px; font-weight:600; color:var(--txt1); }
.user-drop-role { font-size:11.5px; color:var(--txt3); margin-top:1px; }
.user-drop-item {
  display:flex; align-items:center; gap:8px;
  padding:9px 14px; font-size:13px; font-weight:500;
  color:var(--txt2); text-decoration:none; transition:background var(--t);
}
.user-drop-item:hover { background:var(--bg); color:var(--txt1); }
.user-drop-item svg { width:14px; height:14px; flex-shrink:0; }
.user-drop-item.danger { color:var(--danger); }
.user-drop-item.danger:hover { background:var(--danger-l); }

/* ─── CONTENT ─── */
.bo-content { flex:1; padding:24px 22px; }

/* ─── FLASH ─── */
.bo-flash {
  display:flex; align-items:center; gap:10px;
  padding:11px 16px; border-radius:var(--r); font-size:13.5px; font-weight:500;
  margin-bottom:22px; border:1px solid transparent;
  animation:flashIn .25s ease;
}
@keyframes flashIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
.bo-flash svg { width:15px; height:15px; flex-shrink:0; }
.bo-flash-close {
  margin-left:auto; cursor:pointer; background:none; border:none; padding:2px;
  opacity:.5; line-height:0; transition:opacity var(--t); border-radius:4px;
}
.bo-flash-close:hover { opacity:1; }
.bo-flash-close svg { width:13px; height:13px; }
.fl-success { background:var(--success-l); color:#065F46; border-color:#A7F3D0; }
.fl-danger  { background:var(--danger-l);  color:#991B1B; border-color:#FECACA; }
.fl-warning { background:var(--warning-l); color:#78350F; border-color:#FDE68A; }

/* ─── CARDS ─── */
.bo-card { background:var(--card); border:1px solid var(--border); border-radius:var(--r-lg); box-shadow:var(--sh-sm); }
.bo-card-head {
  padding:14px 18px 12px;
  border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;
}
.bo-card-title { font-size:14px; font-weight:650; color:var(--txt1); }
.bo-card-body  { padding:18px; }

/* ─── STAT CARDS ─── */
.stat-card {
  background:var(--card); border:1px solid var(--border); border-radius:var(--r-lg);
  padding:18px; display:flex; align-items:flex-start; gap:14px;
  box-shadow:var(--sh-sm); transition:box-shadow var(--t), transform var(--t);
  text-decoration:none; color:inherit; overflow:hidden; position:relative;
}
.stat-card::after {
  content:''; position:absolute; inset:0; opacity:0;
  background:linear-gradient(135deg,transparent 70%,rgba(99,102,241,.04));
  transition:opacity var(--t);
}
.stat-card:hover { box-shadow:var(--sh-md); transform:translateY(-2px); color:inherit; }
.stat-card:hover::after { opacity:1; }
.stat-ico {
  width:42px; height:42px; border-radius:var(--r); flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.stat-ico svg { width:20px; height:20px; }
.ico-indigo { background:var(--primary-l); color:var(--primary); }
.ico-gold   { background:var(--gold-l);    color:var(--gold); }
.ico-green  { background:var(--success-l); color:var(--success); }
.ico-blue   { background:#DBEAFE;          color:#2563EB; }
.ico-rose   { background:#FFF1F2;          color:#F43F5E; }
.stat-body { flex:1; min-width:0; }
.stat-val { font-size:28px; font-weight:750; color:var(--txt1); line-height:1.1; letter-spacing:-.5px; }
.stat-lbl { font-size:12px; color:var(--txt2); font-weight:500; margin-top:3px; }
.stat-trend { display:inline-flex; align-items:center; gap:3px; font-size:11px; font-weight:600; margin-top:6px; padding:2px 7px; border-radius:20px; }
.trend-up   { background:#F0FDF4; color:#15803D; }
.trend-down { background:#FEF2F2; color:#DC2626; }
.trend-neu  { background:#F8FAFC; color:var(--txt3); }

/* ─── TABLE ─── */
.bo-table-wrap { overflow-x:auto; }
.bo-table { width:100%; border-collapse:separate; border-spacing:0; }
.bo-table thead th {
  background:#F8FAFC; padding:9px 16px;
  font-size:10.5px; font-weight:700; color:var(--txt3);
  text-transform:uppercase; letter-spacing:.06em;
  border-bottom:1px solid var(--border); white-space:nowrap;
  position:sticky; top:0;
}
.bo-table tbody tr { transition:background var(--t-fast); cursor:default; }
.bo-table tbody tr:hover { background:#F8FAFC; }
.bo-table tbody td {
  padding:11px 16px; font-size:13.5px; color:var(--txt1);
  border-bottom:1px solid var(--border); vertical-align:middle;
}
.bo-table tbody tr:last-child td { border-bottom:none; }
.td-muted { color:var(--txt3); font-size:12.5px; }
.td-actions { display:flex; gap:5px; justify-content:flex-end; flex-wrap:wrap; }

/* Search bar */
.table-search-wrap { padding:12px 16px; border-bottom:1px solid var(--border); }
.table-search {
  display:flex; align-items:center; gap:8px;
  background:var(--bg); border:1.5px solid var(--border);
  border-radius:var(--r-sm); padding:7px 12px;
  transition:border-color var(--t), box-shadow var(--t);
}
.table-search:focus-within { border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,.09); background:var(--card); }
.table-search svg { width:14px; height:14px; color:var(--txt3); flex-shrink:0; }
.table-search input { border:none; background:none; outline:none; font-family:var(--font); font-size:13.5px; color:var(--txt1); width:100%; }
.table-search input::placeholder { color:var(--txt3); }
.search-count { font-size:11.5px; color:var(--txt3); white-space:nowrap; }

/* ─── BUTTONS ─── */
.btn-bo {
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 14px; border-radius:var(--r-sm);
  font-size:13px; font-weight:500; font-family:var(--font);
  cursor:pointer; transition:all var(--t);
  border:1.5px solid transparent; text-decoration:none; line-height:1.4; white-space:nowrap;
}
.btn-bo svg { width:14px; height:14px; flex-shrink:0; }
.btn-bo:focus-visible { outline:2px solid var(--primary); outline-offset:2px; }
.btn-pri { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn-pri:hover { background:var(--primary-d); border-color:var(--primary-d); color:#fff; box-shadow:0 4px 12px rgba(99,102,241,.3); }
.btn-sec { background:var(--card); color:var(--txt2); border-color:var(--border); }
.btn-sec:hover { background:var(--bg); color:var(--txt1); border-color:var(--border-h); }
.btn-danger { background:transparent; color:var(--danger); border-color:#FECACA; }
.btn-danger:hover { background:var(--danger-l); color:var(--danger); border-color:#FCA5A5; }
.btn-ghost { background:transparent; color:var(--txt2); border-color:var(--border); }
.btn-ghost:hover { background:var(--bg); color:var(--txt1); }
.btn-sm  { padding:5px 10px; font-size:12px; }
.btn-sm svg { width:12px; height:12px; }
.btn-icon { padding:6px; border-radius:var(--r-sm); }
.btn-icon.btn-sm { padding:5px; }

/* ─── FORM ELEMENTS ─── */
.f-group { margin-bottom:16px; }
.f-label {
  display:block; font-size:12.5px; font-weight:600;
  color:var(--txt2); margin-bottom:5px;
}
.f-control {
  display:block; width:100%;
  padding:9px 12px; font-size:13.5px; font-family:var(--font);
  color:var(--txt1); background:var(--card);
  border:1.5px solid var(--border); border-radius:var(--r-sm);
  transition:border-color var(--t), box-shadow var(--t); outline:none;
  appearance:none; -webkit-appearance:none;
}
.f-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(99,102,241,.09); }
.f-control::placeholder { color:var(--txt3); }
textarea.f-control { resize:vertical; min-height:90px; line-height:1.6; }
select.f-control { cursor:pointer; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394A3B8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:32px; }
.f-hint { font-size:11.5px; color:var(--txt3); margin-top:4px; }

/* ─── BADGES ─── */
.bo-badge {
  display:inline-flex; align-items:center;
  padding:2px 8px; border-radius:20px;
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
.page-head-left h1 { font-size:22px; font-weight:750; color:var(--txt1); margin:0; letter-spacing:-.3px; }
.page-head-left p  { font-size:13px; color:var(--txt3); margin:4px 0 0; }
.page-head h1 { font-size:22px; font-weight:750; color:var(--txt1); margin:0; letter-spacing:-.3px; }
.page-head p  { font-size:13px; color:var(--txt3); margin:4px 0 0; }

/* ─── DATE GROUP (séances) ─── */
.dg { background:var(--card); border:1px solid var(--border); border-radius:var(--r); margin-bottom:10px; overflow:hidden; box-shadow:var(--sh-xs); }
.dg-head {
  padding:10px 16px; background:var(--sb-bg2); color:#E2E8F0;
  display:flex; align-items:center; justify-content:space-between;
  font-size:12.5px; font-weight:600; gap:8px;
}
.dg-badge {
  background:rgba(255,255,255,.12); color:#CBD5E1;
  padding:2px 9px; border-radius:12px; font-size:10.5px; font-weight:600;
}

/* ─── EMPTY STATE ─── */
.empty-st { text-align:center; padding:52px 24px; color:var(--txt3); }
.empty-st svg { width:40px; height:40px; margin-bottom:14px; opacity:.3; }
.empty-st p { font-size:13.5px; margin:0 0 14px; }

/* ─── DIVIDER ─── */
.bo-div { border:none; border-top:1px solid var(--border); margin:20px 0; }

/* ─── QUICK LINKS ─── */
.quick-link {
  display:flex; align-items:center; gap:10px;
  padding:13px 15px; border-radius:var(--r);
  border:1.5px solid var(--border); background:var(--card);
  text-decoration:none; color:var(--txt1); font-size:13.5px; font-weight:500;
  transition:all var(--t); box-shadow:var(--sh-xs);
}
.quick-link:hover { border-color:var(--primary); background:var(--primary-xl); color:var(--primary); box-shadow:var(--sh-md); transform:translateY(-1px); }
.ql-ico { width:34px; height:34px; border-radius:var(--r-sm); display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:background var(--t); }

/* ─── CONFIRM MODAL ─── */
.bo-modal-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.5);
  backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);
  z-index:9000; display:flex; align-items:center; justify-content:center;
  padding:20px; opacity:0; transition:opacity var(--t);
  pointer-events:none;
}
.bo-modal-overlay.open { opacity:1; pointer-events:all; }
.bo-modal {
  background:var(--card); border-radius:var(--r-xl); box-shadow:var(--sh-lg);
  width:100%; max-width:380px; padding:28px;
  transform:scale(.94) translateY(10px); transition:transform var(--t);
}
.bo-modal-overlay.open .bo-modal { transform:scale(1) translateY(0); }
.bo-modal-ico {
  width:46px; height:46px; border-radius:var(--r-lg); margin-bottom:16px;
  background:var(--danger-l); display:flex; align-items:center; justify-content:center;
}
.bo-modal-ico svg { width:22px; height:22px; color:var(--danger); }
.bo-modal h3 { font-size:17px; font-weight:700; color:var(--txt1); margin-bottom:8px; }
.bo-modal p { font-size:13.5px; color:var(--txt2); line-height:1.6; margin-bottom:22px; }
.bo-modal-actions { display:flex; gap:10px; justify-content:flex-end; }

/* ─── SIDEBAR OVERLAY ─── */
.sb-overlay {
  display:none; position:fixed; inset:0;
  background:rgba(0,0,0,.6); z-index:199;
  backdrop-filter:blur(2px);
}

/* ─── FILTER TABS ─── */
.filter-tabs { display:flex; gap:4px; flex-wrap:wrap; }
.ftab {
  display:inline-flex; align-items:center; gap:5px;
  padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
  cursor:pointer; border:1.5px solid var(--border); background:var(--card);
  color:var(--txt2); transition:all var(--t-fast); user-select:none;
}
.ftab:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-xl); }
.ftab.active { border-color:var(--primary); color:var(--primary); background:var(--primary-l); }
.ftab .count { font-size:10px; background:rgba(0,0,0,.07); padding:1px 5px; border-radius:10px; }
.ftab.active .count { background:rgba(99,102,241,.15); }

/* ─── LOGIN ─── */
.login-wrap {
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  padding:24px; position:relative; overflow:hidden;
  background: radial-gradient(ellipse at 20% 50%, #0F1A35 0%, #080E1A 50%, #0A0C18 100%);
}
.login-orbs { position:absolute; inset:0; overflow:hidden; pointer-events:none; }
.login-orb {
  position:absolute; border-radius:50%; filter:blur(60px);
  animation:orbFloat 8s ease-in-out infinite;
}
.orb1 { width:400px; height:400px; background:rgba(99,102,241,.18); top:-100px; right:-80px; animation-delay:0s; }
.orb2 { width:300px; height:300px; background:rgba(245,158,11,.1); bottom:-80px; left:-60px; animation-delay:-3s; }
.orb3 { width:200px; height:200px; background:rgba(16,185,129,.08); top:50%; left:50%; animation-delay:-5s; }
@keyframes orbFloat { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(20px,-20px) scale(1.05)} }

.login-card { width:100%; max-width:420px; position:relative; z-index:1; }
.login-logo-wrap { text-align:center; margin-bottom:28px; }
.login-logo-icon {
  width:56px; height:56px; background:linear-gradient(135deg,var(--gold),var(--gold-d));
  border-radius:16px; display:flex; align-items:center; justify-content:center;
  margin:0 auto 14px; box-shadow:0 8px 28px rgba(245,158,11,.4);
}
.login-logo-icon svg { color:#fff; }
.login-logo-title { color:#F1F5F9; font-size:22px; font-weight:750; letter-spacing:-.3px; }
.login-logo-sub   { color:#64748B; font-size:13px; margin-top:3px; }

.login-box {
  background:rgba(255,255,255,.97); border-radius:var(--r-xl);
  padding:30px 32px; box-shadow:0 32px 80px rgba(0,0,0,.5), 0 1px 0 rgba(255,255,255,.06) inset;
  border:1px solid rgba(255,255,255,.1);
}
.login-title { font-size:17px; font-weight:700; color:var(--txt1); margin-bottom:4px; }
.login-sub   { font-size:13px; color:var(--txt3); margin-bottom:22px; }
.login-err   {
  display:flex; align-items:center; gap:8px;
  background:var(--danger-l); color:#991B1B; border:1px solid #FECACA;
  padding:10px 14px; border-radius:var(--r-sm); font-size:13px; font-weight:500; margin-bottom:16px;
}
.login-err svg { width:14px; height:14px; flex-shrink:0; }
.pw-wrap { position:relative; }
.pw-wrap .f-control { padding-right:42px; }
.pw-toggle {
  position:absolute; right:10px; top:50%; transform:translateY(-50%);
  background:none; border:none; cursor:pointer; color:var(--txt3);
  line-height:0; padding:4px; border-radius:4px; transition:color var(--t);
}
.pw-toggle:hover { color:var(--txt2); }
.pw-toggle svg { width:16px; height:16px; }
.login-btn {
  width:100%; padding:10.5px; font-size:14px; font-weight:600;
  background:var(--primary); color:#fff; border:none; border-radius:var(--r-sm);
  cursor:pointer; transition:background var(--t), transform var(--t), box-shadow var(--t);
  font-family:var(--font); letter-spacing:.01em;
}
.login-btn:hover { background:var(--primary-d); transform:translateY(-1px); box-shadow:0 8px 20px rgba(99,102,241,.35); }
.login-btn:active { transform:translateY(0); box-shadow:none; }

/* ─── RESPONSIVE ─── */
@media(max-width:1024px) {
  .bo-sb { transform:translateX(-100%); width:var(--sb-w) !important; }
  .bo-sb.open { transform:translateX(0); }
  .sb-overlay.open { display:block; }
  .top-toggle { display:inline-flex !important; }
  .bo-main { margin-left:0 !important; }
  .bo-app.mini .bo-main { margin-left:0 !important; }
  .sb-toggle-btn { display:none; }
  .top-clock { display:none; }
}
@media(max-width:640px) {
  .bo-content { padding:14px 12px; }
  .bo-top { padding:0 12px; }
  .bo-bc { display:none; }
  .login-box { padding:24px 20px; }
  .page-head h1 { font-size:19px; }
}
@media(max-width:480px) {
  .top-pill { display:none; }
  .top-uname { display:none; }
}
</style>
</head>
<body>
<div class="pg-bar"></div>
<?php if (!$_isAuth): ?>
<?= $content ?>
<?php else: ?>
<div class="bo-app" id="boApp">
  <div class="sb-overlay" id="sbOverlay"></div>

  <!-- ─── SIDEBAR ─── -->
  <nav class="bo-sb" id="boSb">
    <a href="<?= $url('/') ?>" class="sb-logo">
      <div class="sb-logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div class="sb-logo-text">
        <div class="sb-logo-title">Puy du Fou</div>
        <div class="sb-logo-sub">Back-office</div>
      </div>
    </a>

    <div class="sb-nav">
      <div class="sb-section">Navigation</div>
      <a href="<?= $url('/') ?>" class="sb-link <?= $_active('/') ?>" data-tip="Tableau de bord">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        <span class="sb-link-txt">Tableau de bord</span>
      </a>

      <div class="sb-section">Contenu</div>
      <a href="<?= $url('/spectacles') ?>" class="sb-link <?= $_active('/spectacles') ?>" data-tip="Spectacles">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
        <span class="sb-link-txt">Spectacles</span>
      </a>
      <a href="<?= $url('/lieux') ?>" class="sb-link <?= $_active('/lieux') ?>" data-tip="Lieux">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span class="sb-link-txt">Lieux</span>
      </a>
      <a href="<?= $url('/seances') ?>" class="sb-link <?= $_active('/seances') ?>" data-tip="Séances">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span class="sb-link-txt">Séances</span>
      </a>

      <div class="sb-section">Paramètres</div>
      <a href="<?= $url('/distances') ?>" class="sb-link <?= $_active('/distances') ?>" data-tip="Distances">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="19" cy="12" r="2"/><path d="M7 12h10"/><path d="M5 10V7l3 3-3 3"/><path d="M19 14v3l-3-3 3-3"/></svg>
        <span class="sb-link-txt">Distances</span>
      </a>
      <a href="<?= $url('/jours') ?>" class="sb-link <?= $_active('/jours') ?>" data-tip="Jours d'ouverture">
        <svg class="sb-link-icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span class="sb-link-txt">Jours d'ouverture</span>
      </a>
    </div>

    <div class="sb-toggle-btn">
      <button id="sbMiniToggle" aria-label="Réduire le menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
    </div>

    <div class="sb-foot">
      <div class="sb-user">
        <div class="sb-avatar"><?= htmlspecialchars($_initials) ?></div>
        <div class="sb-uinfo">
          <div class="sb-uname"><?= htmlspecialchars($_fullname) ?></div>
          <div class="sb-urole">Gestionnaire</div>
        </div>
      </div>
      <a href="<?= $url('/logout') ?>" class="sb-logout" title="Déconnexion">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Déconnexion</span>
      </a>
    </div>
  </nav>

  <!-- ─── MAIN ─── -->
  <div class="bo-main">
    <header class="bo-top">
      <button class="top-toggle" id="sbToggle" aria-label="Menu" style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>

      <!-- Breadcrumbs -->
      <nav class="bo-bc">
        <a href="<?= $url('/') ?>" class="bc-home" title="Accueil">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </a>
        <?php if (!empty($_bcs)): ?>
          <?php foreach ($_bcs as $i => $_bc): ?>
            <span class="bc-sep"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
            <span class="bc-item <?= $i === count($_bcs)-1 ? 'current' : '' ?>">
              <?php if ($_bc[1] && $i < count($_bcs)-1): ?>
                <a href="<?= $url($_bc[1]) ?>"><?= htmlspecialchars($_bc[0]) ?></a>
              <?php else: ?>
                <?= htmlspecialchars($_bc[0]) ?>
              <?php endif; ?>
            </span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="bc-sep"><svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></span>
          <span class="bc-item current">Tableau de bord</span>
        <?php endif; ?>
      </nav>

      <div class="top-right">
        <span class="top-clock" id="topClock"></span>
        <div class="top-pill">Système en ligne</div>

        <div class="top-user" id="topUser">
          <button class="top-user-btn" id="topUserBtn" aria-label="Menu utilisateur">
            <div class="top-avatar"><?= htmlspecialchars($_initials) ?></div>
            <span class="top-uname"><?= htmlspecialchars(explode(' ', $_fullname)[0]) ?></span>
            <svg class="top-chev" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="user-drop" id="userDrop">
            <div class="user-drop-head">
              <div class="user-drop-name"><?= htmlspecialchars($_fullname) ?></div>
              <div class="user-drop-role">Gestionnaire</div>
            </div>
            <a href="<?= $url('/logout') ?>" class="user-drop-item danger">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Déconnexion
            </a>
          </div>
        </div>
      </div>
    </header>

    <div class="bo-content">
      <?php if (!empty($flash)):
        $__ft = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_type']);
        $__icons = [
          'success' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="20 6 9 17 4 12"/></svg>',
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

<!-- ─── CONFIRM MODAL ─── -->
<div class="bo-modal-overlay" id="confirmModal" role="dialog" aria-modal="true">
  <div class="bo-modal">
    <div class="bo-modal-ico">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
    </div>
    <h3 id="modalTitle">Confirmer la suppression</h3>
    <p id="modalMsg">Cette action est irréversible.</p>
    <div class="bo-modal-actions">
      <button class="btn-bo btn-sec" id="modalCancel">Annuler</button>
      <button class="btn-bo btn-danger" id="modalConfirm">Supprimer</button>
    </div>
  </div>
</div>

<script>
(function(){
  // ─── Mini sidebar ───
  var app=document.getElementById('boApp'),
      sb=document.getElementById('boSb'),
      ov=document.getElementById('sbOverlay'),
      tg=document.getElementById('sbToggle'),
      mt=document.getElementById('sbMiniToggle');

  var mini=localStorage.getItem('bo_mini')==='1';
  if(mini){app.classList.add('mini');sb.classList.add('mini');}

  if(mt){
    mt.addEventListener('click',function(){
      mini=!mini;
      app.classList.toggle('mini',mini);
      sb.classList.toggle('mini',mini);
      localStorage.setItem('bo_mini',mini?'1':'0');
    });
  }

  // ─── Mobile sidebar ───
  if(tg){
    tg.addEventListener('click',function(){sb.classList.toggle('open');ov.classList.toggle('open');});
    ov.addEventListener('click',function(){sb.classList.remove('open');ov.classList.remove('open');});
  }

  // ─── Flash auto-dismiss ───
  var fl=document.getElementById('boFlash');
  if(fl){setTimeout(function(){fl.style.transition='opacity .35s';fl.style.opacity='0';setTimeout(function(){fl&&fl.remove();},350);},5000);}

  // ─── Live clock ───
  var clk=document.getElementById('topClock');
  if(clk){
    function tick(){
      var d=new Date(),
          h=String(d.getHours()).padStart(2,'0'),
          m=String(d.getMinutes()).padStart(2,'0');
      clk.textContent=h+':'+m;
    }
    tick();setInterval(tick,10000);
  }

  // ─── User dropdown ───
  var tu=document.getElementById('topUser'),
      tub=document.getElementById('topUserBtn'),
      ud=document.getElementById('userDrop');
  if(tub){
    tub.addEventListener('click',function(e){e.stopPropagation();tu.classList.toggle('open');});
    document.addEventListener('click',function(){tu.classList.remove('open');});
  }

  // ─── Custom confirm modal ───
  var modal=document.getElementById('confirmModal'),
      mTitle=document.getElementById('modalTitle'),
      mMsg=document.getElementById('modalMsg'),
      mCancel=document.getElementById('modalCancel'),
      mConfirm=document.getElementById('modalConfirm');
  var _pendingForm=null;

  function openModal(msg,form){
    _pendingForm=form;
    mMsg.textContent=msg||'Cette action est irréversible.';
    modal.classList.add('open');
    mCancel.focus();
  }
  function closeModal(){modal.classList.remove('open');_pendingForm=null;}

  if(mCancel) mCancel.addEventListener('click',closeModal);
  if(modal) modal.addEventListener('click',function(e){if(e.target===modal)closeModal();});
  if(mConfirm){
    mConfirm.addEventListener('click',function(){
      if(_pendingForm){
        var f=_pendingForm;
        _pendingForm=null;
        modal.classList.remove('open');
        // Remove the event listener then submit natively
        f.dataset.confirmed='1';
        f.submit();
      }
    });
  }

  // Intercept forms with onsubmit confirm
  document.querySelectorAll('form[onsubmit]').forEach(function(form){
    var attr=form.getAttribute('onsubmit')||'';
    var match=attr.match(/confirm\(['"]([^'"]+)['"]\)/);
    if(match){
      var msg=match[1];
      form.removeAttribute('onsubmit');
      form.addEventListener('submit',function(e){
        if(form.dataset.confirmed==='1'){form.dataset.confirmed='';return;}
        e.preventDefault();
        openModal(msg,form);
      });
    }
  });

  // Keyboard: Escape closes modal/dropdown
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){
      closeModal();
      if(tu) tu.classList.remove('open');
      if(sb) {sb.classList.remove('open');ov.classList.remove('open');}
    }
  });
})();
</script>
<?php endif; ?>
</body>
</html>
