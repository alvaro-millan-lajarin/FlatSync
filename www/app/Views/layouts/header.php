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

<!-- Mobile top bar (only visible on mobile) -->
<div class="mobile-topbar" id="mobile-topbar">
  <button class="hamburger" id="hamburger" onclick="toggleSidebar()" aria-label="Menú">
    <span class="hamburger-icon"></span>
  </button>
  <span class="mobile-topbar-title">flat<span>sync</span></span>
</div>

<!-- Overlay (mobile only) -->
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">

  <!-- Logo / home selector -->
  <div class="sidebar-logo" onclick="toggleHomeMenu()" title="Cambiar hogar">
    <h1>flat<span>sync</span></h1>
    <p>
      <i data-lucide="home" style="width:11px;height:11px;flex-shrink:0;opacity:.5"></i>
      <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        <?= esc(session()->get('home_name') ?? lang('App.nav_no_home')) ?>
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
          <span style="font-size:0.65rem;background:rgba(96,165,250,0.2);color:#93C5FD;padding:1px 6px;border-radius:10px"><?= lang('App.nav_active') ?></span>
        <?php endif; ?>
      </button>
    </form>
    <?php endforeach; ?>

    <div style="margin:6px 0 2px;border-top:1px solid rgba(255,255,255,0.08);padding-top:6px;display:flex;flex-direction:column;gap:1px">
      <a href="<?= site_url('/homes/join') ?>" class="home-menu-link"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="key-round" style="width:14px;height:14px;flex-shrink:0"></i> <?= lang('App.nav_join_code') ?>
      </a>
      <a href="<?= site_url('/homes/create') ?>" class="home-menu-link"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="plus" style="width:14px;height:14px;flex-shrink:0"></i> <?= lang('App.nav_new_home') ?>
      </a>
      <?php if ($_activeHomeId): ?>
      <a href="<?= site_url('/homes/leave') ?>" class="home-menu-link" style="color:rgba(255,255,255,0.35)"
         onmouseover="this.classList.add('hov')" onmouseout="this.classList.remove('hov')">
        <i data-lucide="log-out" style="width:14px;height:14px;flex-shrink:0"></i> <?= lang('App.nav_leave_session') ?>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Nav sections -->
  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_general') ?></div>
    <a href="<?= site_url('/dashboard') ?>" class="nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
      <i data-lucide="layout-dashboard" class="nav-icon"></i> <?= lang('App.nav_dashboard') ?>
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_tasks') ?></div>
    <a href="<?= site_url('/chores') ?>" class="nav-item <?= $activeNav === 'chores' ? 'active' : '' ?>">
      <i data-lucide="calendar-check" class="nav-icon"></i> <?= lang('App.nav_calendar') ?>
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_finances') ?></div>
    <a href="<?= site_url('/expenses') ?>" class="nav-item <?= $activeNav === 'expenses' ? 'active' : '' ?>">
      <i data-lucide="wallet" class="nav-icon"></i> <?= lang('App.nav_expenses') ?>
    </a>
    <a href="<?= site_url('/expenses/balance') ?>" class="nav-item <?= $activeNav === 'balance' ? 'active' : '' ?>">
      <i data-lucide="scale" class="nav-icon"></i> <?= lang('App.nav_balance') ?>
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_services') ?></div>
    <a href="<?= site_url('/services') ?>" class="nav-item <?= $activeNav === 'services' ? 'active' : '' ?>">
      <i data-lucide="wrench" class="nav-icon"></i> <?= lang('App.nav_nearby') ?>
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_home') ?></div>
    <a href="<?= site_url('/chat') ?>" class="nav-item <?= $activeNav === 'chat' ? 'active' : '' ?>">
      <i data-lucide="message-circle" class="nav-icon"></i> <?= lang('App.nav_chat') ?>
    </a>
    <a href="<?= site_url('/members') ?>" class="nav-item <?= $activeNav === 'members' ? 'active' : '' ?>">
      <i data-lucide="users" class="nav-icon"></i> <?= lang('App.nav_members') ?>
    </a>
    <a href="<?= site_url('/profile') ?>" class="nav-item <?= $activeNav === 'profile' ? 'active' : '' ?>">
      <i data-lucide="circle-user" class="nav-icon"></i> <?= lang('App.nav_profile') ?>
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label"><?= lang('App.nav_extra') ?></div>
    <a href="<?= site_url('/game') ?>" class="nav-item <?= $activeNav === 'game' ? 'active' : '' ?>">
      <i data-lucide="gamepad-2" class="nav-icon"></i> <?= lang('App.nav_game') ?>
    </a>
  </div>

  <!-- Language switcher -->
  <?php
  $_lang = session()->get('lang') ?? 'es';
  $_flags = [
    'es' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" style="width:100%;height:100%;display:block"><rect width="3" height="2" fill="#AA151B"/><rect y=".5" width="3" height="1" fill="#F1BF00"/></svg>',
    'en' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 36" style="width:100%;height:100%;display:block"><rect width="60" height="36" fill="#012169"/><path d="M0,0 60,36M60,0 0,36" stroke="#fff" stroke-width="7.2"/><path d="M0,0 60,36M60,0 0,36" stroke="#C8102E" stroke-width="3.6"/><rect x="24" y="0" width="12" height="36" fill="#fff"/><rect x="0" y="12" width="60" height="12" fill="#fff"/><rect x="25.5" y="0" width="9" height="36" fill="#C8102E"/><rect x="0" y="13.5" width="60" height="9" fill="#C8102E"/></svg>',
    'ca' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6" style="width:100%;height:100%;display:block"><rect width="9" height="6" fill="#FCDD09"/><rect y=".667" width="9" height=".667" fill="#C60B1E"/><rect y="2" width="9" height=".667" fill="#C60B1E"/><rect y="3.333" width="9" height=".667" fill="#C60B1E"/><rect y="4.667" width="9" height=".667" fill="#C60B1E"/></svg>',
  ];
  $_flagTitles = ['es'=>'Español','en'=>'English','ca'=>'Català'];
  ?>
  <div class="sidebar-section" style="padding-bottom:0">
    <div class="sidebar-section-label"><?= lang('App.lang_switch') ?></div>
    <div style="display:flex;gap:8px;padding:0 8px 12px">
      <?php foreach($_flags as $code => $svg): ?>
      <a href="<?= site_url('/lang/'.$code) ?>" title="<?= $_flagTitles[$code] ?>"
         style="display:block;width:38px;height:25px;border-radius:4px;overflow:hidden;text-decoration:none;
                flex-shrink:0;
                opacity:<?= $_lang===$code ? '1' : '0.35' ?>;
                box-shadow:<?= $_lang===$code ? '0 0 0 2px #93C5FD' : '0 0 0 1px rgba(255,255,255,0.15)' ?>;
                transition:opacity .15s,box-shadow .15s">
        <?= $svg ?>
      </a>
      <?php endforeach; ?>
    </div>
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
          <span><a href="<?= site_url('/logout') ?>" onclick="event.stopPropagation()" style="color:rgba(255,255,255,0.45);text-decoration:none;font-size:0.7rem;display:inline-flex;align-items:center;gap:3px"><i data-lucide="log-out" style="width:10px;height:10px"></i> <?= lang('App.nav_logout') ?></a></span>
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
