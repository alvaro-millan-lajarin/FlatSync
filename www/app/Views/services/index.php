<?= view('layouts/header') ?>

<!-- Barra superior -->
<div class="card" style="margin-bottom:24px">
  <div style="display:flex;flex-direction:column;gap:14px">

    <!-- Búsqueda de categoría -->
    <div style="position:relative">
      <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--muted)"></i>
      <input type="text" id="service-search" placeholder="<?= lang('App.services_search_ph') ?>"
             style="width:100%;padding:9px 12px 9px 36px;border:1px solid var(--border);border-radius:8px;font-size:0.875rem;font-family:inherit;background:var(--surface2);color:var(--text);outline:none;box-sizing:border-box"
             oninput="filterCategories(this.value)">
    </div>

    <!-- Ubicación actual + botón GPS -->
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:var(--muted);min-width:0;flex:1">
        <i data-lucide="map-pin" style="width:13px;height:13px;color:var(--primary);flex-shrink:0"></i>
        <span id="location-label" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= lang('App.services_no_location') ?></span>
      </div>
      <button id="btn-locate" onclick="loadAll()" class="btn btn-sm btn-primary" style="display:flex;align-items:center;gap:5px;flex-shrink:0">
        <i data-lucide="locate" style="width:13px;height:13px"></i> <?= lang('App.services_my_location') ?>
      </button>
      <button onclick="toggleManual()" class="btn btn-sm btn-secondary" style="display:flex;align-items:center;gap:5px;flex-shrink:0">
        <i data-lucide="map" style="width:13px;height:13px"></i> <?= lang('App.services_change') ?>
      </button>
    </div>

    <!-- Panel cambiar ubicación (oculto por defecto) -->
    <div id="manual-location-panel" style="display:none;border-top:1px solid var(--divider);padding-top:12px">
      <label style="display:block;font-size:0.78rem;font-weight:500;color:var(--text-secondary);margin-bottom:6px"><?= lang('App.services_city_ph') ?></label>
      <div style="display:flex;gap:8px">
        <input type="text" id="manual-location-input" placeholder="<?= lang('App.services_city_ex') ?>"
               style="flex:1;padding:9px 13px;border:1px solid var(--border);border-radius:8px;font-size:0.875rem;font-family:inherit;background:var(--surface2);color:var(--text);outline:none;min-width:0"
               onkeydown="if(event.key==='Enter'){searchManualLocation();event.preventDefault()}">
        <button onclick="searchManualLocation()" class="btn btn-primary" style="flex-shrink:0;display:flex;align-items:center;gap:5px">
          <i data-lucide="search" style="width:14px;height:14px"></i> <?= lang('App.services_search_btn') ?>
        </button>
      </div>
      <div id="manual-location-results" style="margin-top:8px"></div>
    </div>

  </div>
</div>

<!-- Categorías (rellenado por JS) -->
<div id="services-container"></div>

<!-- Estado global (loader / error) -->
<div id="state-msg" style="display:none">
  <div class="empty-state" style="padding:60px 0">
    <div id="state-icon"></div>
    <h3 id="state-title"></h3>
    <p id="state-body"></p>
  </div>
</div>

<?php
$cats = array_map(fn($c) => [
    'key'   => $c['key'],
    'label' => $c['label'],
    'icon'  => $c['icon'],
    'color' => $c['color'],
    'tags'  => $c['tags'],
], $categories);
?>
<script>
const CATEGORIES = <?= json_encode($cats, JSON_UNESCAPED_UNICODE) ?>;
const SVC_L = {
  found:           <?= json_encode(lang('App.services_found')) ?>,
  noResults:       <?= json_encode(lang('App.services_no_results')) ?>,
  noResultsSub:    <?= json_encode(lang('App.services_no_results_sub')) ?>,
  searching:       <?= json_encode(lang('App.services_searching') ?: 'Buscando…') ?>,
  noLocation:      <?= json_encode(lang('App.services_no_location') ?: 'Sin ubicación') ?>,
  gettingAddr:     <?= json_encode(lang('App.services_getting_addr') ?: 'Obteniendo dirección…') ?>,
  website:         <?= json_encode(lang('App.svc_website')) ?>,
  osm:             <?= json_encode(lang('App.svc_osm')) ?>,
  gpsBtn:          <?= json_encode(lang('App.svc_gps_btn')) ?>,
  gpsSearching:    <?= json_encode(lang('App.svc_gps_searching')) ?>,
  geoLoading:      <?= json_encode(lang('App.svc_geo_loading')) ?>,
  geoLoadingBody:  <?= json_encode(lang('App.svc_geo_loading_body')) ?>,
  geoUnavail:      <?= json_encode(lang('App.svc_geo_unavail')) ?>,
  geoNoSupport:    <?= json_encode(lang('App.svc_geo_no_support')) ?>,
  geoDenied:       <?= json_encode(lang('App.svc_geo_denied')) ?>,
  geoDeniedHttp:   <?= json_encode(lang('App.svc_geo_denied_http')) ?>,
  geoDeniedReset:  <?= json_encode(lang('App.svc_geo_denied_reset')) ?>,
  geoNoPos:        <?= json_encode(lang('App.svc_geo_no_pos')) ?>,
  geoTimeout:      <?= json_encode(lang('App.svc_geo_timeout')) ?>,
  osmError:        <?= json_encode(lang('App.svc_osm_error')) ?>,
  manualSearching: <?= json_encode(lang('App.svc_manual_searching')) ?>,
  manualNoResults: <?= json_encode(lang('App.svc_manual_no_results')) ?>,
  manualError:     <?= json_encode(lang('App.svc_manual_error')) ?>,
  localProvider:   <?= json_encode(lang('App.svc_local_provider')) ?>,
  serviceIn:       <?= json_encode(lang('App.svc_service_in')) ?>,
};

const COLOR_MAP = {
  accent:  { bg: 'rgba(37,99,235,0.08)',  text: 'var(--primary)',  star: '#2563EB' },
  warning: { bg: 'rgba(245,158,11,0.08)', text: 'var(--warning)', star: '#F59E0B' },
  success: { bg: 'rgba(34,197,94,0.08)',  text: 'var(--success)', star: '#22C55E' },
  danger:  { bg: 'rgba(239,68,68,0.08)',  text: 'var(--danger)',  star: '#EF4444' },
};

let userLat = null, userLng = null;

/* ── Arranque ───────────────────────────────────────────────── */
function loadAll() {
  if (!navigator.geolocation) {
    showState('error', SVC_L.geoUnavail, SVC_L.geoNoSupport);
    return;
  }
  const btn = document.getElementById('btn-locate');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = `<i data-lucide="loader-2" style="width:14px;height:14px"></i> ${SVC_L.gpsSearching}`;
    if (window.lucide) lucide.createIcons(btn);
  }
  showState('loader', SVC_L.geoLoading, SVC_L.geoLoadingBody);
  navigator.geolocation.getCurrentPosition(onGeo, onGeoError, { timeout: 15000, enableHighAccuracy: false });
}

function resetBtn() {
  const btn = document.getElementById('btn-locate');
  if (btn) { btn.disabled = false; btn.innerHTML = `<i data-lucide="locate" style="width:14px;height:14px"></i> ${SVC_L.gpsBtn}`; if (window.lucide) lucide.createIcons(btn); }
}

function onGeo(pos) {
  userLat = pos.coords.latitude;
  userLng = pos.coords.longitude;
  resetBtn();
  setLocationLabel(userLat, userLng);
  launchSearch();
}

async function setLocationLabel(lat, lng) {
  const el = document.getElementById('location-label');
  el.textContent = SVC_L.gettingAddr;
  try {
    const r = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`,
      { headers: { 'Accept-Language': <?= json_encode(session()->get('lang') ?? 'es') ?> } }
    );
    const d = await r.json();
    const a = d.address || {};
    const parts = [
      a.road || a.pedestrian || a.footway || null,
      a.house_number || null,
      a.suburb || a.neighbourhood || a.village || a.town || a.city || null,
    ].filter(Boolean);
    el.textContent = parts.length ? parts.join(', ') : (d.display_name?.split(',').slice(0,2).join(',') || `${lat.toFixed(4)}, ${lng.toFixed(4)}`);
  } catch {
    el.textContent = `${lat.toFixed(4)}, ${lng.toFixed(4)}`;
  }
}

async function launchSearch() {
  hideState();
  const container = document.getElementById('services-container');
  container.innerHTML = '';

  const sections = {};
  CATEGORIES.forEach(cat => {
    const s = buildSkeleton(cat);
    container.appendChild(s);
    sections[cat.key] = s;
  });

  // Una sola query con todos los tags exactos (sin regex → rápido)
  const elements = await overpassFetch();

  if (elements === null) {
    CATEGORIES.forEach(cat =>
      renderError(sections[cat.key], SVC_L.osmError)
    );
    return;
  }

  // Categorizar en cliente
  const bycat = {};
  CATEGORIES.forEach(c => { bycat[c.key] = []; });
  const seen = {};

  elements.forEach(el => {
    const t = el.tags || {};
    if (!el.lat || !el.lon) return;
    const catKey = resolveCategory(t);
    if (!catKey) return;

    const name  = t.name || null;
    const phone = t.phone || t['contact:phone'] || t.mobile || null;
    const web   = t.website || t['contact:website'] || null;
    const st    = t['addr:street'] || null;
    if (!name && !phone && !web && !st) return;

    const dk = (name||'') + Math.round(el.lat*1000) + Math.round(el.lon*1000);
    if (seen[dk]) return;
    seen[dk] = true;

    const dm = haversine(userLat, userLng, el.lat, el.lon);
    bycat[catKey].push({
      name:    name || (st ? SVC_L.serviceIn + st : SVC_L.localProvider),
      address: [st, t['addr:housenumber'], t['addr:city']].filter(Boolean).join(', '),
      phone, website: web,
      hours:   t.opening_hours || null,
      distance_m: dm, distance: fmtDist(dm),
      osm_type: el.type, osm_id: el.id,
    });
  });

  CATEGORIES.forEach(cat => {
    const res = bycat[cat.key].sort((a,b) => a.distance_m - b.distance_m).slice(0, 10);
    renderResults(sections[cat.key], cat, res);
  });
}

function onGeoError(err) {
  resetBtn();
  let titulo = SVC_L.noLocation;
  let detalle = '';
  if (err.code === 1) {
    titulo = SVC_L.geoDenied;
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
      detalle = SVC_L.geoDeniedHttp;
    } else {
      detalle = SVC_L.geoDeniedReset;
    }
  } else if (err.code === 2) {
    detalle = SVC_L.geoNoPos;
  } else {
    detalle = SVC_L.geoTimeout;
  }
  showState('error', titulo, detalle);
}

/* ── Utilidades ─────────────────────────────────────────────── */
function haversine(lat1, lng1, lat2, lng2) {
  const R = 6371000, r = Math.PI / 180;
  const dLat = (lat2-lat1)*r, dLng = (lng2-lng1)*r;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1*r)*Math.cos(lat2*r)*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
function fmtDist(m) { return m < 1000 ? Math.round(m)+' m' : (m/1000).toFixed(1)+' km'; }

/* Mapa tag=valor → clave de categoría */
const CAT_MAP = {
  craft:  { locksmith:'locksmith', plumber:'plumber', hvac:'plumber',
            electrician:'electrician', electronics_repair:'electrician',
            painter:'painter', cleaning:'cleaning', transport:'moving' },
  trade:  { locksmith:'locksmith', plumber:'plumber', electrician:'electrician',
            painter:'painter', cleaning:'cleaning' },
  shop:   { locksmith:'locksmith', plumbing:'plumber', electrical:'electrician',
            dry_cleaning:'cleaning', laundry:'cleaning', cleaning:'cleaning',
            paint:'painter', relocation:'moving', storage_rental:'moving' },
  amenity:{ laundry:'cleaning', storage_rental:'moving' },
  office: { moving_company:'moving' },
};
function resolveCategory(tags) {
  for (const [k, m] of Object.entries(CAT_MAP)) {
    if (tags[k] && m[tags[k]]) return m[tags[k]];
  }
  return null;
}

/* ── Una query única con todos los tags exactos (sin regex) ─── */
function buildCombinedQuery() {
  const a = `(around:5000,${userLat},${userLng})`;
  const lines = [
    // cerrajería
    `node["craft"="locksmith"]${a};`,
    `node["trade"="locksmith"]${a};`,
    `node["shop"="locksmith"]${a};`,
    // fontanería
    `node["craft"="plumber"]${a};`,
    `node["trade"="plumber"]${a};`,
    `node["craft"="hvac"]${a};`,
    `node["shop"="plumbing"]${a};`,
    // electricidad
    `node["craft"="electrician"]${a};`,
    `node["trade"="electrician"]${a};`,
    `node["shop"="electrical"]${a};`,
    `node["craft"="electronics_repair"]${a};`,
    // pintura
    `node["craft"="painter"]${a};`,
    `node["trade"="painter"]${a};`,
    `node["shop"="paint"]${a};`,
    // limpieza
    `node["craft"="cleaning"]${a};`,
    `node["shop"="dry_cleaning"]${a};`,
    `node["shop"="laundry"]${a};`,
    `node["amenity"="laundry"]${a};`,
    // mudanzas
    `node["shop"="relocation"]${a};`,
    `node["office"="moving_company"]${a};`,
    `node["amenity"="storage_rental"]${a};`,
  ].join('');
  return `[out:json][timeout:20];(${lines});out 120;`;
}

/* Lanza la query contra 3 mirrors en paralelo, usa el primero que responda */
async function overpassFetch() {
  const q = buildCombinedQuery();
  const mirrors = [
    'https://overpass-api.de/api/interpreter',
    'https://overpass.kumi.systems/api/interpreter',
    'https://overpass.private.coffee/api/interpreter',
  ];
  const tries = mirrors.map(async url => {
    const ctrl  = new AbortController();
    const timer = setTimeout(() => ctrl.abort(), 22000);
    try {
      const r = await fetch(`${url}?data=${encodeURIComponent(q)}`, { signal: ctrl.signal });
      clearTimeout(timer);
      if (!r.ok) throw new Error('HTTP '+r.status);
      const d = await r.json();
      return d.elements || [];
    } catch(e) { clearTimeout(timer); throw e; }
  });
  try {
    return await Promise.any(tries); // usa el mirror más rápido
  } catch {
    return null;
  }
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
      <span class="cat-count" style="font-size:0.78rem;color:var(--muted)">${SVC_L.searching}</span>
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
    results.length ? results.length + ' ' + SVC_L.found : SVC_L.noResults;

  if (!results.length) {
    section.querySelector('.cat-body').innerHTML =
      `<p style="color:var(--muted);font-size:0.85rem;padding:4px 0;grid-column:1/-1">${SVC_L.noResultsSub}</p>`;
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
          <i data-lucide="globe" style="width:13px;height:13px"></i>${SVC_L.website}
        </a>` : ''}

        <a href="https://www.openstreetmap.org/${escHtml(p.osm_type)}/${escHtml(String(p.osm_id))}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:7px;background:var(--surface);border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:0.78rem;text-decoration:none"
           onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='var(--surface)'">
          <i data-lucide="map" style="width:12px;height:12px"></i>${SVC_L.osm}
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

/* ── Cambio manual de ubicación (Nominatim) ─────────────────── */
function toggleManual() {
  const panel = document.getElementById('manual-location-panel');
  panel.style.display = panel.style.display === 'none' ? '' : 'none';
  if (panel.style.display !== 'none') {
    document.getElementById('manual-location-input').focus();
  }
}

function searchManualLocation() {
  const q = document.getElementById('manual-location-input').value.trim();
  if (!q) return;
  const resultsEl = document.getElementById('manual-location-results');
  resultsEl.innerHTML = `<span style="font-size:0.8rem;color:var(--muted)">${SVC_L.manualSearching}</span>`;

  fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&q=' + encodeURIComponent(q), {
    headers: { 'Accept-Language': <?= json_encode(session()->get('lang') ?? 'es') ?>, 'User-Agent': 'FlatSync/1.0' }
  })
  .then(r => r.json())
  .then(data => {
    if (!data.length) {
      resultsEl.innerHTML = `<span style="font-size:0.8rem;color:var(--muted)">${SVC_L.manualNoResults}</span>`;
      return;
    }
    resultsEl.innerHTML = '';
    data.forEach(place => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.style.cssText = 'display:block;width:100%;text-align:left;padding:8px 10px;margin-bottom:4px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;font-size:0.82rem;color:var(--text);cursor:pointer;font-family:inherit';
      btn.textContent = place.display_name;
      btn.onmouseover = () => btn.style.background = 'var(--surface)';
      btn.onmouseout  = () => btn.style.background = 'var(--surface2)';
      btn.onclick = () => {
        userLat = parseFloat(place.lat);
        userLng = parseFloat(place.lon);
        const label = place.display_name.split(',').slice(0, 2).join(',');
        document.getElementById('location-label').textContent = label;
        document.getElementById('manual-location-panel').style.display = 'none';
        document.getElementById('manual-location-results').innerHTML = '';
        launchSearch();
      };
      resultsEl.appendChild(btn);
    });
  })
  .catch(() => {
    resultsEl.innerHTML = `<span style="font-size:0.8rem;color:var(--danger)">${SVC_L.manualError}</span>`;
  });
}

/* ── Filtro de categorías ────────────────────────────────────── */
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

/* Auto-trigger al cargar */
loadAll();
</script>

<?= view('layouts/footer') ?>
