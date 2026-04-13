<?php
$activeNav    = $activeNav ?? '';
$pageTitle    = $pageTitle ?? '';
$pageSubtitle = $pageSubtitle ?? '';
$topbarAction = $topbarAction ?? '';

$_userId       = session()->get('user_id');
$_activeHomeId = session()->get('home_id');
$_myHomes      = [];
if ($_userId) {
    $uhModel  = new \App\Models\UserHomesModel();
    $_myHomes = $uhModel->getHomesForUser($_userId);
}
helper('avatar');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?: 'FlatMate') ?> — FlatMate</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>?v=<?= filemtime(FCPATH . 'css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="app-shell">

<!-- Hamburger (mobile only) -->
<button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Menú">
  <i data-lucide="menu" style="width:20px;height:20px"></i>
</button>

<!-- Overlay (mobile only) -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">

  <!-- Logo / home selector -->
  <div class="sidebar-logo" onclick="toggleHomeMenu()" title="Cambiar hogar">
    <h1>flat<span>sync</span></h1>
    <p>
      <i data-lucide="home" style="width:11px;height:11px;flex-shrink:0;opacity:.5"></i>
      <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        <?= esc(session()->get('home_name') ?? '— Sin sesión —') ?>
      </span>
      <i data-lucide="chevron-down" style="width:11px;height:11px;flex-shrink:0;opacity:.45"></i>
    </p>
  </div>

  <!-- Home dropdown -->
  <div id="home-menu" style="display:none;padding:6px 8px;border-bottom:1px solid rgba(255,255,255,0.07)">
    <?php foreach ($_myHomes as $h): ?>
    <?php $isActive = $h['id'] == $_activeHomeId; ?>
    <form method="post" action="<?= site_url('/homes/switch/' . $h['id']) ?>">
      <?= csrf_field() ?>
      <button type="submit" style="width:100%;text-align:left;background:<?= $isActive ? 'rgba(255,255,255,0.1)' : 'transparent' ?>;border:none;border-radius:6px;padding:8px 10px;color:<?= $isActive ? '#fff' : 'rgba(255,255,255,0.65)' ?>;cursor:pointer;font-family:inherit;font-size:0.82rem;display:flex;align-items:center;gap:9px;margin-bottom:1px;transition:background .15s"
        onmouseover="this.style.background='rgba(255,255,255,0.1)';this.style.color='#fff'"
        onmouseout="this.style.background='<?= $isActive ? 'rgba(255,255,255,0.1)' : 'transparent' ?>';this.style.color='<?= $isActive ? '#fff' : 'rgba(255,255,255,0.65)' ?>'">
        <i data-lucide="home" style="width:13px;height:13px;flex-shrink:0;opacity:.7"></i>
        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc($h['name']) ?></span>
        <?php if ($isActive): ?>
          <span style="font-size:0.65rem;background:rgba(96,165,250,0.2);color:#93C5FD;padding:1px 6px;border-radius:10px">activo</span>
        <?php endif; ?>
      </button>
    </form>
    <?php endforeach; ?>

    <div style="margin:6px 0 2px;border-top:1px solid rgba(255,255,255,0.08);padding-top:6px;display:flex;flex-direction:column;gap:1px">
      <a href="<?= site_url('/homes/join') ?>" class="home-menu-link"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="key-round" style="width:14px;height:14px;flex-shrink:0"></i> Unirme con código
      </a>
      <a href="<?= site_url('/homes/create') ?>" class="home-menu-link"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="plus" style="width:14px;height:14px;flex-shrink:0"></i> Nuevo hogar
      </a>
      <?php if ($_activeHomeId): ?>
      <a href="<?= site_url('/homes/leave') ?>" class="home-menu-link" style="color:rgba(255,255,255,0.35)"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="log-out" style="width:14px;height:14px;flex-shrink:0"></i> Salir de esta sesión
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Nav sections -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">General</div>
    <a href="<?= site_url('/') ?>" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i data-lucide="layout-dashboard" class="nav-icon"></i> Dashboard
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Tareas</div>
    <a href="<?= site_url('/chores') ?>" class="nav-item <?= $activeNav === 'chores' ? 'active' : '' ?>">
      <i data-lucide="calendar-check" class="nav-icon"></i> Calendario
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Finanzas</div>
    <a href="<?= site_url('/expenses') ?>" class="nav-item <?= $activeNav === 'expenses' ? 'active' : '' ?>">
      <i data-lucide="wallet" class="nav-icon"></i> Gastos
    </a>
    <a href="<?= site_url('/expenses/balance') ?>" class="nav-item <?= $activeNav === 'balance' ? 'active' : '' ?>">
      <i data-lucide="scale" class="nav-icon"></i> Balance
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Servicios</div>
    <a href="<?= site_url('/services') ?>" class="nav-item <?= $activeNav === 'services' ? 'active' : '' ?>">
      <i data-lucide="wrench" class="nav-icon"></i> Servicios cercanos
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Hogar</div>
    <a href="<?= site_url('/chat') ?>" class="nav-item <?= $activeNav === 'chat' ? 'active' : '' ?>">
      <i data-lucide="message-circle" class="nav-icon"></i> Chat
    </a>
    <a href="<?= site_url('/members') ?>" class="nav-item <?= $activeNav === 'members' ? 'active' : '' ?>">
      <i data-lucide="users" class="nav-icon"></i> Miembros
    </a>
    <a href="<?= site_url('/profile') ?>" class="nav-item <?= $activeNav === 'profile' ? 'active' : '' ?>">
      <i data-lucide="circle-user" class="nav-icon"></i> Mi perfil
    </a>
  </div>

  <!-- User chip -->
  <div class="sidebar-bottom">
    <a href="<?= site_url('/profile') ?>" style="text-decoration:none">
      <div class="user-chip">
        <?php $_avatar = session()->get('avatar_url'); ?>
        <?php if ($_avatar): ?>
          <img src="<?= base_url($_avatar) ?>" alt="avatar"
               style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
        <?php else: ?>
          <div class="user-avatar"><?= strtoupper(substr(session()->get('username') ?? 'U', 0, 1)) ?></div>
        <?php endif; ?>
        <div class="user-info">
          <strong><?= esc(session()->get('username') ?? 'Usuario') ?></strong>
          <span><a href="<?= site_url('/logout') ?>" onclick="event.stopPropagation()" style="color:rgba(255,255,255,0.4);text-decoration:none;font-size:0.7rem;display:inline-flex;align-items:center;gap:3px"><i data-lucide="log-out" style="width:10px;height:10px"></i> Cerrar sesión</a></span>
        </div>
      </div>
    </a>
  </div>

</aside>

<!-- Main -->
<main class="main-content">
  <div class="topbar">
    <div>
      <h2 class="page-title"><?= esc($pageTitle) ?></h2>
      <?php if (!empty($pageSubtitle)): ?>
        <p class="page-subtitle"><?= esc($pageSubtitle) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!empty($topbarAction)): ?>
      <?= $topbarAction ?>
    <?php endif; ?>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0"></i> <?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><i data-lucide="alert-triangle" style="width:15px;height:15px;flex-shrink:0"></i> <?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

<script>
function toggleHomeMenu() {
  const m = document.getElementById('home-menu');
  m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  const logo = document.querySelector('.sidebar-logo');
  const menu = document.getElementById('home-menu');
  if (menu && !logo.contains(e.target) && !menu.contains(e.target)) {
    menu.style.display = 'none';
  }
});

function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-overlay').style.display =
    document.getElementById('sidebar').classList.contains('open') ? 'block' : 'none';
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').style.display = 'none';
}
// Close sidebar when a nav link is clicked on mobile
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.sidebar .nav-item').forEach(function(link) {
    link.addEventListener('click', closeSidebar);
  });
});
</script>
