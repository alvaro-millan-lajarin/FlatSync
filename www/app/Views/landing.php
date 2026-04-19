<?php
if (!session()->has('lang')) {
    $_supported = ['ca','es','en'];
    $_header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'es';
    $_detected = 'es';
    foreach (explode(',', $_header) as $_part) {
        $_tag = strtolower(trim(explode(';', $_part)[0]));
        $_lang2 = explode('-', $_tag)[0];
        if (in_array($_tag, $_supported)) { $_detected = $_tag; break; }
        if (in_array($_lang2, $_supported)) { $_detected = $_lang2; break; }
    }
    session()->set('lang', $_detected);
}
$_locale = session()->get('lang');
\Config\Services::language()->setLocale($_locale);

$_typewriter = lang('App.landing_typewriter');
?>
<!DOCTYPE html>
<html lang="<?= $_locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= lang('App.landing_title') ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --primary:   #2563EB;
      --primary-d: #1D4ED8;
      --dark:      #0F172A;
      --text:      #1E293B;
      --muted:     #64748B;
      --surface:   #F8FAFC;
      --border:    #E2E8F0;
      --radius:    14px;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      color: var(--text);
      background: #fff;
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 6%;
      height: 66px;
      background: #fff;
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .nav-logo {
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      color: var(--dark);
      text-decoration: none;
    }
    .nav-logo span { color: var(--primary); }
    .nav-links { display: flex; align-items: center; gap: 12px; }
    .btn-nav-login {
      padding: 8px 20px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      background: transparent;
      color: var(--text);
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      transition: border-color .15s, color .15s;
    }
    .btn-nav-login:hover { border-color: var(--primary); color: var(--primary); }
    .btn-nav-register {
      padding: 8px 20px;
      border-radius: 8px;
      background: var(--primary);
      color: #fff;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      transition: background .15s;
    }
    .btn-nav-register:hover { background: var(--primary-d); }

    /* ── HERO ── */
    .hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: 60px;
      padding: 80px 6% 90px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #EFF6FF;
      color: var(--primary);
      font-size: 0.78rem;
      font-weight: 700;
      padding: 5px 12px;
      border-radius: 20px;
      margin-bottom: 20px;
      letter-spacing: .03em;
    }
    .hero h1 {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.04em;
      color: var(--dark);
      margin-bottom: 20px;
    }
    .hero h1 em {
      font-style: normal;
      color: var(--primary);
    }
    .hero p {
      font-size: 1.05rem;
      color: var(--muted);
      line-height: 1.7;
      max-width: 460px;
      margin-bottom: 32px;
    }
    .hero-actions { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .btn-hero {
      padding: 14px 32px;
      border-radius: 10px;
      background: var(--primary);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      text-decoration: none;
      transition: background .15s, transform .1s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-hero:hover { background: var(--primary-d); transform: translateY(-1px); }
    .btn-hero-ghost {
      padding: 14px 24px;
      border-radius: 10px;
      border: 1.5px solid var(--border);
      color: var(--text);
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      transition: border-color .15s;
    }
    .btn-hero-ghost:hover { border-color: var(--primary); color: var(--primary); }
    .hero-note {
      margin-top: 16px;
      font-size: 0.8rem;
      color: var(--muted);
    }

    /* ── HERO ILLUSTRATION ── */
    .hero-illustration {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .flat-card {
      background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
      border-radius: 24px;
      padding: 36px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 60px rgba(37,99,235,0.12);
      position: relative;
      overflow: hidden;
    }
    .flat-card::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 160px; height: 160px;
      border-radius: 50%;
      background: rgba(37,99,235,0.08);
    }
    .flat-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }
    .flat-card-title { font-size: 0.75rem; font-weight: 700; color: var(--primary); letter-spacing: .08em; text-transform: uppercase; }
    .flat-card-badge { background: #2563EB; color: #fff; font-size: 0.7rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
    .flat-members { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; }
    .flat-member {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #fff;
      border-radius: 10px;
      padding: 10px 14px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .flat-member-left { display: flex; align-items: center; gap: 10px; }
    .avatar {
      width: 32px; height: 32px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.8rem; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .flat-member-name { font-size: 0.85rem; font-weight: 600; color: var(--dark); }
    .flat-member-sub  { font-size: 0.72rem; color: var(--muted); }
    .flat-member-amount { font-size: 0.85rem; font-weight: 700; }
    .amount-pos { color: #16A34A; }
    .amount-neg { color: #DC2626; }
    .flat-total {
      background: var(--primary);
      border-radius: 10px;
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .flat-total-label { font-size: 0.78rem; font-weight: 600; color: rgba(255,255,255,0.75); }
    .flat-total-value { font-size: 1.1rem; font-weight: 800; color: #fff; }

    /* ── FEATURES ── */
    .features {
      background: var(--surface);
      padding: 80px 6%;
      border-top: 1px solid var(--border);
    }
    .features-inner { max-width: 1100px; margin: 0 auto; }
    .features-label {
      text-align: center;
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--primary);
      margin-bottom: 12px;
    }
    .features h2 {
      text-align: center;
      font-size: clamp(1.6rem, 3vw, 2.2rem);
      font-weight: 800;
      letter-spacing: -0.035em;
      color: var(--dark);
      margin-bottom: 48px;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
    }
    .feature-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 28px 24px;
      transition: box-shadow .2s, transform .2s;
    }
    .feature-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-2px); }
    .feature-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px;
    }
    .feature-card h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .feature-card p  { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

    /* ── CTA BOTTOM ── */
    .cta-section {
      background: var(--dark);
      padding: 80px 6%;
      text-align: center;
    }
    .cta-section h2 {
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.04em;
      margin-bottom: 14px;
    }
    .cta-section p { font-size: 1rem; color: rgba(255,255,255,0.55); margin-bottom: 32px; }
    .btn-cta {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 15px 36px;
      background: var(--primary);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      border-radius: 10px;
      text-decoration: none;
      transition: background .15s, transform .1s;
    }
    .btn-cta:hover { background: var(--primary-d); transform: translateY(-1px); }

    /* ── FOOTER ── */
    footer {
      padding: 28px 6%;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }
    footer .logo { font-size: 1rem; font-weight: 800; color: var(--dark); letter-spacing: -0.03em; }
    footer .logo span { color: var(--primary); }
    footer p { font-size: 0.8rem; color: var(--muted); }

    /* ── ANIMATIONS ── */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInRight {
      from { opacity: 0; transform: translateX(36px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to   { opacity: 1; }
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-10px); }
    }
    .hero-illustration { animation: float 5s ease-in-out infinite; }

    .hero-badge  { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .1s; }
    .hero h1     { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .25s; }
    .hero > .hero-text > p { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .4s; }
    .hero-actions { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .55s; }
    .hero-note   { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .65s; }
    .hero-illustration { opacity: 0; animation: fadeInRight .7s ease forwards, float 5s ease-in-out 0.7s infinite; animation-fill-mode: forwards; }

    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity .55s ease, transform .55s ease;
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    .feature-card:nth-child(1) { transition-delay: .0s; }
    .feature-card:nth-child(2) { transition-delay: .1s; }
    .feature-card:nth-child(3) { transition-delay: .2s; }
    .feature-card:nth-child(4) { transition-delay: .3s; }

    @keyframes pulse-dot {
      0%, 100% { transform: scale(1); opacity: 1; }
      50%       { transform: scale(1.5); opacity: .6; }
    }
    .badge-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: var(--primary);
      animation: pulse-dot 1.8s ease infinite;
      flex-shrink: 0;
    }

    @keyframes highlight {
      0%   { background: #fff; }
      40%  { background: #EFF6FF; }
      100% { background: #fff; }
    }
    .flat-member { animation: highlight 3.5s ease infinite; }
    .flat-member:nth-child(2) { animation-delay: 1.2s; }
    .flat-member:nth-child(3) { animation-delay: 2.4s; }

    /* ── SCROLL PROGRESS BAR ── */
    #scroll-progress {
      position: fixed; top: 0; left: 0; height: 3px;
      background: linear-gradient(90deg, #2563EB, #7C6AF7, #60A5FA);
      width: 0%; z-index: 9999;
      transition: width .05s linear;
    }

    /* ── HERO BLOBS ── */
    .hero { position: relative; overflow: hidden; }
    .hero-text, .hero-illustration { position: relative; z-index: 1; }
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: .45;
      pointer-events: none;
      z-index: 0;
    }
    @keyframes blobMove1 {
      0%,100% { transform: translate(0,0) scale(1); }
      33%      { transform: translate(40px,-30px) scale(1.08); }
      66%      { transform: translate(-20px,20px) scale(.95); }
    }
    @keyframes blobMove2 {
      0%,100% { transform: translate(0,0) scale(1); }
      33%      { transform: translate(-50px,25px) scale(1.05); }
      66%      { transform: translate(30px,-20px) scale(.97); }
    }
    .blob-1 {
      width: 380px; height: 380px;
      background: #BFDBFE;
      top: -80px; left: -60px;
      animation: blobMove1 9s ease-in-out infinite;
    }
    .blob-2 {
      width: 300px; height: 300px;
      background: #DDD6FE;
      bottom: -60px; right: 10%;
      animation: blobMove2 11s ease-in-out infinite;
    }
    .blob-3 {
      width: 200px; height: 200px;
      background: #BAE6FD;
      top: 40%; left: 45%;
      animation: blobMove1 13s ease-in-out infinite reverse;
    }

    /* ── STATS SECTION ── */
    .stats-bar {
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      background: var(--surface);
      padding: 36px 6%;
    }
    .stats-bar-inner {
      max-width: 900px; margin: 0 auto;
      display: flex; align-items: center; justify-content: space-around;
      gap: 24px; flex-wrap: wrap;
    }
    .stat-item { text-align: center; }
    .stat-item .num {
      font-size: clamp(1.8rem, 3vw, 2.4rem);
      font-weight: 800;
      letter-spacing: -0.04em;
      color: var(--dark);
      display: block;
      line-height: 1.1;
    }
    .stat-item .num span { color: var(--primary); }
    .stat-item .lbl {
      font-size: 0.82rem;
      color: var(--muted);
      margin-top: 4px;
      display: block;
    }
    .stat-divider {
      width: 1px; height: 40px;
      background: var(--border);
    }
    @media (max-width: 600px) { .stat-divider { display: none; } }

    /* ── TILT CARD ── */
    .flat-card {
      transform-style: preserve-3d;
      will-change: transform;
    }

    /* ── LANG FLAGS IN NAV ── */
    .nav-flags { display: flex; align-items: center; gap: 6px; margin-right: 4px; }
    .nav-flag {
      display: block; width: 28px; height: 19px;
      border-radius: 3px; overflow: hidden; text-decoration: none;
      transition: opacity .15s, box-shadow .15s;
    }
    .nav-flag svg { width: 100%; height: 100%; display: block; }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .hero { grid-template-columns: 1fr; gap: 40px; padding: 48px 5% 56px; }
      .hero-illustration { order: -1; }
      .flat-card { max-width: 100%; padding: 24px; }
      .hero h1 { font-size: 2rem; }
      nav { padding: 0 5%; }
      .features { padding: 56px 5%; }
      .cta-section { padding: 56px 5%; }
      footer { flex-direction: column; text-align: center; }
      .nav-flags { display: none; }
    }
  </style>
</head>
<body>

<div id="scroll-progress"></div>

<!-- ── NAV ── -->
<nav>
  <a href="<?= site_url('/') ?>" class="nav-logo">flat<span>sync</span></a>
  <div class="nav-links">
    <?php
    $_activeLang = $_locale;
    $_navFlags = [
      'es' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2"><rect width="3" height="2" fill="#AA151B"/><rect y=".5" width="3" height="1" fill="#F1BF00"/></svg>',
      'en' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 36"><rect width="60" height="36" fill="#012169"/><path d="M0,0 60,36M60,0 0,36" stroke="#fff" stroke-width="7.2"/><path d="M0,0 60,36M60,0 0,36" stroke="#C8102E" stroke-width="3.6"/><rect x="24" y="0" width="12" height="36" fill="#fff"/><rect x="0" y="12" width="60" height="12" fill="#fff"/><rect x="25.5" y="0" width="9" height="36" fill="#C8102E"/><rect x="0" y="13.5" width="60" height="9" fill="#C8102E"/></svg>',
      'ca' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6"><rect width="9" height="6" fill="#FCDD09"/><rect y=".667" width="9" height=".667" fill="#C60B1E"/><rect y="2" width="9" height=".667" fill="#C60B1E"/><rect y="3.333" width="9" height=".667" fill="#C60B1E"/><rect y="4.667" width="9" height=".667" fill="#C60B1E"/></svg>',
    ];
    $_flagTitles = ['es'=>'Español','en'=>'English','ca'=>'Català'];
    ?>
    <div class="nav-flags">
      <?php foreach ($_navFlags as $code => $svg): ?>
      <a href="<?= site_url('/lang/'.$code) ?>" class="nav-flag" title="<?= $_flagTitles[$code] ?>"
         style="opacity:<?= $_activeLang===$code ? '1' : '0.35' ?>;box-shadow:<?= $_activeLang===$code ? '0 0 0 2px #2563EB' : '0 0 0 1px #E2E8F0' ?>">
        <?= $svg ?>
      </a>
      <?php endforeach; ?>
    </div>
    <a href="<?= site_url('/login') ?>" class="btn-nav-login"><?= lang('App.landing_nav_login') ?></a>
    <a href="<?= site_url('/register') ?>" class="btn-nav-register"><?= lang('App.landing_nav_register') ?></a>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
  <div class="hero-text">
    <div class="hero-badge"><span class="badge-dot"></span> <?= lang('App.landing_badge') ?></div>
    <h1><?= lang('App.landing_h1_line1') ?><br><em id="typewriter-text"></em></h1>
    <p><?= lang('App.landing_hero_p') ?></p>
    <div class="hero-actions">
      <a href="<?= site_url('/register') ?>" class="btn-hero">
        <?= lang('App.landing_start') ?> <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
      </a>
      <a href="<?= site_url('/login') ?>" class="btn-hero-ghost"><?= lang('App.landing_login') ?></a>
    </div>
    <p class="hero-note"><?= lang('App.landing_note') ?></p>
  </div>

  <div class="hero-illustration">
    <div class="flat-card">
      <div class="flat-card-header">
        <span class="flat-card-title"><?= lang('App.landing_card_title') ?></span>
        <span class="flat-card-badge">Abril 2026</span>
      </div>
      <div class="flat-members">
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#2563EB">A</div>
            <div>
              <div class="flat-member-name">Álvaro</div>
              <div class="flat-member-sub"><?= lang('App.landing_paid_rent') ?></div>
            </div>
          </div>
          <span class="flat-member-amount amount-pos">+€124</span>
        </div>
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#7C3AED">M</div>
            <div>
              <div class="flat-member-name">María</div>
              <div class="flat-member-sub"><?= lang('App.landing_paid_shop') ?></div>
            </div>
          </div>
          <span class="flat-member-amount amount-pos">+€38</span>
        </div>
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#0891B2">J</div>
            <div>
              <div class="flat-member-name">Jorge</div>
              <div class="flat-member-sub"><?= lang('App.landing_owes') ?></div>
            </div>
          </div>
          <span class="flat-member-amount amount-neg">−€54</span>
        </div>
      </div>
      <div class="flat-total">
        <span class="flat-total-label"><?= lang('App.landing_total') ?></span>
        <span class="flat-total-value" id="counter">€0.00</span>
      </div>
    </div>
  </div>
</section>

<!-- ── STATS BAR ── -->
<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-item reveal">
      <span class="num" data-target="500">0<span>+</span></span>
      <span class="lbl"><?= lang('App.landing_stat_flats') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.1s">
      <span class="num" data-target="10000">0<span>+</span></span>
      <span class="lbl"><?= lang('App.landing_stat_expenses') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.2s">
      <span class="num" data-target="98">0<span>%</span></span>
      <span class="lbl"><?= lang('App.landing_stat_satisfaction') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.3s">
      <span class="num" data-target="2">0<span> min</span></span>
      <span class="lbl"><?= lang('App.landing_stat_start') ?></span>
    </div>
  </div>
</div>

<!-- ── FEATURES ── -->
<section class="features">
  <div class="features-inner">
    <p class="features-label reveal"><?= lang('App.landing_features_label') ?></p>
    <h2 class="reveal"><?= lang('App.landing_features_h2') ?></h2>
    <div class="features-grid">
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#EFF6FF"><i data-lucide="wallet" style="width:20px;height:20px;color:#2563EB"></i></div>
        <h3><?= lang('App.landing_f1_title') ?></h3>
        <p><?= lang('App.landing_f1_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#F0FDF4"><i data-lucide="calendar-check" style="width:20px;height:20px;color:#16A34A"></i></div>
        <h3><?= lang('App.landing_f2_title') ?></h3>
        <p><?= lang('App.landing_f2_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#FFF7ED"><i data-lucide="message-circle" style="width:20px;height:20px;color:#EA580C"></i></div>
        <h3><?= lang('App.landing_f3_title') ?></h3>
        <p><?= lang('App.landing_f3_desc') ?></p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#FDF4FF"><i data-lucide="wrench" style="width:20px;height:20px;color:#9333EA"></i></div>
        <h3><?= lang('App.landing_f4_title') ?></h3>
        <p><?= lang('App.landing_f4_desc') ?></p>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
  <h2 class="reveal" style="color:#fff"><?= lang('App.landing_cta_h2') ?></h2>
  <p class="reveal"><?= lang('App.landing_cta_p') ?></p>
  <a href="<?= site_url('/register') ?>" class="btn-cta"><?= lang('App.landing_cta_btn') ?> <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
</section>

<!-- ── FOOTER ── -->
<footer>
  <span class="logo">flat<span>sync</span></span>
  <p><?= lang('App.landing_footer') ?></p>
</footer>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
lucide.createIcons();

const _typewriterText = <?= json_encode($_typewriter) ?>;

// ── 1. Scroll progress bar ──
const progressBar = document.getElementById('scroll-progress');
window.addEventListener('scroll', () => {
  const max = document.documentElement.scrollHeight - window.innerHeight;
  progressBar.style.width = (window.scrollY / max * 100) + '%';
}, { passive: true });

// ── 2. Scroll-reveal ──
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('visible'); revealObserver.unobserve(e.target); }
  });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// ── 3. Typewriter ──
function typewriter(el, text, speed = 55) {
  let i = 0;
  el.textContent = '';
  const type = () => {
    if (i < text.length) { el.textContent += text[i++]; setTimeout(type, speed); }
  };
  setTimeout(type, 900);
}
typewriter(document.getElementById('typewriter-text'), _typewriterText);

// ── 4. Counter helper ──
function animateCount(el, target, duration, suffix) {
  const start = performance.now();
  const isFloat = String(target).includes('.');
  const update = (now) => {
    const p = Math.min((now - start) / duration, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    const val = target * ease;
    el.childNodes[0].textContent = isFloat ? val.toFixed(2) : Math.round(val).toLocaleString('es');
    if (p < 1) requestAnimationFrame(update);
    else el.childNodes[0].textContent = isFloat ? target.toFixed(2) : target.toLocaleString('es');
  };
  requestAnimationFrame(update);
}

// ── 5. Hero card counter (€487.50) ──
const heroCounter = document.getElementById('counter');
new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting) {
    const start = performance.now();
    const run = (now) => {
      const p = Math.min((now - start) / 1800, 1);
      const ease = 1 - Math.pow(1 - p, 3);
      heroCounter.textContent = '€' + (487.50 * ease).toFixed(2);
      if (p < 1) requestAnimationFrame(run);
      else heroCounter.textContent = '€487.50';
    };
    requestAnimationFrame(run);
  }
}, { threshold: 0.5 }).observe(heroCounter);

// ── 6. Stats bar counters ──
document.querySelectorAll('.stat-item .num').forEach(el => {
  const target = parseInt(el.dataset.target);
  new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      const duration = 1400;
      const start = performance.now();
      const run = (now) => {
        const p = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 3);
        const val = Math.round(target * ease);
        el.childNodes[0].textContent = val.toLocaleString('es');
        if (p < 1) requestAnimationFrame(run);
        else el.childNodes[0].textContent = target.toLocaleString('es');
      };
      requestAnimationFrame(run);
    }
  }, { threshold: 0.8 }).observe(el);
});

// ── 7. 3D tilt on hero card ──
const card = document.querySelector('.flat-card');
const illustration = document.querySelector('.hero-illustration');
card.addEventListener('mousemove', (e) => {
  const rect = card.getBoundingClientRect();
  const x = (e.clientX - rect.left) / rect.width  - 0.5;
  const y = (e.clientY - rect.top)  / rect.height - 0.5;
  illustration.style.animationPlayState = 'paused';
  card.style.transform = `perspective(800px) rotateY(${x * 18}deg) rotateX(${-y * 14}deg) scale(1.03)`;
  card.style.transition = 'transform .1s ease';
});
card.addEventListener('mouseleave', () => {
  card.style.transform = 'perspective(800px) rotateY(0deg) rotateX(0deg) scale(1)';
  card.style.transition = 'transform .6s ease';
  setTimeout(() => { illustration.style.animationPlayState = 'running'; }, 600);
});
</script>
</body>
</html>
