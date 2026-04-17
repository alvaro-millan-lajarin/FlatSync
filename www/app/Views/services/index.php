<?= view('layouts/header') ?>

<!-- Barra superior: búsqueda + ubicación -->
<div class="card" style="margin-bottom:24px">
  <div style="display:flex;flex-direction:column;gap:12px">
    <div style="position:relative">
      <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--muted)"></i>
      <input type="text" id="service-search" placeholder="Buscar categoría…"
             style="width:100%;padding:9px 12px 9px 36px;border:1px solid var(--border);border-radius:8px;font-size:0.875rem;font-family:inherit;background:var(--surface2);color:var(--text);outline:none;box-sizing:border-box"
             oninput="filterCategories(this.value)">
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:var(--muted);min-width:0;flex:1">
        <i data-lucide="map-pin" style="width:14px;height:14px;color:var(--primary);flex-shrink:0"></i>
        <span id="location-label" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Sin ubicación</span>
      </div>
      <button id="btn-locate" onclick="loadAll()" class="btn btn-primary" style="display:flex;align-items:center;gap:6px;flex-shrink:0">
        <i data-lucide="locate" style="width:14px;height:14px"></i> Buscar lugares cercanos
      </button>
    </div>
  </div>
</div>

<!-- Categorías (rellenado por JS) -->
<div id="services-container"></div>

<!-- Estado global (inicial / error) -->
<div id="state-msg">
  <div class="empty-state" style="padding:60px 0">
    <div id="state-icon"><i data-lucide="map-pin" style="width:40px;height:40px;color:var(--primary)"></i></div>
    <h3 id="state-title">Encuentra servicios cercanos</h3>
    <p id="state-body">Pulsa el botón de arriba para buscar supermercados, farmacias, fontaneros y más cerca de tu hogar.</p>
  </div>
</div>

<?php
$cats = array_map(fn($c) => [
    'key'   => $c['key'],
    'label' => $c['label'],
    'icon'  => $c['icon'],
    'color' => $c['color'],
], $categories);
?>
<script>
const CATEGORIES = <?= json_encode($cats, JSON_UNESCAPED_UNICODE) ?>;
const NEARBY_URL  = '<?= site_url('/services/nearby') ?>';

const COLOR_MAP = {
  accent:  { bg: 'rgba(37,99,235,0.08)',  text: 'var(--primary)',  star: '#2563EB' },
  warning: { bg: 'rgba(245,158,11,0.08)', text: 'var(--warning)', star: '#F59E0B' },
  success: { bg: 'rgba(34,197,94,0.08)',  text: 'var(--success)', star: '#22C55E' },
  danger:  { bg: 'rgba(239,68,68,0.08)',  text: 'var(--danger)',  star: '#EF4444' },
};

let userLat = null, userLng = null;

/* ── Arranque ───────────────────────────────────────────────── */
function loadAll() {
  // Contexto inseguro (HTTP fuera de localhost): el navegador bloquea la geolocalización sin avisar
  if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
    showState('error', 'Conexión no segura',
      'La geolocalización requiere HTTPS. Accede a la app mediante una conexión segura (https://) para usar esta función.');
    return;
  }
  if (!navigator.geolocation) {
    showState('error', 'Geolocalización no disponible', 'Tu navegador no soporta geolocalización.');
    return;
  }
  const btn = document.getElementById('btn-locate');
  if (btn) { btn.disabled = true; btn.innerHTML = '<i data-lucide="loader-2" style="width:14px;height:14px"></i> Buscando…'; if (window.lucide) lucide.createIcons(btn); }
  showState('loader', 'Solicitando permiso de ubicación…', 'Acepta el permiso que aparece en tu dispositivo.');
  navigator.geolocation.getCurrentPosition(onGeo, onGeoError, { timeout: 15000, enableHighAccuracy: false });
}

function resetBtn() {
  const btn = document.getElementById('btn-locate');
  if (btn) { btn.disabled = false; btn.innerHTML = '<i data-lucide="locate" style="width:14px;height:14px"></i> Buscar lugares cercanos'; if (window.lucide) lucide.createIcons(btn); }
}

function onGeo(pos) {
  userLat = pos.coords.latitude;
  userLng = pos.coords.longitude;
  document.getElementById('location-label').textContent =
    userLat.toFixed(4) + ', ' + userLng.toFixed(4);

  resetBtn();
  hideState();
  document.getElementById('services-container').innerHTML = '';

  CATEGORIES.forEach(cat => {
    const section = buildSkeleton(cat);
    document.getElementById('services-container').appendChild(section);
    fetchCategory(cat, section);
  });
}

function onGeoError(err) {
  resetBtn();
  const msgs = {
    1: 'Permiso denegado. Ve a los ajustes de tu navegador/dispositivo y permite el acceso a la ubicación para este sitio.',
    2: 'No se pudo determinar la ubicación. Comprueba que el GPS está activado.',
    3: 'Tiempo de espera agotado. Asegúrate de tener señal GPS y vuelve a intentarlo.',
  };
  showState('error', 'Sin ubicación', msgs[err.code] || 'Error desconocido.');
}

/* ── Fetch por categoría ────────────────────────────────────── */
function fetchCategory(cat, section) {
  const url = NEARBY_URL + '?lat=' + userLat + '&lng=' + userLng + '&type=' + encodeURIComponent(cat.key);
  fetch(url)
    .then(r => r.json())
    .then(data => data.error ? renderError(section, data.error) : renderResults(section, cat, data.results || []))
    .catch(() => renderError(section, 'Error de red'));
}

/* ── Skeleton mientras carga ────────────────────────────────── */
function buildSkeleton(cat) {
  const c   = COLOR_MAP[cat.color] || COLOR_MAP.accent;
  const div = document.createElement('div');
  div.className = 'card service-category';
  div.dataset.label = cat.label.toLowerCase();
  div.style.marginBottom = '24px';
  div.innerHTML = `
    <div class="card-header" style="margin-bottom:12px">
      <span class="card-title">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;background:${c.bg};flex-shrink:0">
          <i data-lucide="${cat.icon}" style="width:14px;height:14px;color:${c.text}"></i>
        </span>
        ${cat.label}
      </span>
      <span class="cat-count" style="font-size:0.78rem;color:var(--muted)">Buscando…</span>
    </div>
    <div class="cat-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:12px">
      ${[1,2,3].map(skeletonCard).join('')}
    </div>`;
  if (window.lucide) lucide.createIcons(div);
  return div;
}

function skeletonCard() {
  return `<div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;height:140px;animation:pulse 1.4s ease-in-out infinite">
    <div style="height:13px;border-radius:4px;background:#E2E8F0;margin-bottom:10px;width:65%"></div>
    <div style="height:10px;border-radius:4px;background:#E2E8F0;margin-bottom:8px;width:40%"></div>
    <div style="height:10px;border-radius:4px;background:#E2E8F0;width:55%"></div>
  </div>`;
}

/* ── Render resultados ──────────────────────────────────────── */
function renderResults(section, cat, results) {
  const c = COLOR_MAP[cat.color] || COLOR_MAP.accent;
  section.querySelector('.cat-count').textContent =
    results.length ? results.length + ' encontrados' : 'Sin resultados en tu zona';

  if (!results.length) {
    section.querySelector('.cat-body').innerHTML =
      `<p style="color:var(--muted);font-size:0.85rem;padding:4px 0;grid-column:1/-1">
        No hemos encontrado proveedores de este tipo en un radio de 3 km.
       </p>`;
    return;
  }

  section.querySelector('.cat-body').innerHTML = results.map(p => `
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:9px;transition:box-shadow .15s"
         onmouseover="this.style.boxShadow='0 4px 14px rgba(0,0,0,0.08)'"
         onmouseout="this.style.boxShadow='none'">

      <!-- Nombre + distancia -->
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
        <div style="font-weight:600;font-size:0.9rem;line-height:1.3">${escHtml(p.name)}</div>
        <span style="font-size:0.7rem;background:var(--surface);border:1px solid var(--border);padding:2px 8px;border-radius:20px;white-space:nowrap;color:var(--muted);display:flex;align-items:center;gap:3px;flex-shrink:0">
          <i data-lucide="map-pin" style="width:10px;height:10px"></i>${escHtml(p.distance)}
        </span>
      </div>

      <!-- Dirección -->
      ${p.address ? `
      <div style="font-size:0.78rem;color:var(--muted);display:flex;align-items:flex-start;gap:5px">
        <i data-lucide="map-pin" style="width:11px;height:11px;flex-shrink:0;margin-top:2px"></i>
        <span>${escHtml(p.address)}</span>
      </div>` : ''}

      <!-- Horario -->
      ${p.hours ? `
      <div style="font-size:0.78rem;color:var(--muted);display:flex;align-items:flex-start;gap:5px">
        <i data-lucide="clock" style="width:11px;height:11px;flex-shrink:0;margin-top:2px"></i>
        <span>${escHtml(p.hours)}</span>
      </div>` : ''}

      <!-- Botones: teléfono / web / mapa -->
      <div style="display:flex;flex-direction:column;gap:6px;margin-top:auto">
        ${p.phone ? `
        <a href="tel:${escHtml(p.phone.replace(/\s/g,''))}"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:7px;background:${c.bg};border-radius:8px;color:${c.text};font-size:0.82rem;font-weight:600;text-decoration:none"
           onmouseover="this.style.opacity='.8'" onmouseout="this.style.opacity='1'">
          <i data-lucide="phone" style="width:13px;height:13px"></i>${escHtml(p.phone)}
        </a>` : ''}

        ${p.website ? `
        <a href="${escHtml(p.website)}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:7px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.82rem;font-weight:500;text-decoration:none"
           onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
          <i data-lucide="globe" style="width:13px;height:13px"></i>Sitio web
        </a>` : ''}

        <a href="https://www.openstreetmap.org/${escHtml(p.osm_type)}/${escHtml(String(p.osm_id))}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:7px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:0.78rem;text-decoration:none"
           onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
          <i data-lucide="map" style="width:12px;height:12px"></i>Ver en OpenStreetMap
        </a>
      </div>
    </div>`).join('');

  if (window.lucide) lucide.createIcons(section);
}

function renderError(section, msg) {
  section.querySelector('.cat-count').textContent = 'Error';
  section.querySelector('.cat-body').innerHTML =
    `<p style="color:var(--danger);font-size:0.85rem;padding:4px 0;grid-column:1/-1">${escHtml(msg)}</p>`;
}

/* ── Filtro ─────────────────────────────────────────────────── */
function filterCategories(q) {
  q = q.trim().toLowerCase();
  document.querySelectorAll('.service-category').forEach(el => {
    el.style.display = (!q || el.dataset.label.includes(q)) ? '' : 'none';
  });
}

/* ── Helpers ────────────────────────────────────────────────── */
function escHtml(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showState(type, title, body) {
  const icons = {
    loader: '<i data-lucide="loader-2" style="width:40px;height:40px;color:var(--muted)"></i>',
    error:  '<i data-lucide="map-pin-off" style="width:40px;height:40px;color:var(--danger)"></i>',
  };
  document.getElementById('state-icon').innerHTML   = icons[type] || '';
  document.getElementById('state-title').textContent = title;
  document.getElementById('state-body').textContent  = body;
  document.getElementById('state-msg').style.display = '';
  document.getElementById('services-container').innerHTML = '';
  if (window.lucide) lucide.createIcons();
}

function hideState() {
  document.getElementById('state-msg').style.display = 'none';
}

/* ── CSS skeleton ───────────────────────────────────────────── */
document.head.insertAdjacentHTML('beforeend',
  '<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}</style>');
</script>

<?= view('layouts/footer') ?>
