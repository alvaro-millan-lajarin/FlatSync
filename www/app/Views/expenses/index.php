<?= view('layouts/header') ?>

<!-- Stats row -->
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card accent">
    <div class="stat-icon"><i data-lucide="wallet"></i></div>
    <div id="stat-month-total" class="stat-value">€<?= number_format($monthTotal, 2) ?></div>
    <div class="stat-label"><?= $filterMonth ? lang('App.expenses_total') : lang('App.expenses_total_all') ?></div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon"><i data-lucide="user"></i></div>
    <div id="stat-my-paid" class="stat-value">€<?= number_format($myPaid, 2) ?></div>
    <div class="stat-label"><?= lang('App.expenses_my_paid') ?></div>
  </div>
  <div id="stat-card-balance" class="stat-card <?= $myBalance > 0 ? 'success' : ($myBalance < 0 ? 'danger' : 'accent') ?>">
    <div class="stat-icon"><i id="stat-icon-balance" data-lucide="<?= $myBalance >= 0 ? 'trending-up' : 'trending-down' ?>"></i></div>
    <div id="stat-balance" class="stat-value"><?= $myBalance >= 0 ? '+' : '-' ?>€<?= number_format(abs($myBalance), 2) ?></div>
    <div id="stat-label-balance" class="stat-label"><?= $myBalance > 0 ? lang('App.expenses_owe_you') : ($myBalance < 0 ? lang('App.expenses_you_owe') : lang('App.expenses_settled')) ?></div>
  </div>
</div>

<!-- Filter bar -->
<div class="card" style="margin-bottom:20px">
  <form method="get" action="<?= site_url('/expenses') ?>" class="expense-filter-form">
    <div class="form-group expense-filter-field">
      <label><?= lang('App.expenses_month') ?></label>
      <input type="month" name="month" value="<?= $filterMonth ?>">
    </div>
    <div class="form-group expense-filter-field">
      <label><?= lang('App.expenses_category') ?></label>
      <select name="category">
        <option value=""><?= lang('App.all_f') ?></option>
        <option value="food" <?= $filterCategory === 'food' ? 'selected' : '' ?>><?= lang('App.cat_food') ?></option>
        <option value="cleaning" <?= $filterCategory === 'cleaning' ? 'selected' : '' ?>><?= lang('App.cat_cleaning') ?></option>
        <option value="bills" <?= $filterCategory === 'bills' ? 'selected' : '' ?>><?= lang('App.cat_bills') ?></option>
        <option value="other" <?= $filterCategory === 'other' ? 'selected' : '' ?>><?= lang('App.cat_other') ?></option>
      </select>
    </div>
    <div class="form-group expense-filter-field">
      <label><?= lang('App.expenses_paid_by') ?></label>
      <select name="paid_by">
        <option value=""><?= lang('App.all') ?></option>
        <?php foreach ($members as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $filterPaidBy == $m['id'] ? 'selected' : '' ?>><?= esc($m['username']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="expense-filter-actions">
      <button type="submit" class="btn btn-secondary"><?= lang('App.filter') ?></button>
      <a href="<?= site_url('/expenses') ?>" class="btn btn-secondary"><i data-lucide="x" style="width:13px;height:13px"></i> <?= lang('App.clear') ?></a>
    </div>
  </form>
</div>

<!-- Expenses table -->
<div class="card">
  <div class="card-header expense-card-header">
    <span class="card-title"><?= lang('App.expenses_history') ?></span>
    <div style="display:flex;gap:8px">
      <button class="btn btn-primary" onclick="openModal('modal-add-expense')"><i data-lucide="plus" style="width:14px;height:14px"></i> <?= lang('App.expenses_add') ?></button>
      <a href="<?= site_url('/expenses/export') ?>" class="btn btn-sm btn-secondary"><i data-lucide="download" style="width:13px;height:13px"></i> <?= lang('App.export') ?></a>
    </div>
  </div>

  <?php if (empty($expenses)): ?>
    <div id="expense-empty-state" class="empty-state"><div class="icon"><i data-lucide="receipt" style="width:32px;height:32px;color:var(--muted)"></i></div><h3><?= lang('App.expenses_empty') ?></h3><p><?= lang('App.expenses_empty_sub') ?></p></div>
  <?php else: ?>
  <?php
    $cats = ['food'=>lang('App.cat_food'),'cleaning'=>lang('App.cat_cleaning'),'bills'=>lang('App.cat_bills'),'other'=>lang('App.cat_other')];
    $grouped = [];
    foreach ($expenses as $e) { $grouped[$e['date']][] = $e; }
    $today     = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
  ?>
  <div id="expense-list" style="display:flex;flex-direction:column;gap:20px">
    <?php foreach ($grouped as $date => $dayExpenses): ?>
    <div id="expense-group-<?= $date ?>">
      <!-- Separador de fecha -->
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
        <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);white-space:nowrap">
          <?php
            $ts = strtotime($date);
            if ($date === $today)         echo lang('App.today') . ' · ' . date('d/m/Y', $ts);
            elseif ($date === $yesterday) echo lang('App.yesterday') . ' · ' . date('d/m/Y', $ts);
            else                          echo date('d/m/Y', $ts);
          ?>
        </span>
        <div style="flex:1;height:1px;background:var(--divider)"></div>
      </div>

      <!-- Filas de gastos -->
      <div id="expense-rows-<?= $date ?>" class="expense-rows">
        <?php foreach ($dayExpenses as $e): ?>
        <div style="display:flex;align-items:center;gap:16px;padding:12px 4px">
          <!-- Título -->
          <div style="flex:2;min-width:0">
            <div style="font-weight:500;font-size:0.9rem"><?= esc($e['title']) ?></div>
            <?php if ($e['description']): ?>
              <div style="font-size:0.75rem;color:var(--muted);margin-top:1px"><?= esc($e['description']) ?></div>
            <?php endif; ?>
            <?php if (!empty($e['split_with'])): ?>
              <?php
                $participantIds = json_decode($e['split_with'], true);
                $participantNames = array_filter(array_map(
                    fn($m) => in_array((int)$m['id'], array_map('intval', $participantIds)) ? esc($m['username']) : null,
                    $members
                ));
              ?>
              <div style="font-size:0.7rem;color:var(--primary);margin-top:2px">
                <i data-lucide="users" style="width:10px;height:10px;vertical-align:middle"></i>
                <?= implode(', ', $participantNames) ?>
              </div>
            <?php endif; ?>
          </div>
          <!-- Categoría -->
          <div class="expense-col-category" style="flex:0 0 90px">
            <span class="badge badge-accent"><?= $cats[$e['category']] ?? esc($e['category']) ?></span>
          </div>
          <!-- Importe -->
          <div class="expense-col-amount" style="flex:0 0 80px;text-align:right;font-size:1rem;font-weight:700;color:var(--primary)">
            €<?= number_format($e['amount'], 2) ?>
          </div>
          <!-- Pagado por -->
          <div class="expense-col-person" style="flex:0 0 130px;display:flex;align-items:center;gap:6px">
            <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($e['paid_by_name'], 0, 1)) ?></div>
            <span style="font-size:0.855rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($e['paid_by_name']) ?></span>
          </div>
          <!-- Acciones -->
          <div style="flex:0 0 100px;display:flex;gap:6px;justify-content:flex-end;align-items:center">
            <?php if ($e['receipt_image']): ?>
            <a href="<?= base_url('uploads/' . $e['receipt_image']) ?>" download title="Descargar recibo"
               class="btn btn-sm btn-secondary btn-icon">
              <i data-lucide="paperclip" style="width:13px;height:13px"></i>
            </a>
            <?php endif; ?>
            <?php if ($e['paid_by'] == session()->get('user_id')): ?>
            <button class="btn btn-sm btn-secondary btn-icon" onclick="openEditModal(this)" data-expense="<?= htmlspecialchars(json_encode($e, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG), ENT_QUOTES) ?>" title="Editar"><i data-lucide="pencil" style="width:13px;height:13px"></i></button>
            <form method="post" action="<?= site_url('/expenses/delete/' . $e['id']) ?>" data-confirm="¿Eliminar «<?= esc(addslashes($e['title'])) ?>»? Esta acción no se puede deshacer.">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-danger btn-icon" title="Eliminar"><i data-lucide="trash-2" style="width:13px;height:13px"></i></button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal: Add expense -->
<div class="modal-overlay" id="modal-add-expense">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="plus-circle" style="width:18px;height:18px;color:var(--primary)"></i> <?= lang('App.expenses_add_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-add-expense')">×</button>
    </div>
    <form method="post" action="<?= site_url('/expenses/store') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group">
        <label><?= lang('App.expenses_description') ?></label>
        <input type="text" name="title" required placeholder="Ej: Papel higiénico, Cena compartida...">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><?= lang('App.expenses_amount') ?></label>
          <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00">
        </div>
        <div class="form-group">
          <label><?= lang('App.expenses_category') ?></label>
          <select name="category">
            <option value="food"><?= lang('App.cat_food') ?></option>
            <option value="cleaning"><?= lang('App.cat_cleaning') ?></option>
            <option value="bills"><?= lang('App.cat_bills') ?></option>
            <option value="other"><?= lang('App.cat_other') ?></option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><?= lang('App.expenses_paid_by') ?></label>
          <select name="paid_by" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $m['id'] == session()->get('user_id') ? 'selected' : '' ?>><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label><?= lang('App.expenses_date') ?></label>
          <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label><?= lang('App.expenses_notes') ?></label>
        <textarea name="description" placeholder="Detalles adicionales..."></textarea>
      </div>
      <div class="form-group">
        <label><?= lang('App.expenses_split') ?></label>
        <div class="split-checks">
          <?php foreach ($members as $m): ?>
          <label class="split-check-label">
            <input type="checkbox" name="split_with[]" value="<?= $m['id'] ?>" checked>
            <span><?= esc($m['username']) ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="form-group">
        <label><i data-lucide="paperclip" style="width:13px;height:13px"></i> <?= lang('App.expenses_receipt') ?> <small style="color:var(--muted)">(<?= lang('App.optional') ?>)</small></label>
        <input type="file" name="receipt_image" accept="image/*,.pdf">
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center"><?= lang('App.expenses_save') ?></button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-expense')"><?= lang('App.cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit expense -->
<div class="modal-overlay" id="modal-edit-expense">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="pencil" style="width:18px;height:18px;color:var(--primary)"></i> <?= lang('App.expenses_edit_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-edit-expense')">×</button>
    </div>
    <form method="post" id="form-edit-expense" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group">
        <label><?= lang('App.expenses_description') ?></label>
        <input type="text" name="title" id="edit-title" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><?= lang('App.expenses_amount') ?></label>
          <input type="number" name="amount" id="edit-amount" required step="0.01">
        </div>
        <div class="form-group">
          <label><?= lang('App.expenses_category') ?></label>
          <select name="category" id="edit-category">
            <option value="food"><?= lang('App.cat_food') ?></option>
            <option value="cleaning"><?= lang('App.cat_cleaning') ?></option>
            <option value="bills"><?= lang('App.cat_bills') ?></option>
            <option value="other"><?= lang('App.cat_other') ?></option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label><?= lang('App.expenses_date') ?></label>
        <input type="date" name="date" id="edit-date" required>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center"><?= lang('App.save') ?></button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-expense')"><?= lang('App.cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(btn) {
  const expense = JSON.parse(btn.dataset.expense);
  document.getElementById('edit-title').value    = expense.title;
  document.getElementById('edit-amount').value   = expense.amount;
  document.getElementById('edit-category').value = expense.category;
  document.getElementById('edit-date').value     = expense.date;
  document.getElementById('form-edit-expense').action = '<?= site_url('/expenses/update/') ?>' + expense.id;
  openModal('modal-edit-expense');
}

// Envío AJAX del formulario de añadir gasto — igual que el chat con buildNote(data.note)
document.querySelector('#modal-add-expense form').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fetch(this.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        closeModal('modal-add-expense');
        this.reset();
        // Renderizar directamente desde la respuesta (como buildNote en el chat)
        if (data.expense) {
          injectExpenses([data.expense]);
          knownId = Math.max(knownId, parseInt(data.expense.id));
        }
        if (data.stats) updateStats(data.stats);
      } else if (data.error) {
        alert(data.error);
      }
    })
    .catch(() => {});
});
</script>

<style>
.expense-rows > div + div { border-top: 1px solid var(--divider); }
.split-checks { display:flex; flex-wrap:wrap; gap:8px; }
.split-check-label { display:flex; align-items:center; gap:5px; font-size:0.855rem; cursor:pointer;
  background:var(--surface2); border:1px solid var(--border); border-radius:20px; padding:4px 10px;
  transition:border-color .15s, background .15s; user-select:none; }
.split-check-label input { accent-color:var(--primary); }
.split-check-label:has(input:checked) { border-color:var(--primary); background:rgba(37,99,235,0.07); }
@media (max-width: 768px) {
  .expense-col-person  { flex: 0 0 auto !important; }
  .expense-col-person span { display: none; }
  .expense-col-amount  { flex: 0 0 60px !important; font-size: 0.9rem !important; }
  .expense-card-header { flex-direction: column !important; align-items: flex-start !important; gap: 10px !important; }
  .expense-card-header > div { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
}
@media (max-width: 480px) {
  .expense-col-category { display: none !important; }
  .expense-col-amount { flex: 0 0 52px !important; font-size: 0.85rem !important; }
}
</style>

<script>
const POLL_URL      = '<?= site_url('/expenses/poll') ?>';
const DEL_URL       = '<?= site_url('/expenses/delete/') ?>';
const BASE_URL      = '<?= base_url() ?>';
const CSRF_KEY      = '<?= csrf_token() ?>';
const CSRF_VAL      = '<?= csrf_hash() ?>';
const EDIT_URL_BASE = '<?= site_url('/expenses/update/') ?>';
const ME_ID         = <?= (int) session()->get('user_id') ?>;

  <?php $maxId = !empty($expenses) ? max(array_column($expenses, 'id')) : 0; ?>
  let knownId = <?= $maxId ?>;

  const CATS = {food:'<?= lang('App.cat_food') ?>', cleaning:'<?= lang('App.cat_cleaning') ?>', bills:'<?= lang('App.cat_bills') ?>', other:'<?= lang('App.cat_other') ?>'};

  function isModalOpen() {
    return !!document.querySelector('.modal-overlay.open');
  }

  function esc(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function jsonAttr(obj) {
    return JSON.stringify(obj)
      .replace(/&/g,'\\u0026').replace(/</g,'\\u003c').replace(/>/g,'\\u003e')
      .replace(/'/g,'\\u0027').replace(/"/g,'&quot;');
  }

  function dateLabel(dateStr) {
    const todayStr = new Date().toISOString().slice(0, 10);
    const yesterdayStr = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
    const d = new Date(dateStr + 'T00:00:00');
    const parts = d.toLocaleDateString('es-ES', {day:'2-digit', month:'2-digit', year:'numeric'});
    if (dateStr === todayStr)     return '<?= lang('App.today') ?> · ' + parts;
    if (dateStr === yesterdayStr) return '<?= lang('App.yesterday') ?> · ' + parts;
    return parts;
  }

  const MEMBERS_MAP = {<?php foreach ($members as $m): ?><?= $m['id'] ?>:'<?= esc($m['username']) ?>',<?php endforeach; ?>};

  function buildRow(e) {
    const catLabel = CATS[e.category] || esc(e.category);
    const initial  = (e.paid_by_name || '?').charAt(0).toUpperCase();
    const expAttr  = jsonAttr(e);
    const confirmMsg = esc('¿Eliminar «' + e.title + '»? Esta acción no se puede deshacer.');
    let splitHtml = '';
    if (e.split_with) {
      const ids = typeof e.split_with === 'string' ? JSON.parse(e.split_with) : e.split_with;
      const names = ids.map(id => MEMBERS_MAP[id] || id).filter(Boolean).join(', ');
      splitHtml = `<div style="font-size:0.7rem;color:var(--primary);margin-top:2px">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        ${esc(names)}
      </div>`;
    }
    return `
      <div style="display:flex;align-items:center;gap:16px;padding:12px 4px">
        <div style="flex:2;min-width:0">
          <div style="font-weight:500;font-size:0.9rem">${esc(e.title)}</div>
          ${e.description ? `<div style="font-size:0.75rem;color:var(--muted);margin-top:1px">${esc(e.description)}</div>` : ''}
          ${splitHtml}
        </div>
        <div class="expense-col-category" style="flex:0 0 90px">
          <span class="badge badge-accent">${catLabel}</span>
        </div>
        <div class="expense-col-amount" style="flex:0 0 80px;text-align:right;font-size:1rem;font-weight:700;color:var(--primary)">
          €${Number(e.amount).toFixed(2)}
        </div>
        <div class="expense-col-person" style="flex:0 0 130px;display:flex;align-items:center;gap:6px">
          <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0">${initial}</div>
          <span style="font-size:0.855rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(e.paid_by_name)}</span>
        </div>
        <div style="flex:0 0 100px;display:flex;gap:6px;justify-content:flex-end;align-items:center">
          ${e.receipt_image ? `<a href="${BASE_URL}uploads/${esc(e.receipt_image)}" download title="Descargar recibo" class="btn btn-sm btn-secondary btn-icon"><i data-lucide="paperclip" style="width:13px;height:13px"></i></a>` : ''}
          ${parseInt(e.paid_by) === ME_ID ? `
          <button class="btn btn-sm btn-secondary btn-icon" onclick="openEditModal(this)" data-expense="${expAttr}" title="Editar">
            <i data-lucide="pencil" style="width:13px;height:13px"></i>
          </button>
          <form method="post" action="${DEL_URL}${e.id}" data-confirm="${confirmMsg}">
            <input type="hidden" name="${CSRF_KEY}" value="${CSRF_VAL}">
            <button class="btn btn-sm btn-danger btn-icon" title="Eliminar">
              <i data-lucide="trash-2" style="width:13px;height:13px"></i>
            </button>
          </form>` : ''}
        </div>
      </div>`;
  }

  function updateStats(stats) {
    const totalEl  = document.getElementById('stat-month-total');
    const paidEl   = document.getElementById('stat-my-paid');
    const balEl    = document.getElementById('stat-balance');
    const balCard  = document.getElementById('stat-card-balance');
    const balIcon  = document.getElementById('stat-icon-balance');
    const balLabel = document.getElementById('stat-label-balance');
    if (totalEl) totalEl.textContent = '€' + Number(stats.monthTotal).toFixed(2);
    if (paidEl)  paidEl.textContent  = '€' + Number(stats.myPaid).toFixed(2);
    if (balEl) {
      const b = Number(stats.myBalance);
      balEl.textContent = (b >= 0 ? '+' : '-') + '€' + Math.abs(b).toFixed(2);
    }
    if (balCard) {
      const b = Number(stats.myBalance);
      balCard.className = 'stat-card ' + (b > 0 ? 'success' : b < 0 ? 'danger' : 'accent');
    }
    if (balIcon) {
      balIcon.setAttribute('data-lucide', Number(stats.myBalance) >= 0 ? 'trending-up' : 'trending-down');
    }
    if (balLabel) {
      const b = Number(stats.myBalance);
      balLabel.textContent = b > 0 ? '<?= lang('App.expenses_owe_you') ?>' : b < 0 ? '<?= lang('App.expenses_you_owe') ?>' : '<?= lang('App.expenses_settled') ?>';
    }
    if (window.lucide) window.lucide.createIcons();
  }

  function injectExpenses(newExpenses) {
    // Remove empty state and create list if needed
    const emptyState = document.getElementById('expense-empty-state');
    if (emptyState) {
      const listDiv = document.createElement('div');
      listDiv.id = 'expense-list';
      listDiv.style.cssText = 'display:flex;flex-direction:column;gap:20px';
      emptyState.replaceWith(listDiv);
    }

    const list = document.getElementById('expense-list');
    if (!list) return;

    // Group new expenses by date
    const byDate = {};
    newExpenses.forEach(e => {
      if (!byDate[e.date]) byDate[e.date] = [];
      byDate[e.date].push(e);
    });

    // Sort dates DESC so most recent appears first
    const sortedDates = Object.keys(byDate).sort().reverse();

    sortedDates.forEach(date => {
      const rowsHtml = byDate[date].map(buildRow).join('');
      let rowsEl = document.getElementById('expense-rows-' + date);

      if (rowsEl) {
        // Date group already exists — prepend new rows
        rowsEl.insertAdjacentHTML('afterbegin', rowsHtml);
      } else {
        // Create a new date group and insert at top of list
        const groupHtml = `
          <div id="expense-group-${date}">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);white-space:nowrap">
                ${dateLabel(date)}
              </span>
              <div style="flex:1;height:1px;background:var(--divider)"></div>
            </div>
            <div id="expense-rows-${date}" class="expense-rows">
              ${rowsHtml}
            </div>
          </div>`;
        list.insertAdjacentHTML('afterbegin', groupHtml);
      }
    });

    if (window.lucide) window.lucide.createIcons();
  }

function pollExpenses() {
  fetch(`${POLL_URL}?after=${knownId}`)
    .then(r => r.json())
    .then(data => {
      if (data.expenses && data.expenses.length > 0) {
        data.expenses.forEach(e => {
          knownId = Math.max(knownId, parseInt(e.id));
        });
        injectExpenses(data.expenses);
      }
    })
    .catch(() => {});
}

setInterval(pollExpenses, 3000);
</script>

<?= view('layouts/footer') ?>
