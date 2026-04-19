<?= view('layouts/header') ?>

<!-- Mobile tab switcher (hidden on desktop) -->
<div class="chat-tabs">
  <button class="chat-tab active" id="tab-chat" onclick="switchTab('chat')">
    <i data-lucide="message-circle" style="width:14px;height:14px"></i> <?= lang('App.chat_tab') ?>
  </button>
  <button class="chat-tab" id="tab-notes" onclick="switchTab('notes')">
    <i data-lucide="sticky-note" style="width:14px;height:14px"></i> <?= lang('App.notes_tab') ?>
  </button>
</div>

<div class="chat-layout">

  <!-- ── PANEL IZQUIERDO: NOTAS ── -->
  <div class="notes-panel">
    <div class="notes-header">
      <span class="card-title"><i data-lucide="sticky-note"></i> <?= lang('App.notes_title') ?></span>
    </div>

    <!-- Formulario añadir nota -->
    <form method="post" action="<?= site_url('/chat/notes/store') ?>" class="note-form">
      <?= csrf_field() ?>
      <textarea name="content" placeholder="<?= lang('App.notes_placeholder') ?>" class="note-textarea" maxlength="500" rows="3"></textarea>
      <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:8px">
        <i data-lucide="plus" style="width:13px;height:13px"></i> <?= lang('App.notes_add') ?>
      </button>
    </form>

    <div class="divider"></div>

    <!-- Lista de notas -->
    <div class="notes-list">
      <?php if (empty($notes)): ?>
        <div style="text-align:center;color:var(--muted);font-size:0.82rem;padding:20px 0">
          <i data-lucide="file-text" style="width:28px;height:28px;opacity:.4;display:block;margin:0 auto 8px"></i>
          <?= lang('App.notes_empty') ?>
        </div>
      <?php else: ?>
        <?php foreach ($notes as $note): ?>
        <div class="note-item" data-note-id="<?= $note['id'] ?>">
          <div class="note-content"><?= nl2br(esc($note['content'])) ?></div>
          <div class="note-meta">
            <span data-ts="<?= strtotime($note['created_at']) ?>"><?= esc($note['username']) ?> · <?= date('d/m H:i', strtotime($note['created_at'])) ?></span>
            <?php if ($note['user_id'] == session()->get('user_id')): ?>
            <button class="note-delete-btn" onclick="deleteNote(<?= $note['id'] ?>, this)" title="Eliminar">
              <i data-lucide="trash-2" style="width:11px;height:11px"></i>
            </button>
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
            <div class="chat-text" data-raw="<?= esc($m['message']) ?>"><?= nl2br(esc($m['message'])) ?></div>
            <?php if (!empty($m['edited'])): ?><span class="chat-edited">(editado)</span><?php endif; ?>
            <div class="chat-time" data-ts="<?= strtotime($m['created_at']) ?>"></div>
          </div>
          <?php if ($isMe): ?>
          <div class="msg-actions">
            <button class="msg-edit-btn" onclick="editMessage(<?= $m['id'] ?>, this)" title="Editar">
              <i data-lucide="pencil" style="width:11px;height:11px"></i>
            </button>
            <button class="msg-delete-btn" onclick="deleteMessage(<?= $m['id'] ?>, this)" title="Eliminar">
              <i data-lucide="trash-2" style="width:11px;height:11px"></i>
            </button>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
        <div class="empty-state" style="margin:auto">
          <div class="icon"><i data-lucide="message-circle" style="width:36px;height:36px;color:var(--muted)"></i></div>
          <h3><?= lang('App.chat_empty') ?></h3>
          <p><?= lang('App.chat_empty_sub') ?></p>
        </div>
      <?php endif; ?>
    </div>

    <div class="chat-input-wrap">
      <form id="chat-form" method="post" action="<?= site_url('/chat/send') ?>" style="display:flex;gap:10px;align-items:flex-end">
        <?= csrf_field() ?>
        <textarea name="message" id="chat-input" rows="1" placeholder="<?= lang('App.chat_placeholder') ?>"
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

/* Action buttons on own messages */
.msg-actions {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-self: center;
  flex-shrink: 0;
}
.msg-edit-btn, .msg-delete-btn {
  display: flex; align-items: center; justify-content: center;
  width: 24px; height: 24px;
  background: none; border: none; border-radius: 50%;
  color: var(--muted); opacity: 0; cursor: pointer;
  transition: color .15s, background .15s, opacity .15s; padding: 0;
}
.msg-edit-btn:hover  { color: var(--primary); background: rgba(37,99,235,0.1); opacity: 1 !important; }
.msg-delete-btn:hover { color: var(--danger); background: rgba(239,68,68,0.1); opacity: 1 !important; }
.chat-msg--me:hover .msg-edit-btn,
.chat-msg--me:hover .msg-delete-btn { opacity: 0.5; }
@media (hover: none) {
  .msg-edit-btn, .msg-delete-btn { opacity: 0.4; }
}

/* Inline edit area inside bubble */
.msg-edit-wrap { margin-top: 6px; }
.msg-edit-ta {
  width: 100%; resize: none; border-radius: 8px;
  padding: 6px 10px; font-size: 0.875rem;
  border: 1px solid rgba(255,255,255,0.4);
  background: rgba(255,255,255,0.15); color: #fff;
  font-family: inherit; line-height: 1.5; outline: none;
  box-sizing: border-box;
}
.msg-edit-ta:focus { border-color: rgba(255,255,255,0.8); }
.chat-bubble--them .msg-edit-ta {
  border-color: var(--border); background: var(--surface2); color: var(--text);
}
.chat-bubble--them .msg-edit-ta:focus { border-color: var(--primary); }
.chat-edited { font-size: 0.6rem; opacity: 0.6; margin-right: 4px; font-style: italic; }

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

/* Mobile tab switcher */
.chat-tabs {
  display: none;
  gap: 0;
  margin-bottom: 12px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 4px;
  box-shadow: var(--shadow-sm);
}
.chat-tab {
  flex: 1;
  padding: 9px 12px;
  border: none;
  border-radius: calc(var(--radius) - 3px);
  background: transparent;
  color: var(--text-secondary);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s, color .15s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.chat-tab.active {
  background: var(--primary);
  color: #fff;
}

@media (max-width: 768px) {
  .chat-tabs   { display: flex; }
  .chat-layout { grid-template-columns: 1fr; height: auto; }
  .chat-panel  { height: calc(100vh - 210px); }
  .notes-panel.hidden-mobile { display: none; }
  .chat-panel.hidden-mobile  { display: none; }
}
</style>

<script>
const ME_ID    = <?= (int) session()->get('user_id') ?>;
const POLL_URL = '<?= site_url('/chat/poll') ?>';
const BASE_URL = '<?= base_url() ?>';
let lastId     = <?= !empty($messages) ? (int) end($messages)['id'] : 0 ?>;
const CHAT_L = {
  deleteMsg:   '<?= addslashes(lang('App.chat_delete_msg') ?: '¿Eliminar este mensaje?') ?>',
  deleteNote:  '<?= addslashes(lang('App.chat_delete_note') ?: '¿Eliminar esta nota?') ?>',
  notesEmpty:  '<?= addslashes(lang('App.notes_empty')) ?>',
  save:        '<?= addslashes(lang('App.save')) ?>',
  cancel:      '<?= addslashes(lang('App.cancel')) ?>',
};

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

const DEL_MSG_URL   = '<?= site_url('/chat/message/delete/') ?>';
const EDIT_MSG_URL  = '<?= site_url('/chat/message/edit/') ?>';
const NOTE_STORE_URL = '<?= site_url('/chat/notes/store') ?>';
const NOTE_DEL_URL   = '<?= site_url('/chat/notes/delete/') ?>';

function buildMsg(m) {
  const isMe = m.user_id == ME_ID;
  const cls  = isMe ? 'chat-msg--me' : 'chat-msg--them';
  const bcls = isMe ? 'chat-bubble--me' : 'chat-bubble--them';
  const time = m.ts ? new Date(m.ts * 1000).toLocaleTimeString('es', {hour:'2-digit', minute:'2-digit'}) : '';
  const name = isMe ? '' : `<div class="chat-name">${escHtml(m.username)}</div>`;
  const edited = m.edited && m.edited != '0' ? '<span class="chat-edited">(editado)</span>' : '';
  const svgTrash  = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
  const svgPencil = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`;
  const actBtns = isMe ? `<div class="msg-actions">
    <button class="msg-edit-btn" onclick="editMessage(${m.id}, this)" title="Editar">${svgPencil}</button>
    <button class="msg-delete-btn" onclick="deleteMessage(${m.id}, this)" title="Eliminar">${svgTrash}</button>
  </div>` : '';
  return `<div class="chat-msg ${cls}" data-id="${m.id}">
    ${avatarHtml(m)}
    <div class="chat-bubble ${bcls}">
      ${name}
      <div class="chat-text" data-raw="${escHtml(m.message)}">${escHtml(m.message).replace(/\n/g,'<br>')}</div>
      ${edited}
      <div class="chat-time">${time}</div>
    </div>
    ${actBtns}
  </div>`;
}

function deleteMessage(id, btn) {
  showConfirm(CHAT_L.deleteMsg, () => _doDeleteMessage(id, btn));
}
function _doDeleteMessage(id, btn) {
  const fd = new FormData();
  fd.append('<?= csrf_token() ?>', document.querySelector('[name="<?= csrf_token() ?>"]').value);
  fetch(`${DEL_MSG_URL}${id}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const row = btn.closest('.chat-msg');
        row.style.transition = 'opacity .2s';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 200);
      }
    })
    .catch(() => {});
}

function editMessage(id, btn) {
  const bubble  = btn.closest('.chat-msg').querySelector('.chat-bubble');
  const textEl  = bubble.querySelector('.chat-text');
  textEl.style.display = 'none';

  let wrap = bubble.querySelector('.msg-edit-wrap');
  if (!wrap) {
    wrap = document.createElement('div');
    wrap.className = 'msg-edit-wrap';
    const ta = document.createElement('textarea');
    ta.className = 'msg-edit-ta';
    ta.rows = 2;
    const acts = document.createElement('div');
    acts.style.cssText = 'display:flex;gap:6px;margin-top:6px';
    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn btn-sm btn-primary';
    saveBtn.textContent = CHAT_L.save;
    saveBtn.onclick = () => saveEdit(id, wrap);
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-sm btn-secondary';
    cancelBtn.textContent = CHAT_L.cancel;
    cancelBtn.onclick = () => cancelEdit(wrap);
    acts.appendChild(saveBtn);
    acts.appendChild(cancelBtn);
    wrap.appendChild(ta);
    wrap.appendChild(acts);
    textEl.insertAdjacentElement('afterend', wrap);
  } else {
    wrap.style.display = '';
  }
  const ta = wrap.querySelector('.msg-edit-ta');
  ta.value = textEl.dataset.raw || '';
  ta.focus();
}

function cancelEdit(wrap) {
  wrap.style.display = 'none';
  wrap.closest('.chat-bubble').querySelector('.chat-text').style.display = '';
}

function saveEdit(id, wrap) {
  const ta   = wrap.querySelector('.msg-edit-ta');
  const text = ta.value.trim();
  if (!text) return;
  const bubble = wrap.closest('.chat-bubble');
  const textEl = bubble.querySelector('.chat-text');
  const csrf   = document.querySelector('[name="<?= csrf_token() ?>"]').value;
  const fd = new FormData();
  fd.append('<?= csrf_token() ?>', csrf);
  fd.append('message', text);
  fetch(`${EDIT_MSG_URL}${id}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        textEl.dataset.raw = text;
        textEl.innerHTML = escHtml(text).replace(/\n/g, '<br>');
        textEl.style.display = '';
        wrap.style.display = 'none';
        if (!bubble.querySelector('.chat-edited')) {
          wrap.insertAdjacentHTML('beforebegin', '<span class="chat-edited">(editado)</span>');
        }
      }
    })
    .catch(() => {});
}

// ── Notes ──────────────────────────────────────────────
function buildNote(note) {
  const d = new Date(note.ts * 1000);
  const time = d.toLocaleDateString('es', {day:'2-digit', month:'2-digit'}) + ' ' +
               d.toLocaleTimeString('es', {hour:'2-digit', minute:'2-digit'});
  const svgTrash = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>`;
  const delBtn = note.user_id == ME_ID
    ? `<button class="note-delete-btn" onclick="deleteNote(${note.id}, this)" title="Eliminar">${svgTrash}</button>`
    : '';
  return `<div class="note-item" data-note-id="${note.id}">
    <div class="note-content">${escHtml(note.content).replace(/\n/g,'<br>')}</div>
    <div class="note-meta">
      <span>${escHtml(note.username)} · ${time}</span>
      ${delBtn}
    </div>
  </div>`;
}

function deleteNote(id, btn) {
  showConfirm(CHAT_L.deleteNote, () => _doDeleteNote(id, btn));
}
function _doDeleteNote(id, btn) {
  const csrf = document.querySelector('[name="<?= csrf_token() ?>"]').value;
  const fd = new FormData();
  fd.append('<?= csrf_token() ?>', csrf);
  fetch(`${NOTE_DEL_URL}${id}`, { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'} })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        const item = btn.closest('.note-item');
        item.style.transition = 'opacity .2s';
        item.style.opacity = '0';
        setTimeout(() => {
          item.remove();
          const list = document.querySelector('.notes-list');
          if (!list.querySelector('.note-item')) {
            list.innerHTML = `<div style="text-align:center;color:var(--muted);font-size:0.82rem;padding:20px 0"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 8px;opacity:.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>${CHAT_L.notesEmpty}</div>`;
          }
        }, 200);
      }
    })
    .catch(() => {});
}

document.querySelector('.note-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const ta = this.querySelector('textarea[name="content"]');
  const text = ta.value.trim();
  if (!text) return;
  const fd = new FormData(this);
  fetch(NOTE_STORE_URL, { method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'} })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        ta.value = '';
        const list = document.querySelector('.notes-list');
        const empty = list.querySelector('div[style*="text-align:center"]');
        if (empty) empty.remove();
        list.insertAdjacentHTML('afterbegin', buildNote(data.note));
      }
    })
    .catch(() => {});
});

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
      if (data.notes) updateNotes(data.notes);
    })
    .catch(() => {});
}

function updateNotes(notes) {
  const list = document.querySelector('.notes-list');
  const currentIds = [...list.querySelectorAll('.note-item')].map(el => el.dataset.noteId).join(',');
  const serverIds  = notes.map(n => String(n.id)).join(',');
  if (currentIds === serverIds) return;
  if (notes.length === 0) {
    list.innerHTML = `<div style="text-align:center;color:var(--muted);font-size:0.82rem;padding:20px 0"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 8px;opacity:.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>${CHAT_L.notesEmpty}</div>`;
  } else {
    list.innerHTML = notes.map(buildNote).join('');
  }
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

// Convertir todos los tiempos PHP a hora local del navegador
document.querySelectorAll('.chat-time[data-ts]').forEach(el => {
  const d = new Date(parseInt(el.dataset.ts) * 1000);
  el.textContent = d.toLocaleTimeString('es', {hour: '2-digit', minute: '2-digit'});
});
document.querySelectorAll('.note-meta span[data-ts]').forEach(el => {
  const d = new Date(parseInt(el.dataset.ts) * 1000);
  const username = el.textContent.split(' · ')[0];
  const time = d.toLocaleDateString('es', {day:'2-digit', month:'2-digit'}) + ' ' +
               d.toLocaleTimeString('es', {hour:'2-digit', minute:'2-digit'});
  el.textContent = username + ' · ' + time;
});

scrollBottom();
setInterval(poll, 3000);

function switchTab(tab) {
  const notes = document.querySelector('.notes-panel');
  const chat  = document.querySelector('.chat-panel');
  const tabChat  = document.getElementById('tab-chat');
  const tabNotes = document.getElementById('tab-notes');
  if (tab === 'chat') {
    notes.classList.add('hidden-mobile');
    chat.classList.remove('hidden-mobile');
    tabChat.classList.add('active');
    tabNotes.classList.remove('active');
    scrollBottom();
  } else {
    chat.classList.add('hidden-mobile');
    notes.classList.remove('hidden-mobile');
    tabNotes.classList.add('active');
    tabChat.classList.remove('active');
  }
}

// On mobile, start with chat visible and notes hidden
if (window.innerWidth <= 768) {
  document.querySelector('.notes-panel').classList.add('hidden-mobile');
}
</script>

<?= view('layouts/footer') ?>
