// ── Modal helpers ──
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// Close modal clicking outside
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// ── Mobile sidebar toggle ──
const menuBtn = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');
if (menuBtn && sidebar) {
  menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
}

// ── Auto-dismiss alerts ──
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => {
    el.style.transition = 'opacity .5s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  });
}, 4000);

// ── Animate stat numbers ──
function animateValue(el, start, end, duration) {
  let startTs = null;
  const step = (ts) => {
    if (!startTs) startTs = ts;
    const progress = Math.min((ts - startTs) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    el.textContent = el.dataset.prefix + Math.floor(start + (end - start) * eased) + (el.dataset.suffix || '');
    if (progress < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

document.querySelectorAll('[data-animate]').forEach(el => {
  const val = parseFloat(el.dataset.animate);
  animateValue(el, 0, val, 1000);
});

// ── Progress bars ──
document.querySelectorAll('.progress-fill[data-width]').forEach(el => {
  setTimeout(() => { el.style.width = el.dataset.width + '%'; }, 100);
});

// ── Service Worker ──
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}
