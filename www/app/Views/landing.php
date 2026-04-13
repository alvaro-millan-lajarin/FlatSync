<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FlatSync — Gestiona tu piso compartido</title>
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

    /* Entrance keyframes */
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

    /* Floating card */
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-10px); }
    }
    .hero-illustration { animation: float 5s ease-in-out infinite; }

    /* Hero text — staggered entrance */
    .hero-badge  { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .1s; }
    .hero h1     { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .25s; }
    .hero > .hero-text > p { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .4s; }
    .hero-actions { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .55s; }
    .hero-note   { opacity: 0; animation: fadeInUp .6s ease forwards; animation-delay: .65s; }
    .hero-illustration { opacity: 0; animation: fadeInRight .7s ease forwards, float 5s ease-in-out 0.7s infinite; animation-fill-mode: forwards; }

    /* Scroll-reveal base */
    .reveal {
      opacity: 0;
      transform: translateY(24px);
      transition: opacity .55s ease, transform .55s ease;
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Staggered delay for feature cards */
    .feature-card:nth-child(1) { transition-delay: .0s; }
    .feature-card:nth-child(2) { transition-delay: .1s; }
    .feature-card:nth-child(3) { transition-delay: .2s; }
    .feature-card:nth-child(4) { transition-delay: .3s; }

    /* Pulsing badge dot */
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

    /* Member row highlight sweep */
    @keyframes highlight {
      0%   { background: #fff; }
      40%  { background: #EFF6FF; }
      100% { background: #fff; }
    }
    .flat-member { animation: highlight 3.5s ease infinite; }
    .flat-member:nth-child(2) { animation-delay: 1.2s; }
    .flat-member:nth-child(3) { animation-delay: 2.4s; }

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
    }
  </style>
</head>
<body>

<!-- ── NAV ── -->
<nav>
  <a href="<?= site_url('/') ?>" class="nav-logo">flat<span>sync</span></a>
  <div class="nav-links">
    <a href="<?= site_url('/login') ?>" class="btn-nav-login">Iniciar sesión</a>
    <a href="<?= site_url('/register') ?>" class="btn-nav-register">Registrarse</a>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-text">
    <div class="hero-badge"><span class="badge-dot"></span> Gestión de piso compartido</div>
    <h1>Vivir juntos,<br><em>sin complicaciones.</em></h1>
    <p>Controla gastos, organiza tareas, chatea con tus compañeros y encuentra servicios cercanos, todo desde un mismo sitio.</p>
    <div class="hero-actions">
      <a href="<?= site_url('/register') ?>" class="btn-hero">
        Empezar gratis <i data-lucide="arrow-right" style="width:16px;height:16px"></i>
      </a>
      <a href="<?= site_url('/login') ?>" class="btn-hero-ghost">Iniciar sesión</a>
    </div>
    <p class="hero-note">Gratis · Sin tarjeta de crédito · Listo en 2 minutos</p>
  </div>

  <div class="hero-illustration">
    <div class="flat-card">
      <div class="flat-card-header">
        <span class="flat-card-title">Piso Barcelona</span>
        <span class="flat-card-badge">Abril 2026</span>
      </div>
      <div class="flat-members">
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#2563EB">A</div>
            <div>
              <div class="flat-member-name">Álvaro</div>
              <div class="flat-member-sub">Pagó alquiler + luz</div>
            </div>
          </div>
          <span class="flat-member-amount amount-pos">+€124</span>
        </div>
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#7C3AED">M</div>
            <div>
              <div class="flat-member-name">María</div>
              <div class="flat-member-sub">Pagó compra semana</div>
            </div>
          </div>
          <span class="flat-member-amount amount-pos">+€38</span>
        </div>
        <div class="flat-member">
          <div class="flat-member-left">
            <div class="avatar" style="background:#0891B2">J</div>
            <div>
              <div class="flat-member-name">Jorge</div>
              <div class="flat-member-sub">Debe su parte</div>
            </div>
          </div>
          <span class="flat-member-amount amount-neg">−€54</span>
        </div>
      </div>
      <div class="flat-total">
        <span class="flat-total-label">Total este mes</span>
        <span class="flat-total-value" id="counter">€0.00</span>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURES ── -->
<section class="features">
  <div class="features-inner">
    <p class="features-label reveal">Todo lo que necesitas</p>
    <h2 class="reveal">Una app para gestionar<br>todo el piso</h2>
    <div class="features-grid">
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#EFF6FF"><i data-lucide="wallet" style="width:20px;height:20px;color:#2563EB"></i></div>
        <h3>Gastos compartidos</h3>
        <p>Registra quién pagó qué, divide automáticamente y salda deudas con un clic.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#F0FDF4"><i data-lucide="calendar-check" style="width:20px;height:20px;color:#16A34A"></i></div>
        <h3>Tareas del hogar</h3>
        <p>Asigna y rota las tareas entre compañeros. Multas automáticas si alguien falla.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#FFF7ED"><i data-lucide="message-circle" style="width:20px;height:20px;color:#EA580C"></i></div>
        <h3>Chat y notas</h3>
        <p>Comunícate con todos en tiempo real y deja notas importantes para el piso.</p>
      </div>
      <div class="feature-card reveal">
        <div class="feature-icon" style="background:#FDF4FF"><i data-lucide="wrench" style="width:20px;height:20px;color:#9333EA"></i></div>
        <h3>Servicios cercanos</h3>
        <p>Encuentra fontaneros, electricistas y limpiadores cerca de tu hogar al instante.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
  <h2 class="reveal" style="color:#fff">¿Listo para<br>simplificar tu piso?</h2>
  <p class="reveal">Únete en menos de 2 minutos. Es completamente gratis.</p>
  <a href="<?= site_url('/register') ?>" class="btn-cta">Crear mi hogar <i data-lucide="arrow-right" style="width:16px;height:16px"></i></a>
</section>

<!-- ── FOOTER ── -->
<footer>
  <span class="logo">flat<span>sync</span></span>
  <p>Hecho para compañeros de piso</p>
</footer>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>
lucide.createIcons();

// ── Scroll-reveal ──
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.classList.add('visible');
      observer.unobserve(e.target);
    }
  });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// ── Counter animation ──
function animateCounter(el, target, duration) {
  const start = performance.now();
  const update = (now) => {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);
    const ease = 1 - Math.pow(1 - progress, 3); // ease-out cubic
    el.textContent = '€' + (target * ease).toFixed(2);
    if (progress < 1) requestAnimationFrame(update);
    else el.textContent = '€' + target.toFixed(2);
  };
  requestAnimationFrame(update);
}

const counterEl = document.getElementById('counter');
const counterObserver = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting) {
    animateCounter(counterEl, 487.50, 1800);
    counterObserver.unobserve(counterEl);
  }
}, { threshold: 0.5 });
counterObserver.observe(counterEl);
</script>
</body>
</html>
