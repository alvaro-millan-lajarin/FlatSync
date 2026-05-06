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
<?php
$_metaDesc  = lang('App.landing_meta_desc');
$_ogTitle   = lang('App.landing_og_title');
$_canonical = rtrim(base_url(), '/') . '/';
$_ogImage   = base_url('assets/logo.png');
?>
<!DOCTYPE html>
<html lang="<?= $_locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= lang('App.landing_title') ?></title>
  <meta name="description" content="<?= esc($_metaDesc) ?>">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?= esc($_canonical) ?>">

  <!-- Favicon -->
  <link rel="icon" href="<?= base_url('favicon.ico') ?>" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/logo.png') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/logo.png') ?>">

  <!-- hreflang — misma URL sirve los tres idiomas según sesión -->
  <link rel="alternate" hreflang="es"      href="<?= esc($_canonical) ?>">
  <link rel="alternate" hreflang="en"      href="<?= esc($_canonical) ?>">
  <link rel="alternate" hreflang="ca"      href="<?= esc($_canonical) ?>">
  <link rel="alternate" hreflang="x-default" href="<?= esc($_canonical) ?>">

  <!-- Open Graph -->
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= esc($_canonical) ?>">
  <meta property="og:site_name"   content="FlatSync">
  <meta property="og:title"       content="<?= esc($_ogTitle) ?>">
  <meta property="og:description" content="<?= esc($_metaDesc) ?>">
  <meta property="og:image"       content="<?= esc($_ogImage) ?>">
  <meta property="og:locale"      content="<?= $_locale === 'en' ? 'en_GB' : ($_locale === 'ca' ? 'ca_ES' : 'es_ES') ?>">

  <!-- Twitter / X card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= esc($_ogTitle) ?>">
  <meta name="twitter:description" content="<?= esc($_metaDesc) ?>">
  <meta name="twitter:image"       content="<?= esc($_ogImage) ?>">

  <!-- Structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "FlatSync",
    "url": "<?= esc($_canonical) ?>",
    "description": "<?= esc($_metaDesc) ?>",
    "applicationCategory": "LifestyleApplication",
    "operatingSystem": "Web",
    "offers": {
      "@type": "Offer",
      "price": "0",
      "priceCurrency": "EUR"
    }
  }
  </script>

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
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid transparent;
      position: sticky;
      top: 0;
      z-index: 100;
      transition: border-color .3s, box-shadow .3s, background .3s;
    }
    nav.scrolled {
      border-color: var(--border);
      box-shadow: 0 2px 20px rgba(0,0,0,0.06);
      background: rgba(255,255,255,0.97);
    }
    .nav-logo {
      font-size: 1.35rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      color: var(--dark);
      text-decoration: none;
      transition: transform .2s;
    }
    .nav-logo:hover { transform: scale(1.04); }
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
      transition: background .15s, transform .15s;
    }
    .btn-nav-register:hover { background: var(--primary-d); transform: translateY(-1px); }

    /* ── HERO ── */
    .hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: 60px;
      padding: 80px 6% 90px;
      max-width: 1200px;
      margin: 0 auto;
      position: relative;
      overflow: hidden;
    }
    .hero-text, .hero-illustration { position: relative; z-index: 1; }
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
      opacity: 0;
      animation: fadeInUp .6s ease forwards .1s;
    }
    .hero h1 {
      font-size: clamp(2rem, 4vw, 3rem);
      font-weight: 800;
      line-height: 1.15;
      letter-spacing: -0.04em;
      color: var(--dark);
      margin-bottom: 20px;
      opacity: 0;
      animation: fadeInUp .6s ease forwards .25s;
    }
    .hero h1 em { font-style: normal; color: var(--primary); }
    .hero > .hero-text > p {
      font-size: 1.05rem;
      color: var(--muted);
      line-height: 1.7;
      max-width: 460px;
      margin-bottom: 32px;
      opacity: 0;
      animation: fadeInUp .6s ease forwards .4s;
    }
    .hero-actions {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
      opacity: 0;
      animation: fadeInUp .6s ease forwards .55s;
    }
    .btn-hero {
      padding: 14px 32px;
      border-radius: 10px;
      background: var(--primary);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      text-decoration: none;
      transition: background .15s, transform .15s, box-shadow .15s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(37,99,235,0.35);
    }
    .btn-hero:hover { background: var(--primary-d); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(37,99,235,0.45); }
    .btn-hero-ghost {
      padding: 14px 24px;
      border-radius: 10px;
      border: 1.5px solid var(--border);
      color: var(--text);
      font-size: 1rem;
      font-weight: 600;
      text-decoration: none;
      transition: border-color .15s, color .15s, transform .15s;
    }
    .btn-hero-ghost:hover { border-color: var(--primary); color: var(--primary); transform: translateY(-1px); }
    .hero-note {
      margin-top: 16px;
      font-size: 0.8rem;
      color: var(--muted);
      opacity: 0;
      animation: fadeInUp .6s ease forwards .65s;
    }

    /* ── HERO ILLUSTRATION ── */
    .hero-illustration {
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      animation: fadeInRight .7s ease forwards .3s, float 5s ease-in-out 1s infinite;
      animation-fill-mode: forwards;
    }
    .flat-card {
      background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
      border-radius: 24px;
      padding: 36px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 20px 60px rgba(37,99,235,0.15);
      position: relative;
      overflow: hidden;
      transform-style: preserve-3d;
      will-change: transform;
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
      animation: highlight 3.5s ease infinite;
    }
    .flat-member:nth-child(2) { animation-delay: 1.2s; }
    .flat-member:nth-child(3) { animation-delay: 2.4s; }
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

    /* ── STATS BAR ── */
    .stats-bar {
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      background: var(--surface);
      padding: 36px 6%;
      overflow: hidden;
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
    .stat-divider { width: 1px; height: 40px; background: var(--border); }
    @media (max-width: 600px) { .stat-divider { display: none; } }

    /* ── FEATURES ── */
    .features {
      background: var(--surface);
      padding: 80px 6%;
      border-top: 1px solid var(--border);
      overflow: hidden;
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
      transition: box-shadow .3s, transform .3s, border-color .3s;
      position: relative;
      overflow: hidden;
    }
    .feature-card::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(37,99,235,0.04) 0%, transparent 60%);
      opacity: 0;
      transition: opacity .3s;
    }
    .feature-card:hover { box-shadow: 0 12px 36px rgba(0,0,0,0.1); transform: translateY(-4px); border-color: rgba(37,99,235,0.2); }
    .feature-card:hover::after { opacity: 1; }
    .feature-icon {
      width: 44px; height: 44px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 16px;
      transition: transform .3s;
    }
    .feature-card:hover .feature-icon { transform: scale(1.1) rotate(-4deg); }
    .feature-card h3 { font-size: 1rem; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .feature-card p  { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

    /* ── CTA BOTTOM ── */
    .cta-section {
      background: var(--dark);
      padding: 80px 6%;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cta-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 70% 60% at 50% 100%, rgba(37,99,235,0.35) 0%, transparent 70%);
      pointer-events: none;
    }
    .cta-section > * { position: relative; z-index: 1; }
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
      transition: background .15s, transform .15s, box-shadow .15s;
      box-shadow: 0 4px 20px rgba(37,99,235,0.5);
    }
    .btn-cta:hover { background: var(--primary-d); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(37,99,235,0.6); }

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
      from { opacity: 0; transform: translateX(40px); }
      to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-12px); }
    }
    @keyframes highlight {
      0%   { background: #fff; }
      40%  { background: #EFF6FF; }
      100% { background: #fff; }
    }
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

    /* ── SCROLL PROGRESS BAR ── */
    #scroll-progress {
      position: fixed; top: 0; left: 0; height: 3px;
      background: linear-gradient(90deg, #2563EB, #7C6AF7, #60A5FA);
      width: 0%; z-index: 9999;
      transition: width .05s linear;
    }

    /* ── HERO BLOBS ── */
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(60px);
      opacity: .45;
      pointer-events: none;
      z-index: 0;
      will-change: transform;
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
    .blob-1 { width: 380px; height: 380px; background: #BFDBFE; top: -80px; left: -60px; animation: blobMove1 9s ease-in-out infinite; }
    .blob-2 { width: 300px; height: 300px; background: #DDD6FE; bottom: -60px; right: 10%; animation: blobMove2 11s ease-in-out infinite; }
    .blob-3 { width: 200px; height: 200px; background: #BAE6FD; top: 40%; left: 45%; animation: blobMove1 13s ease-in-out infinite reverse; }

    /* ── REVEAL ANIMATIONS ── */
    .reveal {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity .65s cubic-bezier(.16,1,.3,1), transform .65s cubic-bezier(.16,1,.3,1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }

    /* Feature cards — slide from sides */
    .feature-card.reveal { transform: translateY(40px) scale(0.97); }
    .feature-card.reveal.visible { transform: translateY(0) scale(1); }
    .feature-card:nth-child(1) { transition-delay: .05s; }
    .feature-card:nth-child(2) { transition-delay: .15s; }
    .feature-card:nth-child(3) { transition-delay: .25s; }
    .feature-card:nth-child(4) { transition-delay: .35s; }

    /* Stat items pop in */
    .stat-item.reveal { transform: translateY(20px) scale(0.95); }
    .stat-item.reveal.visible { transform: translateY(0) scale(1); }

    /* ── CTA FLOATING PARTICLES ── */
    .cta-particle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      pointer-events: none;
      animation: particleFloat linear infinite;
    }
    @keyframes particleFloat {
      0%   { transform: translateY(0) rotate(0deg); opacity: 0; }
      10%  { opacity: 1; }
      90%  { opacity: 1; }
      100% { transform: translateY(-300px) rotate(360deg); opacity: 0; }
    }

    /* ── LANG FLAGS NAV ── */
    .nav-flags { display: flex; align-items: center; gap: 6px; margin-right: 4px; }
    .nav-flag {
      display: block; width: 28px; height: 19px;
      border-radius: 3px; overflow: hidden; text-decoration: none;
      transition: opacity .15s, box-shadow .15s, transform .15s;
    }
    .nav-flag:hover { transform: scale(1.1); }
    .nav-flag svg { width: 100%; height: 100%; display: block; }

    /* ── MARQUEE STRIP ── */
    .marquee-wrap {
      overflow: hidden;
      padding: 13px 0;
      background: #fff;
      border-bottom: 1px solid var(--border);
      position: relative;
    }
    .marquee-wrap::before,
    .marquee-wrap::after {
      content: '';
      position: absolute;
      top: 0; bottom: 0;
      width: 80px;
      z-index: 1;
      pointer-events: none;
    }
    .marquee-wrap::before { left: 0;  background: linear-gradient(to right, #fff, transparent); }
    .marquee-wrap::after  { right: 0; background: linear-gradient(to left,  #fff, transparent); }
    .marquee-inner {
      display: flex;
      animation: marqueeScroll 30s linear infinite;
      white-space: nowrap;
      will-change: transform;
    }
    .marquee-wrap:hover .marquee-inner { animation-play-state: paused; }
    .marquee-item {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 5px 26px;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--muted);
      border-right: 1px solid var(--border);
      flex-shrink: 0;
    }
    .marquee-item i { width: 14px; height: 14px; color: var(--primary); flex-shrink: 0; }
    @keyframes marqueeScroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

    /* ── HOW IT WORKS ── */
    .how-section {
      padding: 90px 6%;
      background: var(--surface);
      border-top: 1px solid var(--border);
      overflow: hidden;
    }
    .how-inner { max-width: 1060px; margin: 0 auto; }
    .how-h2 {
      font-size: clamp(1.6rem, 3vw, 2.2rem);
      font-weight: 800;
      letter-spacing: -0.035em;
      color: var(--dark);
      margin-bottom: 72px;
      text-align: center;
    }
    .how-steps { display: flex; flex-direction: column; gap: 68px; }
    .how-row {
      display: grid;
      grid-template-columns: 56px 1fr 1.1fr;
      align-items: center;
      gap: 44px;
    }
    .how-row-flip { grid-template-columns: 1.1fr 1fr 56px; }
    .how-row-flip .how-visual { order: -1; }
    .how-row-flip .how-num    { order: 3; text-align: right; }
    .how-num {
      font-size: 3.4rem;
      font-weight: 900;
      color: var(--border);
      letter-spacing: -0.05em;
      line-height: 1;
      user-select: none;
    }
    .how-text h3 { font-size: 1.25rem; font-weight: 700; color: var(--dark); margin-bottom: 10px; }
    .how-text p  { font-size: 0.93rem; color: var(--muted); line-height: 1.75; max-width: 380px; }
    .how-visual {
      border-radius: 18px;
      padding: 24px;
      min-height: 168px;
      box-shadow: 0 6px 28px rgba(0,0,0,0.08);
      display: flex;
      flex-direction: column;
      gap: 10px;
      position: relative;
      overflow: hidden;
    }
    .how-visual-1 { background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); }
    .how-visual-2 { background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%); }
    .how-visual-3 { background: linear-gradient(135deg, #FDF4FF 0%, #EDE9FE 100%); }

    /* visual 1 — home creation */
    .hv-home-header { display: flex; align-items: center; gap: 10px; }
    .hv-home-icon { width: 36px; height: 36px; border-radius: 9px; background: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .hv-home-name { font-size: 0.85rem; font-weight: 700; color: var(--dark); }
    .hv-code-chip { display: inline-block; background: rgba(37,99,235,0.12); color: #2563EB; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; font-family: monospace; letter-spacing: .05em; }
    .hv-avatars { display: flex; }
    .hv-avt { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; color: #fff; border: 2px solid #fff; margin-left: -5px; }
    .hv-avt:first-child { margin-left: 0; }

    /* visual 2 — expenses */
    .hv-exp-row { display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.72); border-radius: 9px; padding: 8px 11px; gap: 8px; }
    .hv-exp-left { display: flex; align-items: center; gap: 7px; font-size: 0.8rem; font-weight: 600; color: var(--dark); }
    .hv-exp-right { font-size: 0.8rem; font-weight: 700; color: #16A34A; }
    .hv-exp-split { font-size: 0.68rem; color: var(--muted); margin-left: 3px; }

    /* visual 3 — sync */
    .hv-sync-row { display: flex; align-items: center; gap: 9px; background: rgba(255,255,255,0.72); border-radius: 9px; padding: 9px 12px; }
    .hv-sync-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .hv-sync-text { font-size: 0.8rem; font-weight: 600; color: var(--dark); }

    /* ── SIDE REVEAL ── */
    [data-reveal="left"]  { opacity: 0; transform: translateX(-52px); transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    [data-reveal="right"] { opacity: 0; transform: translateX(52px);  transition: opacity .75s cubic-bezier(.16,1,.3,1), transform .75s cubic-bezier(.16,1,.3,1); }
    [data-reveal].reveal-in { opacity: 1; transform: translateX(0); }

    /* ── SPLIT WORD ── */
    .sw-outer { display: inline-block; overflow: hidden; vertical-align: bottom; }
    .sw-inner { display: inline-block; transform: translateY(115%); transition: transform .65s cubic-bezier(.16,1,.3,1); }
    .sw-inner.sw-up { transform: translateY(0); }

    /* ── HOW SECTION RESPONSIVE ── */
    @media (max-width: 860px) {
      .how-row, .how-row-flip {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      .how-num { display: none; }
      .how-row-flip .how-visual { order: 0; }
    }

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
<nav id="main-nav">
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
    <a href="<?= site_url('/demo') ?>" class="btn-nav-login" style="border-color:rgba(124,106,247,0.5);color:#7C6AF7" onmouseover="this.style.borderColor='#7C6AF7';this.style.color='#7C6AF7'" onmouseout="this.style.borderColor='rgba(124,106,247,0.5)';this.style.color='#7C6AF7'"><?= lang('App.landing_demo') ?></a>
    <a href="<?= site_url('/login') ?>" class="btn-nav-login"><?= lang('App.landing_nav_login') ?></a>
    <a href="<?= site_url('/register') ?>" class="btn-nav-register"><?= lang('App.landing_nav_register') ?></a>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero" id="hero">
  <div class="blob blob-1" id="blob1"></div>
  <div class="blob blob-2" id="blob2"></div>
  <div class="blob blob-3" id="blob3"></div>
  <div class="hero-text">
    <div class="hero-badge"><span class="badge-dot"></span> <?= lang('App.landing_badge') ?></div>
    <h1><?= lang('App.landing_h1_line1') ?><br><em id="typewriter-text"></em></h1>
    <p><?= lang('App.landing_hero_p') ?></p>
    <div class="hero-actions">
      <a href="<?= site_url('/register') ?>" class="btn-hero">
        <?= lang('App.landing_start') ?> <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
      </a>
      <a href="<?= site_url('/demo') ?>" class="btn-hero-ghost" style="border-color:rgba(124,106,247,0.5);color:#7C6AF7;display:inline-flex;align-items:center;gap:8px">
        <i data-lucide="play-circle" style="width:16px;height:16px"></i> <?= lang('App.landing_demo') ?>
      </a>
    </div>
    <p class="hero-note"><?= lang('App.landing_note') ?></p>
  </div>

  <div class="hero-illustration" id="hero-illus">
    <div class="flat-card" id="flat-card">
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

<!-- ── MARQUEE ── -->
<?php
$_mqItems = [
  ['wallet',         lang('App.landing_f1_title')],
  ['calendar-check', lang('App.landing_f2_title')],
  ['message-circle', lang('App.landing_f3_title')],
  ['wrench',         lang('App.landing_f4_title')],
  ['shield-check',   lang('App.landing_mq_free')],
  ['zap',            lang('App.landing_mq_quick')],
];
?>
<div class="marquee-wrap" aria-hidden="true">
  <div class="marquee-inner">
    <?php for ($__r = 0; $__r < 2; $__r++): foreach ($_mqItems as $_mq): ?>
    <span class="marquee-item">
      <i data-lucide="<?= $_mq[0] ?>"></i>
      <?= esc($_mq[1]) ?>
    </span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- ── STATS BAR ── -->
<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-item reveal">
      <span class="num" data-target="500">0<span>+</span></span>
      <span class="lbl"><?= lang('App.landing_stat_flats') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.12s">
      <span class="num" data-target="10000">0<span>+</span></span>
      <span class="lbl"><?= lang('App.landing_stat_expenses') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.24s">
      <span class="num" data-target="98">0<span>%</span></span>
      <span class="lbl"><?= lang('App.landing_stat_satisfaction') ?></span>
    </div>
    <div class="stat-divider"></div>
    <div class="stat-item reveal" style="transition-delay:.36s">
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

<!-- ── HOW IT WORKS ── -->
<section class="how-section">
  <div class="how-inner">
    <p class="features-label reveal" style="text-align:center;margin-bottom:12px"><?= lang('App.landing_how_label') ?></p>
    <h2 class="how-h2" data-split-words><?= lang('App.landing_how_h2') ?></h2>
    <div class="how-steps">

      <!-- Step 1 -->
      <div class="how-row" data-reveal="left">
        <div class="how-num">01</div>
        <div class="how-text">
          <h3><?= lang('App.landing_how_s1_title') ?></h3>
          <p><?= lang('App.landing_how_s1_desc') ?></p>
        </div>
        <div class="how-visual how-visual-1">
          <div class="hv-home-header">
            <div class="hv-home-icon"><i data-lucide="home" style="width:18px;height:18px;color:#fff"></i></div>
            <div class="hv-home-name">Mi Piso · Barcelona</div>
          </div>
          <div><span class="hv-code-chip">&#128273;&nbsp;FLAT-XK9</span></div>
          <div class="hv-avatars">
            <div class="hv-avt" style="background:#2563EB">A</div>
            <div class="hv-avt" style="background:#7C3AED">M</div>
            <div class="hv-avt" style="background:#0891B2">J</div>
            <div class="hv-avt" style="background:#E2E8F0;color:#94A3B8">+</div>
          </div>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="how-row how-row-flip" data-reveal="right">
        <div class="how-num">02</div>
        <div class="how-text">
          <h3><?= lang('App.landing_how_s2_title') ?></h3>
          <p><?= lang('App.landing_how_s2_desc') ?></p>
        </div>
        <div class="how-visual how-visual-2">
          <div class="hv-exp-row">
            <div class="hv-exp-left"><i data-lucide="shopping-cart" style="width:13px;height:13px;color:#16A34A"></i> Mercadona</div>
            <div class="hv-exp-right">€48.50 <span class="hv-exp-split">÷3</span></div>
          </div>
          <div class="hv-exp-row">
            <div class="hv-exp-left"><i data-lucide="zap" style="width:13px;height:13px;color:#EA580C"></i> Electricidad</div>
            <div class="hv-exp-right">€32.00 <span class="hv-exp-split">÷3</span></div>
          </div>
          <div class="hv-exp-row" style="background:rgba(22,163,74,0.14);margin-top:4px">
            <div class="hv-exp-left" style="color:var(--muted);font-size:.75rem">Balance total</div>
            <div class="hv-exp-right" style="font-size:.9rem">€80.50</div>
          </div>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="how-row" data-reveal="left">
        <div class="how-num">03</div>
        <div class="how-text">
          <h3><?= lang('App.landing_how_s3_title') ?></h3>
          <p><?= lang('App.landing_how_s3_desc') ?></p>
        </div>
        <div class="how-visual how-visual-3">
          <div class="hv-sync-row">
            <div class="hv-sync-dot" style="background:#16A34A"></div>
            <i data-lucide="calendar-check" style="width:13px;height:13px;color:#9333EA"></i>
            <div class="hv-sync-text"><?= lang('App.landing_f2_title') ?> ✓</div>
          </div>
          <div class="hv-sync-row">
            <div class="hv-sync-dot" style="background:#2563EB"></div>
            <i data-lucide="message-circle" style="width:13px;height:13px;color:#9333EA"></i>
            <div class="hv-sync-text">«¿Traes la compra?»</div>
          </div>
          <div class="hv-sync-row">
            <div class="hv-sync-dot" style="background:#EA580C"></div>
            <i data-lucide="map-pin" style="width:13px;height:13px;color:#9333EA"></i>
            <div class="hv-sync-text"><?= lang('App.landing_f4_title') ?></div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section" id="cta-section">
  <h2 class="reveal"><?= lang('App.landing_cta_h2') ?></h2>
  <p class="reveal"><?= lang('App.landing_cta_p') ?></p>
  <div class="reveal" style="display:inline-flex;gap:14px;flex-wrap:wrap;justify-content:center">
    <a href="<?= site_url('/register') ?>" class="btn-cta"><?= lang('App.landing_cta_btn') ?> <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
    <a href="<?= site_url('/demo') ?>" class="btn-cta" style="background:rgba(255,255,255,0.12);box-shadow:none;border:1.5px solid rgba(255,255,255,0.25)"><i data-lucide="play-circle" style="width:16px;height:16px"></i> <?= lang('App.landing_demo') ?></a>
  </div>
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

// ── 2. Nav scrolled state ──
const mainNav = document.getElementById('main-nav');

window.addEventListener('scroll', () => {
  const sy = window.scrollY;
  const max = document.documentElement.scrollHeight - window.innerHeight;
  progressBar.style.width = (sy / max * 100) + '%';
  mainNav.classList.toggle('scrolled', sy > 20);

  // Parallax blobs on scroll
  const pct = Math.min(sy / 600, 1);
  const b1 = document.getElementById('blob1');
  const b2 = document.getElementById('blob2');
  const b3 = document.getElementById('blob3');
  if (b1) b1.style.transform = `translateY(${sy * 0.18}px)`;
  if (b2) b2.style.transform = `translateY(${-sy * 0.12}px)`;
  if (b3) b3.style.transform = `translateY(${sy * 0.08}px)`;

  // Hero text parallax
  const heroText = document.querySelector('.hero-text');
  if (heroText) heroText.style.transform = `translateY(${sy * 0.1}px)`;
}, { passive: true });

// ── 3. IntersectionObserver reveal (enhanced) ──
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      io.unobserve(e.target);
    }
  });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// ── 4. Typewriter ──
function typewriter(el, text, speed = 55) {
  let i = 0;
  el.textContent = '';
  const cursor = document.createElement('span');
  cursor.style.cssText = 'display:inline-block;width:2px;height:1em;background:currentColor;margin-left:2px;vertical-align:middle;animation:blink .7s step-end infinite';
  el.parentNode.appendChild(cursor);
  const style = document.createElement('style');
  style.textContent = '@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}';
  document.head.appendChild(style);
  const type = () => {
    if (i < text.length) {
      el.textContent += text[i++];
      setTimeout(type, speed);
    } else {
      setTimeout(() => cursor.remove(), 1200);
    }
  };
  setTimeout(type, 900);
}
typewriter(document.getElementById('typewriter-text'), _typewriterText);

// ── 5. Hero card counter ──
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

// ── 6. Stats bar counters with easing ──
const countedNums = new Set();
document.querySelectorAll('.stat-item .num').forEach(el => {
  const target = parseInt(el.dataset.target);
  new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !countedNums.has(el)) {
      countedNums.add(el);
      const duration = 1600;
      const start = performance.now();
      const run = (now) => {
        const p = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - p, 4);
        const val = Math.round(target * ease);
        el.childNodes[0].textContent = val.toLocaleString();
        if (p < 1) requestAnimationFrame(run);
        else el.childNodes[0].textContent = target.toLocaleString();
      };
      requestAnimationFrame(run);
    }
  }, { threshold: 0.8 }).observe(el);
});

// ── 7. 3D tilt on hero card (mouse) ──
const card = document.getElementById('flat-card');
const illus = document.getElementById('hero-illus');
if (card && illus) {
  card.addEventListener('mousemove', (e) => {
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width  - 0.5;
    const y = (e.clientY - r.top)  / r.height - 0.5;
    illus.style.animationPlayState = 'paused';
    card.style.transform = `perspective(900px) rotateY(${x * 16}deg) rotateX(${-y * 12}deg) scale(1.04)`;
    card.style.transition = 'transform .08s ease';
    // Dynamic shadow based on tilt
    card.style.boxShadow = `${-x * 20}px ${y * 20 + 20}px 60px rgba(37,99,235,${0.12 + Math.abs(x) * 0.08})`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = 'perspective(900px) rotateY(0deg) rotateX(0deg) scale(1)';
    card.style.transition = 'transform .6s ease, box-shadow .6s ease';
    card.style.boxShadow = '0 20px 60px rgba(37,99,235,0.15)';
    setTimeout(() => { illus.style.animationPlayState = 'running'; }, 600);
  });
}

// ── 8. CTA floating particles ──
const cta = document.getElementById('cta-section');
if (cta) {
  for (let i = 0; i < 12; i++) {
    const p = document.createElement('div');
    p.className = 'cta-particle';
    const size = Math.random() * 40 + 10;
    p.style.cssText = `
      width:${size}px; height:${size}px;
      left:${Math.random() * 100}%;
      bottom:${Math.random() * -40}px;
      animation-duration:${Math.random() * 8 + 6}s;
      animation-delay:${Math.random() * 6}s;
    `;
    cta.appendChild(p);
  }
}

// ── 9. Feature cards — magnetic hover micro-effect ──
document.querySelectorAll('.feature-card').forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left - r.width  / 2) / (r.width  / 2);
    const y = (e.clientY - r.top  - r.height / 2) / (r.height / 2);
    card.style.transform = `translateY(-4px) rotateX(${-y * 3}deg) rotateY(${x * 3}deg)`;
    card.style.transition = 'transform .1s ease';
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = 'translateY(0) rotateX(0deg) rotateY(0deg)';
    card.style.transition = 'transform .4s ease, box-shadow .3s, border-color .3s';
  });
  card.style.transformStyle = 'preserve-3d';
});

// ── 10. Split word animation for [data-split-words] headings ──
document.querySelectorAll('[data-split-words]').forEach(el => {
  const words = el.innerText.trim().split(' ');
  el.innerHTML = words.map(w => `<span class="sw-outer"><span class="sw-inner">${w}&nbsp;</span></span>`).join('');
  new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) {
      el.querySelectorAll('.sw-inner').forEach((s, i) => {
        setTimeout(() => s.classList.add('sw-up'), i * 85);
      });
    }
  }, { threshold: 0.4 }).observe(el);
});

// ── 11. Side reveal for how-section rows ──
const sideIo = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('reveal-in');
      sideIo.unobserve(e.target);
    }
  });
}, { threshold: 0.15 });
document.querySelectorAll('[data-reveal]').forEach(el => sideIo.observe(el));

// ── 13. Scroll-triggered section bg color shift on stats bar ──
const statSection = document.querySelector('.stats-bar');
if (statSection) {
  new IntersectionObserver((entries) => {
    entries[0].target.style.transition = 'background .6s ease';
    if (entries[0].isIntersecting) {
      entries[0].target.style.background = '#EFF6FF';
      setTimeout(() => { entries[0].target.style.background = 'var(--surface)'; }, 1200);
    }
  }, { threshold: 0.5 }).observe(statSection);
}
</script>
</body>
</html>
