<?= view('layouts/header') ?>

<!-- Game canvas card -->
<div class="card" style="padding:0;overflow:hidden">
  <div style="position:relative;line-height:0">
    <canvas id="fr-cv" style="display:block;width:100%;aspect-ratio:4;cursor:pointer;touch-action:none"></canvas>

    <div id="fr-ov" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0;background:rgba(30,27,75,0.55);backdrop-filter:blur(3px)">
      <div id="fr-ttl" style="font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;line-height:1;color:#fff;letter-spacing:-.5px;text-shadow:0 2px 10px rgba(0,0,0,.3);margin-bottom:10px">FlatRunner</div>
      <div id="fr-scr" style="font-size:.88rem;color:rgba(255,255,255,.7);min-height:1.2em;text-align:center;margin-bottom:14px"></div>
      <button onclick="FR.jump()" class="btn btn-primary" id="fr-btn" style="font-size:1rem;padding:10px 32px">▶ Jugar</button>
      <div id="fr-hint" style="font-size:.72rem;color:rgba(255,255,255,.38);margin-top:10px">Espacio · Click · Tap para saltar</div>
    </div>
  </div>
</div>

<!-- Bottom grid: hi-score + ranking -->
<div id="fr-bottom-grid" style="display:grid;grid-template-columns:auto 1fr;gap:16px;margin-top:16px;align-items:start">

  <!-- Personal hi-score -->
  <div class="card" id="fr-hi-card" style="min-width:160px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
      <i data-lucide="star" style="width:18px;height:18px;color:#f59e0b;flex-shrink:0"></i>
      <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em">Tu récord</div>
    </div>
    <div id="fr-hi" style="font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;color:var(--primary);line-height:1">0</div>
    <button onclick="FR.resetHi()" class="btn btn-secondary btn-sm" style="margin-top:12px;gap:5px;width:100%;justify-content:center">
      <i data-lucide="rotate-ccw" style="width:12px;height:12px"></i> Borrar
    </button>
  </div>

  <!-- Ranking del hogar -->
  <div class="card">
    <div class="card-header" style="margin-bottom:8px">
      <span class="card-title" style="display:flex;align-items:center;gap:6px">
        <i data-lucide="trophy" style="width:15px;height:15px"></i> Ranking del hogar
      </span>
    </div>
    <div id="fr-ranking">
      <?php if (empty($ranking)): ?>
        <div style="padding:16px 0;text-align:center;color:var(--muted);font-size:.85rem">
          Aún no hay puntuaciones. ¡Sé el primero!
        </div>
      <?php else: ?>
        <?php
        $medals = ['🥇','🥈','🥉'];
        foreach ($ranking as $i => $r):
          $pos  = $i + 1;
          $isMe = (int)$r['user_id'] === $currentUserId;
          $medal = $medals[$i] ?? $pos;
        ?>
        <div style="display:flex;align-items:center;gap:12px;padding:9px <?= $isMe ? '8px' : '4px' ?>;<?= $i < count($ranking)-1 ? 'border-bottom:1px solid var(--divider);' : '' ?><?= $isMe ? 'background:rgba(124,106,247,0.07);border-radius:8px;' : '' ?>">
          <div style="flex:0 0 26px;font-size:1.05rem;text-align:center;line-height:1"><?= $medal ?></div>
          <div class="user-avatar" style="width:26px;height:26px;font-size:.65rem;flex-shrink:0"><?= strtoupper(substr($r['username'],0,1)) ?></div>
          <div style="flex:1;font-size:.88rem;font-weight:<?= $isMe ? '600' : '500' ?>">
            <?= esc($r['username']) ?><?php if ($isMe): ?> <span style="color:var(--muted);font-weight:400;font-size:.78rem">(tú)</span><?php endif; ?>
          </div>
          <div style="font-family:'Syne',sans-serif;font-size:.95rem;font-weight:800;color:var(--primary)"><?= number_format((int)$r['score']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<style>
@media (max-width: 640px) {
  /* Taller canvas on mobile so the game is actually playable */
  #fr-cv { aspect-ratio: 2 !important; }

  /* Stack hi-score + ranking vertically */
  #fr-bottom-grid {
    grid-template-columns: 1fr !important;
  }
  #fr-hi-card {
    min-width: 0 !important;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }
  #fr-hi-card > :first-child { flex: 1; }
  #fr-hi {
    font-size: 2.4rem !important;
    flex: 0 0 auto;
  }
  #fr-hi-card .btn { margin-top: 0 !important; width: auto !important; }

  /* Bigger overlay text on mobile */
  #fr-ttl { font-size: 1.5rem !important; }
  #fr-hint { display: none; }
}
</style>

<script>
const FR = (() => {
'use strict';

/* ── Canvas setup ─────────────────────────────────────────────────── */
const cv  = document.getElementById('fr-cv');
const ctx = cv.getContext('2d');
const W = 800, H = 200;  // game coordinate space (fixed)
let scale = 1, visibleW = W;

function fitCanvas() {
  const w = cv.clientWidth  || W;
  const h = cv.clientHeight || H;
  cv.width  = w;
  cv.height = h;
  scale    = h / H;          // uniform scale: fit to height
  visibleW = w / scale;      // visible game units horizontally
}
fitCanvas();
window.addEventListener('resize', () => { fitCanvas(); if (state !== 'running') draw(); });

/* ── Server / CSRF ────────────────────────────────────────────────── */
const CSRF_NAME  = '<?= csrf_token() ?>';
const CSRF_VALUE = '<?= csrf_hash() ?>';
const SCORE_URL  = '<?= site_url('/game/score') ?>';
const ME_ID      = <?= $currentUserId ?>;
const MEDALS     = ['🥇','🥈','🥉'];

/* ── Palette ──────────────────────────────────────────────────────── */
const C = {
  bg1:'#eae8ff', bg2:'#f3f1ff',
  gnd:'#7C6AF7', gndD:'#5f52d8',
  pl:'#7C6AF7',  plD:'#5f52d8',
  sk:'#fbbf24',  eye:'#1e1b4b',
  cloud:'rgba(255,255,255,0.82)',
  sh:'rgba(0,0,0,0.07)',
  scoreCol:'#1e1b4b',
  trash:'#ef4444', trashD:'#b91c1c',
  bill:'#f59e0b',  billD:'#d97706',
  note:'#6366f1',  noteD:'#4338ca',
};

const GND   = 160;
const JUMPV = -12;
const GRAV  = 0.75;

/* ── Player ───────────────────────────────────────────────────────── */
const P = { x:75, w:26, h:44, y:GND-44, vy:0, onG:true, f:0, t:0 };

/* ── Obstacles ────────────────────────────────────────────────────── */
const TYPES = [
  { id:'bin',  color:C.trash, dark:C.trashD, w:28, h:44 },
  { id:'bill', color:C.bill,  dark:C.billD,  w:40, h:28 },
  { id:'note', color:C.note,  dark:C.noteD,  w:26, h:40 },
  { id:'fly',  color:'#818cf8', dark:'#4f46e5', w:38, h:22 }, // flying: stay on ground!
];
const FLY_Y = 69; // y position for flying obstacles (above player when standing)
let obs = [], oT = 0, oN = 90;

/* ── Clouds ───────────────────────────────────────────────────────── */
const clouds = [
  {x:120,y:22,w:70,h:22},{x:380,y:14,w:90,h:28},
  {x:620,y:28,w:58,h:20},{x:760,y:18,w:65,h:24},
];

/* ── State ────────────────────────────────────────────────────────── */
let state = 'idle', score = 0, frameN = 0, speed = 4, raf = null;
let hi = parseInt(localStorage.getItem('fr_hi') || '0');

/* ── Helpers ──────────────────────────────────────────────────────── */
function rr(x, y, w, h, r) {
  r = r || 4;
  ctx.beginPath();
  ctx.moveTo(x+r,y); ctx.lineTo(x+w-r,y);
  ctx.quadraticCurveTo(x+w,y,x+w,y+r); ctx.lineTo(x+w,y+h-r);
  ctx.quadraticCurveTo(x+w,y+h,x+w-r,y+h); ctx.lineTo(x+r,y+h);
  ctx.quadraticCurveTo(x,y+h,x,y+h-r); ctx.lineTo(x,y+r);
  ctx.quadraticCurveTo(x,y,x+r,y); ctx.closePath();
}
function shadow(cx, rx) {
  ctx.fillStyle = C.sh; ctx.beginPath();
  ctx.ellipse(cx, GND+4, rx||12, 4, 0, 0, Math.PI*2); ctx.fill();
}

/* ── Spawn ────────────────────────────────────────────────────────── */
function spawn() {
  const flying = Math.random() < 0.22;           // 22 % flying
  const t = flying ? TYPES[3] : TYPES[Math.floor(Math.random()*3)];
  obs.push({...t, x:visibleW+16, y: flying ? FLY_Y : GND-t.h});

  // 30 % chance of a tight pair — gap always small enough to jump both at once
  let paired = false;
  if (!flying && Math.random() < 0.30) {
    const t2 = TYPES[Math.floor(Math.random()*3)];
    const gap = 8 + Math.floor(Math.random() * 22); // 8–29 px: always clearable in one jump
    obs.push({...t2, x:visibleW+16+t.w+gap, y:GND-t2.h});
    paired = true;
  }

  oT = 0;
  // After a pair, add extra breathing room before the next obstacle
  oN = Math.max(52, 75 + Math.floor(Math.random()*55) - Math.min(28, frameN*0.017) + (paired ? 28 : 0));
}

/* ── Update ───────────────────────────────────────────────────────── */
function update() {
  frameN++; score = frameN/6|0; speed = 4 + frameN*0.002;
  if (!P.onG) P.vy += GRAV; P.y += P.vy;
  if (P.y >= GND-P.h) { P.y = GND-P.h; P.vy = 0; P.onG = true; }
  P.t++; if (P.onG && P.t%7===0) P.f = (P.f+1)%4;
  if (++oT >= oN) spawn();
  for (let i=obs.length-1;i>=0;i--) { obs[i].x-=speed; if(obs[i].x+obs[i].w<0) obs.splice(i,1); }
  clouds.forEach(c => { c.x -= speed*0.22; if (c.x+c.w<0) c.x = visibleW+c.w; });
  const m=5, px=P.x+m, pw=P.x+P.w-m, py=P.y+m, ph=P.y+P.h-m;
  for (const o of obs) {
    if (px<o.x+o.w-m && pw>o.x+m && py<o.y+o.h-m && ph>o.y+m) {
      state = 'dead';
      if (score > hi) { hi = score; localStorage.setItem('fr_hi', hi); }
      return;
    }
  }
}

/* ── Draw ─────────────────────────────────────────────────────────── */
function drawBg() {
  const g = ctx.createLinearGradient(0,0,0,GND);
  g.addColorStop(0,C.bg1); g.addColorStop(1,C.bg2);
  ctx.fillStyle=g; ctx.fillRect(0,0,visibleW,GND);
  clouds.forEach(c => { ctx.fillStyle=C.cloud; rr(c.x,c.y,c.w,c.h,c.h/2); ctx.fill(); });
  ctx.fillStyle=C.gnd;  ctx.fillRect(0,GND,visibleW,H-GND);
  ctx.fillStyle=C.gndD; ctx.fillRect(0,GND,visibleW,4);
  ctx.fillStyle='rgba(255,255,255,0.2)';
  const off=(frameN*speed)%50|0;
  for (let x=-50+off;x<visibleW;x+=50) ctx.fillRect(x,GND+10,24,2);
}

function drawPlayer() {
  const {x,y,onG,f}=P; const jmp=!onG, sw=f%2?1:-1;
  shadow(x+12);
  ctx.fillStyle=C.plD;
  if (jmp) { ctx.fillRect(x+4,y+34,6,8); ctx.fillRect(x+15,y+34,6,8); }
  else { const a=f%2===0; ctx.fillRect(x+4,y+32,6,a?14:9); ctx.fillRect(x+15,y+32,6,a?9:14); }
  ctx.fillStyle=C.pl; rr(x+4,y+16,18,17,3); ctx.fill();
  if (jmp) { ctx.fillRect(x-2,y+16,7,5); ctx.fillRect(x+20,y+16,7,5); }
  else { ctx.fillRect(x-2,y+19+sw*2,7,5); ctx.fillRect(x+20,y+19-sw*2,7,5); }
  ctx.fillStyle=C.sk; ctx.beginPath(); ctx.arc(x+13,y+9,9,0,Math.PI*2); ctx.fill();
  ctx.fillStyle=C.plD; ctx.beginPath(); ctx.arc(x+13,y+4,7,Math.PI,0); ctx.fill();
  ctx.fillStyle=C.eye; ctx.fillRect(x+17,y+7,2,3);
  if (!jmp) { ctx.strokeStyle='#b45309'; ctx.lineWidth=1.5; ctx.beginPath(); ctx.arc(x+15,y+12,4,.15,Math.PI-.15); ctx.stroke(); }
}

function drawFly(o) {
  // faded distant shadow on ground
  ctx.fillStyle = 'rgba(0,0,0,0.04)';
  ctx.beginPath(); ctx.ellipse(o.x+o.w/2, GND+4, o.w/2, 3, 0, 0, Math.PI*2); ctx.fill();

  const cx = o.x+o.w/2, cy = o.y+o.h/2;
  const flapDown = (frameN>>3)%2===0; // wings flap every 8 frames
  const tipY = flapDown ? o.y+o.h+4 : o.y-6;

  // wings
  ctx.fillStyle = o.color;
  ctx.beginPath(); // left wing
  ctx.moveTo(cx-3, cy);
  ctx.quadraticCurveTo(cx-o.w/3, tipY, o.x-4, cy+5);
  ctx.lineTo(o.x+5, cy+9); ctx.quadraticCurveTo(cx-o.w/4, cy+7, cx-3, cy); ctx.fill();
  ctx.beginPath(); // right wing
  ctx.moveTo(cx+3, cy);
  ctx.quadraticCurveTo(cx+o.w/3, tipY, o.x+o.w+4, cy+5);
  ctx.lineTo(o.x+o.w-5, cy+9); ctx.quadraticCurveTo(cx+o.w/4, cy+7, cx+3, cy); ctx.fill();

  // coin body
  ctx.fillStyle = o.dark;
  ctx.beginPath(); ctx.ellipse(cx, cy, 13, 8, 0, 0, Math.PI*2); ctx.fill();
  ctx.fillStyle = C.bill;
  ctx.beginPath(); ctx.ellipse(cx, cy, 10, 6, 0, 0, Math.PI*2); ctx.fill();
  ctx.fillStyle = '#fff';
  ctx.font = 'bold 9px sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
  ctx.fillText('€', cx, cy+0.5); ctx.textAlign = 'left'; ctx.textBaseline = 'alphabetic';
}

function drawObs(o) {
  if (o.id === 'fly') { drawFly(o); return; }
  shadow(o.x+o.w/2, o.w/2+3);
  if (o.id==='bin') {
    ctx.fillStyle='#fca5a5'; ctx.fillRect(o.x+7,o.y-5,o.w-14,6);
    ctx.fillStyle=o.dark; rr(o.x-2,o.y,o.w+4,9,3); ctx.fill();
    ctx.fillStyle=o.color; ctx.fillRect(o.x+2,o.y+9,o.w-4,o.h-9);
    ctx.strokeStyle='rgba(255,255,255,0.38)'; ctx.lineWidth=1.5;
    for (let lx=o.x+8;lx<o.x+o.w-3;lx+=6) { ctx.beginPath(); ctx.moveTo(lx,o.y+14); ctx.lineTo(lx,o.y+o.h-4); ctx.stroke(); }
  } else if (o.id==='bill') {
    ctx.fillStyle=o.color; rr(o.x,o.y,o.w,o.h,4); ctx.fill();
    ctx.fillStyle=o.dark; ctx.fillRect(o.x,o.y,o.w,7);
    ctx.fillStyle='#fff'; ctx.font='bold 13px sans-serif'; ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText('€',o.x+o.w/2,o.y+o.h/2+3); ctx.textAlign='left'; ctx.textBaseline='alphabetic';
    ctx.fillStyle='rgba(255,255,255,0.45)'; ctx.fillRect(o.x+5,o.y+10,o.w-10,2); ctx.fillRect(o.x+5,o.y+o.h-6,o.w-10,2);
  } else {
    ctx.fillStyle=o.color; rr(o.x,o.y,o.w,o.h,3); ctx.fill();
    ctx.fillStyle='rgba(255,255,255,0.5)';
    for (let ly=o.y+8;ly<o.y+o.h-8;ly+=7) ctx.fillRect(o.x+4,ly,o.w-8,2);
    ctx.fillStyle='#a5b4fc'; ctx.fillRect(o.x+o.w/2-3,o.y-5,6,6);
    ctx.fillStyle='#fff'; ctx.beginPath(); ctx.arc(o.x+o.w/2,o.y,3,0,Math.PI*2); ctx.fill();
  }
}

function drawScore() {
  ctx.fillStyle=C.scoreCol; ctx.font='bold 14px "Syne",monospace';
  ctx.textAlign='right'; ctx.textBaseline='top';
  ctx.fillText('PUNTOS  '+String(score).padStart(5,'0'), visibleW-14, 10);
  ctx.textAlign='left'; ctx.textBaseline='alphabetic';
}

function draw() {
  ctx.save();
  ctx.scale(scale, scale);
  drawBg();
  obs.forEach(drawObs);
  drawPlayer();
  if (state === 'running') drawScore();
  ctx.restore();
}

/* ── Ranking update ───────────────────────────────────────────────── */
function renderRanking(ranking) {
  const wrap = document.getElementById('fr-ranking');
  if (!ranking || !ranking.length) {
    wrap.innerHTML = '<div style="padding:16px 0;text-align:center;color:var(--muted);font-size:.85rem">Aún no hay puntuaciones. ¡Sé el primero!</div>';
    return;
  }
  wrap.innerHTML = ranking.map((r, i) => {
    const isMe = parseInt(r.user_id) === ME_ID;
    const medal = MEDALS[i] || (i+1);
    return `<div style="display:flex;align-items:center;gap:12px;padding:9px ${isMe?'8px':'4px'};${i<ranking.length-1?'border-bottom:1px solid var(--divider);':''}${isMe?'background:rgba(124,106,247,0.07);border-radius:8px;':''}">
      <div style="flex:0 0 26px;font-size:1.05rem;text-align:center;line-height:1">${medal}</div>
      <div class="user-avatar" style="width:26px;height:26px;font-size:.65rem;flex-shrink:0">${r.username.charAt(0).toUpperCase()}</div>
      <div style="flex:1;font-size:.88rem;font-weight:${isMe?'600':'500'}">${r.username}${isMe?' <span style="color:var(--muted);font-weight:400;font-size:.78rem">(tú)</span>':''}</div>
      <div style="font-family:\'Syne\',sans-serif;font-size:.95rem;font-weight:800;color:var(--primary)">${parseInt(r.score).toLocaleString()}</div>
    </div>`;
  }).join('');
}

async function submitScore(s) {
  try {
    const fd = new FormData();
    fd.append(CSRF_NAME, CSRF_VALUE);
    fd.append('score', s);
    const res  = await fetch(SCORE_URL, { method:'POST', body:fd });
    const data = await res.json();
    if (data.ok) {
      renderRanking(data.ranking);
      if (data.new_record) {
        document.getElementById('fr-hi').textContent = s.toLocaleString();
        // flash the hi-score
        const el = document.getElementById('fr-hi');
        el.style.transition = 'color .2s';
        el.style.color = '#10b981';
        setTimeout(() => { el.style.color = ''; }, 1200);
      }
    }
  } catch (e) {}
}

/* ── Game over ────────────────────────────────────────────────────── */
function gameOver() {
  draw();
  submitScore(score);
  const newHi = score >= hi;
  document.getElementById('fr-ttl').textContent = newHi ? '🏆 ¡Nuevo récord!' : '¡Game Over!';
  document.getElementById('fr-scr').innerHTML =
    'Puntuación: <b>' + score + '</b>&nbsp;&nbsp;·&nbsp;&nbsp;Récord: <b>' + hi + '</b>';
  document.getElementById('fr-btn').textContent = '↺ Reintentar';
  document.getElementById('fr-ov').style.display = 'flex';
  document.getElementById('fr-hi').textContent = hi.toLocaleString();
}

/* ── Start / loop ─────────────────────────────────────────────────── */
function startGame() {
  if (raf) cancelAnimationFrame(raf);
  Object.assign(P, {y:GND-44, vy:0, onG:true, f:0, t:0});
  obs=[]; oT=0; oN=90; score=0; frameN=0; speed=4; state='running';
  document.getElementById('fr-ov').style.display = 'none';
  raf = requestAnimationFrame(loop);
}
function loop() {
  update(); draw();
  if (state==='running') raf = requestAnimationFrame(loop);
  else gameOver();
}

/* ── Controls ─────────────────────────────────────────────────────── */
function jump() {
  if (state==='running') { if (P.onG) { P.vy=JUMPV; P.onG=false; } }
  else startGame();
}
function resetHi() {
  hi = 0; localStorage.removeItem('fr_hi');
  document.getElementById('fr-hi').textContent = '0';
}

/* ── Init ─────────────────────────────────────────────────────────── */
document.getElementById('fr-hi').textContent = hi.toLocaleString();
if (hi > 0) document.getElementById('fr-scr').textContent = 'Récord: ' + hi;
requestAnimationFrame(() => { fitCanvas(); draw(); });

document.addEventListener('keydown', e => {
  if (e.code==='Space'||e.code==='ArrowUp') { e.preventDefault(); jump(); }
});
cv.addEventListener('click', () => jump());
cv.addEventListener('touchstart', e => { e.preventDefault(); jump(); }, {passive:false});

return { jump, resetHi };
})();
</script>

<?= view('layouts/footer') ?>
