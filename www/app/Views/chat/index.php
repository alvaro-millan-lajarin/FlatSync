<?= view('layouts/header') ?>

<div class="chat-layout">

  <!-- ── PANEL IZQUIERDO: NOTAS ── -->
  <div class="notes-panel">
    <div class="notes-header">
      <span class="card-title"><i data-lucide="sticky-note"></i> Notas del hogar</span>
    </div>

    <!-- Formulario añadir nota -->
    <form method="post" action="<?= site_url('/chat/notes/store') ?>" class="note-form">
      <?= csrf_field() ?>
      <textarea name="content" placeholder="Escribe una nota..." class="note-textarea" maxlength="500" rows="3"></textarea>
      <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:8px">
        <i data-lucide="plus" style="width:13px;height:13px"></i> Añadir nota
      </button>
    </form>

    <div class="divider"></div>

    <!-- Lista de notas -->
    <div class="notes-list">
      <?php if (empty($notes)): ?>
        <div style="text-align:center;color:var(--muted);font-size:0.82rem;padding:20px 0">
          <i data-lucide="file-text" style="width:28px;height:28px;opacity:.4;display:block;margin:0 auto 8px"></i>
          Sin notas aún
        </div>
      <?php else: ?>
        <?php foreach ($notes as $note): ?>
        <div class="note-item">
          <div class="note-content"><?= nl2br(esc($note['content'])) ?></div>
          <div class="note-meta">
            <span><?= esc($note['username']) ?> · <?= date('d/m H:i', strtotime($note['created_at'])) ?></span>
            <?php if ($note['user_id'] == session()->get('user_id')): ?>
            <form method="post" action="<?= site_url('/chat/notes/delete/' . $note['id']) ?>" style="display:inline" onsubmit="return confirm('¿Eliminar esta nota?')">
              <?= csrf_field() ?>
              <button class="note-delete-btn" title="Eliminar"><i data-lucide="trash-2" style="width:11px;height:11px"></i></button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── PANEL DERECHO: CHAT ── -->
  <div class="chat-panel">
    <div id="chat-messages" class="chat-messages">
      <?php foreach ($messages as $m): ?>
        <?php $isMe = $m['user_id'] == session()->get('user_id'); ?>
        <div class="chat-msg <?= $isMe ? 'chat-msg--me' : 'chat-msg--them' ?>" data-id="<?= $m['id'] ?>">
          <div class="chat-avatar-wrap">
            <?php if (!empty($m['avatar_url'])): ?>
              <img src="<?= base_url($m['avatar_url']) ?>" alt="<?= esc($m['username']) ?>" class="chat-avatar-img" title="<?= esc($m['username']) ?>">
            <?php else: ?>
              <div class="chat-avatar-initials" title="<?= esc($m['username']) ?>"><?= strtoupper(substr($m['username'], 0, 1)) ?></div>
            <?php endif; ?>
          </div>
          <div class="chat-bubble <?= $isMe ? 'chat-bubble--me' : 'chat-bubble--them' ?>">
            <?php if (!$isMe): ?>
              <div class="chat-name"><?= esc($m['username']) ?></div>
            <?php endif; ?>
            <div class="chat-text"><?= nl2br(esc($m['message'])) ?></div>
            <div class="chat-time"><?= date('H:i', strtotime($m['created_at'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
        <div class="empty-state" style="margin:auto">
          <div class="icon"><i data-lucide="message-circle" style="width:36px;height:36px;color:var(--muted)"></i></div>
          <h3>Sin mensajes aún</h3>
          <p>Sé el primero en escribir</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="chat-input-wrap">
      <form id="chat-form" method="post" action="<?= site_url('/chat/send') ?>" style="display:flex;gap:10px;align-items:flex-end">
        <?= csrf_field() ?>
        <textarea name="message" id="chat-input" rows="1" placeholder="Escribe un mensaje..."
          class="chat-textarea" onkeydown="handleKey(event)"></textarea>
        <button type="submit" class="btn btn-primary" style="height:42px;flex-shrink:0;padding:0 18px">
          <i data-lucide="send" style="width:15px;height:15px"></i>
        </button>
      </form>
    </div>
  </div>

</div>

<style>
/* Layout dos columnas */
.chat-layout {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 20px;
  height: calc(100vh - 145px);
}

/* Panel notas */
.notes-panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 0;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.notes-header {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 14px;
  display: flex;
  align-items: center;
  gap: 7px;
}

.notes-header [data-lucide] { width: 15px; height: 15px; color: var(--text-secondary); }

.note-form .note-textarea {
  width: 100%;
  resize: none;
  font-size: 0.855rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 9px 12px;
  background: var(--surface2);
  font-family: inherit;
  line-height: 1.5;
  outline: none;
  transition: border-color .15s;
}
.note-form .note-textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}

.notes-list {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-right: 2px;
}

.note-item {
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 10px 12px;
  transition: box-shadow .15s;
}
.note-item:hover { box-shadow: var(--shadow-sm); }

.note-content {
  font-size: 0.855rem;
  color: var(--text);
  line-height: 1.55;
  margin-bottom: 6px;
  word-break: break-word;
}

.note-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.7rem;
  color: var(--muted);
}

.note-delete-btn {
  background: none;
  border: none;
  color: var(--muted);
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  transition: color .15s, background .15s;
}
.note-delete-btn:hover { color: var(--danger); background: #FEF2F2; }

/* Panel chat */
.chat-panel {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 4px 2px 16px;
  scroll-behavior: smooth;
}

/* Message rows */
.chat-msg { display: flex; align-items: flex-end; gap: 8px; max-width: 80%; }
.chat-msg--them { align-self: flex-start; }
.chat-msg--me   { align-self: flex-end; flex-direction: row-reverse; }

/* Avatar */
.chat-avatar-wrap { flex-shrink: 0; }
.chat-avatar-img {
  width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
  display: block; border: 2px solid var(--border);
}
.chat-avatar-initials {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--primary); color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem; font-weight: 700; flex-shrink: 0;
  border: 2px solid var(--border);
}
.chat-msg--me .chat-avatar-initials { background: var(--primary-hover); }

/* Bubble */
.chat-bubble { padding: 10px 14px; border-radius: 18px; position: relative; word-break: break-word; }
.chat-bubble--them {
  background: var(--surface); border: 1px solid var(--border);
  border-bottom-left-radius: 4px; box-shadow: var(--shadow-sm);
}
.chat-bubble--me {
  background: var(--primary); color: #fff;
  border-bottom-right-radius: 4px; box-shadow: 0 2px 8px rgba(37,99,235,0.25);
}
.chat-name { font-size: 0.72rem; font-weight: 700; color: var(--primary); margin-bottom: 4px; }
.chat-text { font-size: 0.875rem; line-height: 1.55; }
.chat-bubble--me .chat-text { color: #fff; }
.chat-time { font-size: 0.63rem; opacity: .55; margin-top: 4px; text-align: right; }
.chat-bubble--me .chat-time { color: rgba(255,255,255,.75); opacity: 1; }

/* Input */
.chat-input-wrap {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 14px; padding: 10px 14px; margin-top: 10px;
  box-shadow: var(--shadow-sm);
}
.chat-textarea {
  flex: 1; resize: none; min-height: 40px; max-height: 120px;
  overflow-y: auto; border-radius: 10px; padding: 9px 13px;
  font-size: 0.875rem; border: 1px solid var(--border);
  background: var(--surface2); font-family: inherit; line-height: 1.5;
  outline: none; transition: border-color .15s; width: 100%;
}
.chat-textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

@media (max-width: 768px) {
  .chat-layout { grid-template-columns: 1fr; height: auto; }
  .chat-panel  { height: calc(100vh - 145px); }
}
</style>

<script>
const ME_ID    = <?= (int) session()->get('user_id') ?>;
const POLL_URL = '<?= site_url('/chat/poll') ?>';
const BASE_URL = '<?= base_url() ?>';
let lastId     = <?= !empty($messages) ? (int) end($messages)['id'] : 0 ?>;

function scrollBottom() {
  const el = document.getElementById('chat-messages');
  el.scrollTop = el.scrollHeight;
}

function avatarHtml(m) {
  if (m.avatar_url) {
    return `<div class="chat-avatar-wrap"><img src="${BASE_URL}${escHtml(m.avatar_url)}" class="chat-avatar-img" title="${escHtml(m.username)}"></div>`;
  }
  return `<div class="chat-avatar-wrap"><div class="chat-avatar-initials" title="${escHtml(m.username)}">${escHtml(m.username[0].toUpperCase())}</div></div>`;
}

function buildMsg(m) {
  const isMe = m.user_id == ME_ID;
  const cls  = isMe ? 'chat-msg--me' : 'chat-msg--them';
  const bcls = isMe ? 'chat-bubble--me' : 'chat-bubble--them';
  const time = new Date(m.created_at.replace(' ', 'T')).toLocaleTimeString('es', {hour:'2-digit', minute:'2-digit'});
  const name = isMe ? '' : `<div class="chat-name">${escHtml(m.username)}</div>`;
  return `<div class="chat-msg ${cls}" data-id="${m.id}">
    ${avatarHtml(m)}
    <div class="chat-bubble ${bcls}">
      ${name}
      <div class="chat-text">${escHtml(m.message).replace(/\n/g,'<br>')}</div>
      <div class="chat-time">${time}</div>
    </div>
  </div>`;
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function poll() {
  fetch(`${POLL_URL}?after=${lastId}`)
    .then(r => r.json())
    .then(data => {
      if (data.messages && data.messages.length > 0) {
        const container = document.getElementById('chat-messages');
        const empty = container.querySelector('.empty-state');
        if (empty) empty.remove();
        const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 80;
        data.messages.forEach(m => {
          container.insertAdjacentHTML('beforeend', buildMsg(m));
          lastId = Math.max(lastId, parseInt(m.id));
        });
        if (wasAtBottom) scrollBottom();
      }
    })
    .catch(() => {});
}

document.getElementById('chat-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const text = input.value.trim();
  if (!text) return;
  const fd = new FormData(this);
  fetch(this.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(() => { input.value = ''; input.style.height = 'auto'; poll(); })
    .catch(() => {});
});

const input = document.getElementById('chat-input');
input.addEventListener('input', () => {
  input.style.height = 'auto';
  input.style.height = Math.min(input.scrollHeight, 120) + 'px';
});

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    if (input.value.trim()) document.getElementById('chat-form').dispatchEvent(new Event('submit'));
  }
}

scrollBottom();
setInterval(poll, 3000);
</script>

<?= view('layouts/footer') ?>
