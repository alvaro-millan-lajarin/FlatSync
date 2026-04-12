<?= view('layouts/header') ?>

<?php
// Build tasks indexed by date for JS
$tasksByDate = [];
foreach ($calDays as $day) {
    if (!empty($day['tasks'])) {
        $tasksByDate[$day['date']] = $day['tasks'];
    }
}
?>

<!-- View tabs -->
<div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap;align-items:center">
  <a href="<?= site_url('/chores?view=week') ?>" class="btn <?= ($view==='week') ? 'btn-primary' : 'btn-secondary' ?>">
    <i data-lucide="calendar" style="width:14px;height:14px"></i> Semana
  </a>
  <a href="<?= site_url('/chores?view=month') ?>" class="btn <?= ($view==='month') ? 'btn-primary' : 'btn-secondary' ?>">
    <i data-lucide="calendar-days" style="width:14px;height:14px"></i> Mes
  </a>
  <button class="btn btn-primary" style="margin-left:auto" onclick="openModal('modal-add-chore')">
    <i data-lucide="plus" style="width:14px;height:14px"></i> Nueva tarea
  </button>
</div>

<!-- CALENDAR -->
<div class="card" id="calendar-card">
  <div class="card-header">
    <div style="display:flex;align-items:center;gap:12px">
      <a href="<?= site_url('/chores?view=' . $view . '&offset=' . ($offset - 1)) ?>" class="btn btn-sm btn-secondary">‹</a>
      <span class="card-title"><?= $calendarTitle ?></span>
      <a href="<?= site_url('/chores?view=' . $view . '&offset=' . ($offset + 1)) ?>" class="btn btn-sm btn-secondary">›</a>
    </div>
    <a href="<?= site_url('/chores?view=' . $view . '&offset=0') ?>" class="btn btn-sm btn-secondary">Hoy</a>
  </div>

  <div class="calendar-grid">
    <?php foreach (['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'] as $d): ?>
      <div class="cal-header"><?= $d ?></div>
    <?php endforeach; ?>

    <?php foreach ($calDays as $day): ?>
    <div class="cal-day <?= $day['today'] ? 'today' : '' ?> <?= $day['other_month'] ? 'other-month' : '' ?>"
         data-date="<?= $day['date'] ?>"
         onclick="selectDay('<?= $day['date'] ?>', this)">
      <div class="cal-day-num"><?= $day['num'] ?></div>
      <?php foreach ($day['tasks'] as $t): ?>
        <div class="cal-task" style="background:<?= $t['color'] ?? 'rgba(37,99,235,0.12)' ?>;color:<?= $t['text_color'] ?? 'var(--text)' ?>">
          <?= esc($t['task_name']) ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Day detail panel (shown when a day is clicked) -->
<div id="day-panel" style="display:none;margin-top:16px">
  <div class="card">
    <div class="card-header">
      <span class="card-title" id="day-panel-title"></span>
      <button class="btn btn-sm btn-secondary" onclick="closePanel()">
        <i data-lucide="x" style="width:13px;height:13px"></i>
      </button>
    </div>
    <div id="day-panel-body"></div>
  </div>
</div>

<!-- Missed chores -->
<?php if (!empty($missedChores)): ?>
<?php
  $missedGrouped = [];
  foreach ($missedChores as $c) { $missedGrouped[$c['due_date']][] = $c; }
  $today     = date('Y-m-d');
  $yesterday = date('Y-m-d', strtotime('-1 day'));
?>
<div class="card" style="margin-top:16px;border-color:rgba(239,68,68,0.3)">
  <div class="card-header">
    <span class="card-title" style="color:var(--danger)">
      <i data-lucide="alert-triangle" style="width:15px;height:15px;color:var(--danger)"></i> Tareas no realizadas
    </span>
  </div>

  <div style="display:flex;flex-direction:column;gap:20px">
    <?php foreach ($missedGrouped as $date => $choroes): ?>
    <div>
      <!-- Separador de fecha -->
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
        <span style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);white-space:nowrap">
          <?php
            $ts = strtotime($date);
            if ($date === $today)         echo 'Hoy · ' . date('d/m/Y', $ts);
            elseif ($date === $yesterday) echo 'Ayer · ' . date('d/m/Y', $ts);
            else                          echo ucfirst(date('l', $ts)) . ' · ' . date('d/m/Y', $ts);
          ?>
        </span>
        <div style="flex:1;height:1px;background:rgba(239,68,68,0.2)"></div>
      </div>

      <!-- Filas -->
      <div style="display:flex;flex-direction:column;gap:2px">
        <?php foreach ($choroes as $c): ?>
        <div style="display:flex;align-items:center;gap:16px;padding:12px 4px;border-bottom:1px solid var(--divider)">
          <!-- Tarea -->
          <div style="flex:2;min-width:0;font-weight:500;font-size:0.9rem"><?= esc($c['task_name']) ?></div>
          <!-- Responsable -->
          <div style="flex:1;display:flex;align-items:center;gap:6px">
            <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($c['assigned_name'], 0, 1)) ?></div>
            <span style="font-size:0.855rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($c['assigned_name']) ?></span>
          </div>
          <!-- Penalización -->
          <div style="flex:0 0 80px;text-align:right;font-size:1rem;font-weight:700;color:var(--danger)">
            -€<?= number_format($c['penalty_amount'], 2) ?>
          </div>
          <!-- Eliminar -->
          <div style="flex:0 0 auto">
            <form method="post" action="<?= site_url('/chores/delete/' . $c['id']) ?>"
                  onsubmit="return confirm('¿Eliminar «<?= esc(addslashes($c['task_name'])) ?>»?')">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-danger btn-icon" title="Eliminar">
                <i data-lucide="trash-2" style="width:13px;height:13px"></i>
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Modal: Add chore -->
<div class="modal-overlay" id="modal-add-chore">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="plus-circle" style="width:18px;height:18px;color:var(--primary)"></i> Nueva tarea</h3>
      <button class="modal-close" onclick="closeModal('modal-add-chore')">×</button>
    </div>
    <form method="post" action="<?= site_url('/chores/store') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nombre de la tarea</label>
        <input type="text" name="task_name" required placeholder="Ej: Limpiar baño, Sacar basura...">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Asignar a</label>
          <select name="assigned_user_id" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha límite</label>
          <input type="date" name="due_date" id="modal-due-date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Penalización (€)</label>
          <input type="number" name="penalty_amount" value="<?= esc($defaultPenalty) ?>" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label>Recurrencia</label>
          <select name="recurrence">
            <option value="none">Sin recurrencia</option>
            <option value="weekly">Semanal (rotativo)</option>
            <option value="biweekly">Quincenal</option>
            <option value="monthly">Mensual</option>
          </select>
        </div>
      </div>
      <input type="hidden" name="icon" value="task">
      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Crear tarea</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-chore')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit chore -->
<div class="modal-overlay" id="modal-edit-chore">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="pencil" style="width:18px;height:18px;color:var(--primary)"></i> Editar tarea</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-chore')">×</button>
    </div>
    <form method="post" id="form-edit-chore" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="chore_id" id="edit-chore-id">
      <div class="form-group">
        <label>Nombre de la tarea</label>
        <input type="text" name="task_name" id="edit-task-name" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Asignar a</label>
          <select name="assigned_user_id" id="edit-assigned" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha límite</label>
          <input type="date" name="due_date" id="edit-due-date" required>
        </div>
      </div>
      <div class="form-group">
        <label>Penalización (€)</label>
        <input type="number" name="penalty_amount" id="edit-penalty" min="0" step="0.5">
      </div>
      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Guardar cambios</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-chore')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Swap request -->
<div class="modal-overlay" id="modal-swap">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="arrow-left-right" style="width:18px;height:18px;color:var(--primary)"></i> Proponer intercambio</h3>
      <button class="modal-close" onclick="closeModal('modal-swap')">×</button>
    </div>
    <form method="post" action="<?= site_url('/chores/swap/request') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="chore_id" id="swap-chore-id">
      <div class="form-group">
        <label>Tarea a intercambiar</label>
        <input type="text" id="swap-chore-name" readonly style="opacity:.7">
      </div>
      <div class="form-group">
        <label>Intercambiar con</label>
        <select name="target_user_id" required>
          <?php foreach ($members as $m): ?>
            <?php if ($m['id'] != session()->get('user_id')): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Compensación económica (€) <small style="color:var(--muted)">Opcional</small></label>
        <input type="number" name="compensation" value="0" min="0" step="0.5" placeholder="0.00">
      </div>
      <div class="form-group">
        <label>Mensaje</label>
        <textarea name="message" placeholder="Explica por qué necesitas el cambio..."></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Enviar solicitud</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-swap')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<style>
.cal-day {
  cursor: pointer;
  transition: background .15s, box-shadow .15s;
  border-radius: 8px;
}
.cal-day:hover { background: var(--surface2); }
.cal-day.selected {
  background: var(--primary-light);
  box-shadow: inset 0 0 0 2px var(--primary);
}
.cal-day .cal-day-num { pointer-events: none; }

/* Day detail panel task rows */
.day-task-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 13px 16px;
  background: var(--surface2);
  border-radius: 10px;
  border: 1px solid var(--border);
  margin-bottom: 8px;
  flex-wrap: wrap;
}
.day-task-row:last-child { margin-bottom: 0; }
</style>

<script>
const TASKS    = <?= json_encode($tasksByDate) ?>;
const TASKS_BY_ID = {};
Object.values(TASKS).forEach(arr => arr.forEach(t => { TASKS_BY_ID[t.id] = t; }));
const ME_ID    = <?= (int) session()->get('user_id') ?>;
const IS_ADMIN = <?= session()->get('is_admin') ? 'true' : 'false' ?>;
const CSRF     = '<?= csrf_hash() ?>';
const CSRF_KEY = '<?= csrf_token() ?>';
const MARK_URL = '<?= site_url('/chores/mark-done/') ?>';
const UPD_URL  = '<?= site_url('/chores/update/') ?>';
const DEL_URL  = '<?= site_url('/chores/delete/') ?>';

let selectedEl = null;

function selectDay(date, el) {
  // Toggle off if same day clicked again
  if (selectedEl === el) {
    closePanel();
    return;
  }

  // Deselect previous
  if (selectedEl) selectedEl.classList.remove('selected');
  selectedEl = el;
  el.classList.add('selected');

  const tasks = TASKS[date] || [];
  const panel = document.getElementById('day-panel');
  const title = document.getElementById('day-panel-title');
  const body  = document.getElementById('day-panel-body');

  // Format date nicely
  const d     = new Date(date + 'T00:00:00');
  const label = d.toLocaleDateString('es-ES', { weekday:'long', day:'numeric', month:'long' });
  title.textContent = label.charAt(0).toUpperCase() + label.slice(1);

  if (tasks.length === 0) {
    closePanel();
    return;
  } else {
    let html = '';
    tasks.forEach(t => {
      const isMe     = t.assigned_user_id == ME_ID;
      const canAct   = true;
      const isPending = t.status === 'pending';

      const statusBadge = {
        done:    `<span class="badge badge-done"   style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Completada</span>`,
        missed:  `<span class="badge badge-missed" style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> No realizada</span>`,
        pending: `<span class="badge badge-pending" style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Pendiente</span>`,
      }[t.status] || '';

      let actions = '';
      if (isPending && isMe) {
        actions += `
          <form method="post" action="${MARK_URL}${t.id}" style="display:inline">
            <input type="hidden" name="${CSRF_KEY}" value="${CSRF}">
            <button class="btn btn-sm btn-primary" type="submit">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              Completar
            </button>
          </form>
          <button class="btn btn-sm btn-secondary" onclick="openSwapModal(${t.id},'${escJs(t.task_name)}')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
            Cambiar
          </button>`;
      }
      if (canAct) {
        actions += `
          <button class="btn btn-sm btn-secondary btn-icon" title="Editar"
                  onclick="openEditChoreModal(${t.id})">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
          </button>
          <form method="post" action="${DEL_URL}${t.id}" style="display:inline"
                onsubmit="return confirm('¿Eliminar «${escJs(t.task_name)}»?')">
            <input type="hidden" name="${CSRF_KEY}" value="${CSRF}">
            <button class="btn btn-sm btn-danger btn-icon" type="submit" title="Eliminar">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
          </form>`;
      }

      html += `
        <div class="day-task-row">
          <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
            <div style="width:36px;height:36px;background:${t.color ?? 'rgba(37,99,235,0.1)'};border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="${t.text_color ?? 'var(--primary)'}" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
            </div>
            <div style="min-width:0">
              <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(t.task_name)}</div>
              <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">${escHtml(t.assigned_name)}</div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap">
            ${statusBadge}
            ${actions}
          </div>
        </div>`;
    });
    body.innerHTML = html;
  }

  panel.style.display = 'block';
  // Scroll panel into view smoothly
  setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
}

function closePanel() {
  document.getElementById('day-panel').style.display = 'none';
  if (selectedEl) { selectedEl.classList.remove('selected'); selectedEl = null; }
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) {
  return String(s).replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

function openEditChoreModal(id) {
  const t = TASKS_BY_ID[id];
  if (!t) return;
  document.getElementById('form-edit-chore').action = UPD_URL + t.id;
  document.getElementById('edit-chore-id').value    = t.id;
  document.getElementById('edit-task-name').value   = t.task_name;
  document.getElementById('edit-assigned').value    = t.assigned_user_id;
  document.getElementById('edit-due-date').value    = t.due_date;
  document.getElementById('edit-penalty').value     = t.penalty_amount ?? '';
  openModal('modal-edit-chore');
}

function openSwapModal(choreId, choreName) {
  document.getElementById('swap-chore-id').value = choreId;
  document.getElementById('swap-chore-name').value = choreName;
  openModal('modal-swap');
}

// Auto-open today's panel if today is in the calendar
<?php
$today = date('Y-m-d');
$todayInCalendar = false;
foreach ($calDays as $d) {
    if ($d['date'] === $today && !$d['other_month']) { $todayInCalendar = true; break; }
}
?>
<?php if ($todayInCalendar): ?>
document.addEventListener('DOMContentLoaded', () => {
  const todayEl = document.querySelector('.cal-day.today');
  if (todayEl) selectDay('<?= $today ?>', todayEl);
});
<?php endif; ?>
</script>

<?= view('layouts/footer') ?>
