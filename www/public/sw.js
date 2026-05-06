const CACHE     = 'flatsync-v1';
const OFFLINE   = '/offline.html';

// Static assets to pre-cache on install
const PRECACHE = [
  '/offline.html',
  '/css/app.css',
  '/js/app.js',
  '/assets/icon-192.png',
  '/assets/icon-512.png',
  '/assets/logo.png',
];

// ── Install: pre-cache shell ──────────────────────────────────────────────────
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE).then(c => c.addAll(PRECACHE))
  );
  self.skipWaiting();
});

// ── Activate: delete old caches ───────────────────────────────────────────────
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// ── Fetch strategy ────────────────────────────────────────────────────────────
self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // Only handle same-origin GET requests
  if (request.method !== 'GET' || url.origin !== location.origin) return;

  // API and dynamic routes → network only, no cache
  if (url.pathname.startsWith('/api/') ||
      url.pathname.startsWith('/chat/poll') ||
      url.pathname.startsWith('/expenses/poll') ||
      url.pathname.startsWith('/chores/poll')) {
    return;
  }

  // Static assets (CSS, JS, images, fonts) → cache first, update in background
  if (/\.(css|js|png|jpg|jpeg|webp|svg|ico|woff2?)$/.test(url.pathname)) {
    e.respondWith(
      caches.match(request).then(cached => {
        const network = fetch(request).then(res => {
          if (res.ok) caches.open(CACHE).then(c => c.put(request, res.clone()));
          return res;
        });
        return cached || network;
      })
    );
    return;
  }

  // HTML pages → network first, fall back to offline page
  e.respondWith(
    fetch(request)
      .then(res => {
        if (res.ok) {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(request, clone));
        }
        return res;
      })
      .catch(() => caches.match(request).then(cached => cached || caches.match(OFFLINE)))
  );
});
