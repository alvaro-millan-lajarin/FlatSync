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
    <i data-lucide="calendar" style="width:14px;height:14px"></i> <?= lang('App.chores_week') ?>
  </a>
  <a href="<?= site_url('/chores?view=month') ?>" class="btn <?= ($view==='month') ? 'btn-primary' : 'btn-secondary' ?>">
    <i data-lucide="calendar-days" style="width:14px;height:14px"></i> <?= lang('App.chores_month') ?>
  </a>
  <button class="btn btn-primary" style="margin-left:auto" onclick="openModal('modal-add-chore')">
    <i data-lucide="plus" style="width:14px;height:14px"></i> <?= lang('App.chores_new') ?>
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
    <a href="<?= site_url('/chores?view=' . $view . '&offset=0') ?>" class="btn btn-sm btn-secondary"><?= lang('App.chores_today_btn') ?></a>
  </div>

  <div class="calendar-grid">
    <?php foreach (lang('App.chores_days') as $d): ?>
      <div class="cal-header"><?= $d ?></div>
    <?php endforeach; ?>

    <?php foreach ($calDays as $day): ?>
    <div class="cal-day <?= $day['today'] ? 'today' : '' ?> <?= $day['other_month'] ? 'other-month' : '' ?>"
         data-date="<?= $day['date'] ?>"
         onclick="selectDay('<?= $day['date'] ?>', this)">
      <div class="cal-day-num"><?= $day['num'] ?></div>
      <?php foreach ($day['tasks'] as $t): ?>
        <div class="cal-task" data-chore-id="<?= $t['id'] ?>" style="background:<?= $t['color'] ?? 'rgba(37,99,235,0.12)' ?>;color:<?= $t['text_color'] ?? 'var(--text)' ?>">
          <?php if ($t['recurrence'] !== 'none'): ?><span style="opacity:.6;margin-right:2px">↻</span><?php endif; ?><?= esc($t['task_name']) ?>
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

<!-- History: missed chores + swaps -->
<?php
// Build a unified history list sorted by date DESC
$history = [];
foreach ($missedChores as $c) {
    $history[] = ['type' => 'missed', 'sort' => $c['due_date'], 'data' => $c];
}
foreach ($recentSwaps as $s) {
    $history[] = ['type' => 'swap', 'sort' => $s['created_at'], 'data' => $s];
}
usort($history, fn($a, $b) => strcmp($b['sort'], $a['sort']));
$today     = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
?>
<?php if (!empty($history)): ?>
<div class="card" style="margin-top:16px">
  <div class="card-header">
    <span class="card-title">
      <i data-lucide="clock" style="width:15px;height:15px"></i> <?= lang('App.chores_history') ?>
    </span>
  </div>

  <div class="history-list">
    <?php foreach ($history as $item):
      $type = $item['type'];
      $d    = $item['data'];
      $dateStr = $type === 'missed' ? $d['due_date'] : substr($d['created_at'], 0, 10);
      $ts   = strtotime($dateStr);
      if ($dateStr === $today)         $dateLabel = lang('App.today') . ' · ' . date('d/m/Y', $ts);
      elseif ($dateStr === $yesterday) $dateLabel = lang('App.yesterday') . ' · ' . date('d/m/Y', $ts);
      else                             $dateLabel = date('d/m/Y', $ts);
    ?>
    <div class="history-item">

      <!-- badge -->
      <div class="hi-badge">
        <?php if ($type === 'missed'): ?>
          <span class="badge badge-missed" style="gap:4px;width:100%;justify-content:center">
            <i data-lucide="alert-triangle" style="width:10px;height:10px"></i> <?= lang('App.chores_status_missed') ?>
          </span>
        <?php else: ?>
          <span class="badge" style="gap:4px;width:100%;justify-content:center;background:rgba(124,106,247,0.15);color:var(--accent)">
            <i data-lucide="arrow-left-right" style="width:10px;height:10px"></i> Swap
          </span>
        <?php endif; ?>
      </div>

      <!-- task name -->
      <div class="hi-name"><?= esc($d['task_name']) ?></div>

      <!-- flex line-break (hidden desktop, visible mobile) -->
      <div class="hi-break"></div>

      <!-- person(s) -->
      <?php if ($type === 'missed'): ?>
        <div class="hi-person">
          <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($d['assigned_name'], 0, 1)) ?></div>
          <span class="hi-person-name"><?= esc($d['assigned_name']) ?></span>
        </div>
      <?php else: ?>
        <div class="hi-person">
          <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($d['requester_name'], 0, 1)) ?></div>
          <span class="hi-person-name"><?= esc($d['requester_name']) ?></span>
          <i data-lucide="arrow-right" style="width:12px;height:12px;flex-shrink:0;color:var(--muted)"></i>
          <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($d['target_name'], 0, 1)) ?></div>
          <span class="hi-person-name"><?= esc($d['target_name']) ?></span>
        </div>
      <?php endif; ?>

      <!-- penalty -->
      <div class="hi-penalty">
        <?php if ($type === 'missed'): ?>-€<?= number_format($d['penalty_amount'], 2) ?><?php endif; ?>
      </div>

      <!-- date -->
      <div class="hi-date"><?= $dateLabel ?></div>

      <!-- action -->
      <div class="hi-action">
        <?php if ($type === 'missed'): ?>
          <form method="post" action="<?= site_url('/chores/delete/' . $d['id']) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="scope" value="this">
            <button class="btn btn-sm btn-danger btn-icon" title="Eliminar">
              <i data-lucide="trash-2" style="width:13px;height:13px"></i>
            </button>
          </form>
        <?php else: ?>
          <div style="width:28px"></div>
        <?php endif; ?>
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
      <h3 class="modal-title"><i data-lucide="plus-circle" style="width:18px;height:18px;color:var(--primary)"></i> <?= lang('App.chores_add_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-add-chore')">×</button>
    </div>
    <form method="post" action="<?= site_url('/chores/store') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label><?= lang('App.chores_task_name') ?></label>
        <input type="text" name="task_name" required placeholder="<?= lang('App.chores_task_ph') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label style="display:flex;align-items:center;justify-content:space-between">
            <?= lang('App.chores_assign') ?>
            <button type="button" onclick="openRuleta()"
                    style="font-size:0.72rem;background:none;border:none;cursor:pointer;color:var(--primary);padding:0;font-weight:700;display:inline-flex;align-items:center;gap:3px;line-height:1">
              <?= lang('App.ruleta_btn') ?>
            </button>
          </label>
          <select name="assigned_user_id" id="add-assigned-select" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label><?= lang('App.chores_due') ?></label>
          <input type="date" name="due_date" id="modal-due-date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><?= lang('App.chores_penalty') ?></label>
          <input type="number" name="penalty_amount" value="<?= esc($defaultPenalty) ?>" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label><?= lang('App.chores_recurrence') ?></label>
          <select name="recurrence">
            <option value="none"><?= lang('App.chores_rec_none') ?></option>
            <option value="weekly"><?= lang('App.chores_rec_weekly') ?></option>
            <option value="biweekly"><?= lang('App.chores_rec_biweekly') ?></option>
            <option value="monthly"><?= lang('App.chores_rec_monthly') ?></option>
          </select>
        </div>
      </div>
      <input type="hidden" name="icon" value="task">
      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center"><?= lang('App.chores_create') ?></button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-chore')"><?= lang('App.cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Ruleta de asignación -->
<div class="modal-overlay" id="modal-ruleta">
  <div class="modal" style="max-width:340px">
    <div class="modal-header">
      <h3 class="modal-title"><?= lang('App.ruleta_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-ruleta')">×</button>
    </div>
    <div style="padding:8px 24px 24px;display:flex;flex-direction:column;align-items:center">
      <!-- Wheel container -->
      <div style="position:relative;display:inline-block;margin-bottom:20px;margin-top:8px">
        <!-- Arrow indicator -->
        <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);z-index:10;
                    width:0;height:0;
                    border-left:10px solid transparent;border-right:10px solid transparent;
                    border-top:18px solid #1E293B;
                    filter:drop-shadow(0 2px 4px rgba(0,0,0,0.3))"></div>
        <!-- Wheel canvas -->
        <canvas id="ruleta-canvas" width="280" height="280"
                style="display:block;border-radius:50%;box-shadow:0 6px 28px rgba(0,0,0,0.18)">
        </canvas>
        <!-- Center hub -->
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                    width:28px;height:28px;background:#fff;border-radius:50%;
                    border:3px solid #1E293B;box-shadow:0 2px 8px rgba(0,0,0,0.25);z-index:5"></div>
      </div>
      <!-- Result -->
      <div id="ruleta-result" style="min-height:54px;margin-bottom:16px;text-align:center;width:100%"></div>
      <!-- Spin button -->
      <button class="btn btn-primary" id="btn-spin" onclick="spinRuleta()"
              style="width:100%;justify-content:center;margin-bottom:8px;font-size:1rem">
        <?= lang('App.ruleta_spin') ?>
      </button>
      <!-- Re-spin + Confirm row (hidden until result) -->
      <div style="display:flex;gap:8px;width:100%">
        <button class="btn btn-secondary" id="btn-respin"
                style="display:none;flex:1;justify-content:center" onclick="spinRuleta()">
          <?= lang('App.ruleta_respin') ?>
        </button>
        <button class="btn btn-primary" id="btn-confirm-ruleta"
                style="display:none;flex:1;justify-content:center" onclick="confirmRuleta()">
          <?= lang('App.ruleta_confirm') ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit chore -->
<div class="modal-overlay" id="modal-edit-chore">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="pencil" style="width:18px;height:18px;color:var(--primary)"></i> <?= lang('App.chores_edit_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-edit-chore')">×</button>
    </div>
    <form method="post" id="form-edit-chore" action="">
      <?= csrf_field() ?>
      <input type="hidden" name="chore_id" id="edit-chore-id">
      <div class="form-group">
        <label><?= lang('App.chores_task_name') ?></label>
        <input type="text" name="task_name" id="edit-task-name" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label><?= lang('App.chores_assign') ?></label>
          <select name="assigned_user_id" id="edit-assigned" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label><?= lang('App.chores_due') ?></label>
          <input type="date" name="due_date" id="edit-due-date" required>
        </div>
      </div>
      <div class="form-group">
        <label><?= lang('App.chores_penalty') ?></label>
        <input type="number" name="penalty_amount" id="edit-penalty" min="0" step="0.5">
      </div>
      <div style="display:flex;gap:10px;margin-top:4px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center"><?= lang('App.save') ?></button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-chore')"><?= lang('App.cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Swap request -->
<div class="modal-overlay" id="modal-swap">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="arrow-left-right" style="width:18px;height:18px;color:var(--primary)"></i> <?= lang('App.chores_swap_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-swap')">×</button>
    </div>
    <form method="post" action="<?= site_url('/chores/swap/request') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="chore_id" id="swap-chore-id">
      <div class="form-group">
        <label><?= lang('App.chores_swap_with') ?></label>
        <select name="target_user_id" required>
          <?php foreach ($members as $m): ?>
            <?php if ($m['id'] != session()->get('user_id')): ?>
              <option value="<?= $m['id'] ?>"><?= esc($m['username']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label><?= lang('App.chores_swap_msg') ?></label>
        <textarea name="message" placeholder="<?= lang('App.chores_swap_msg_ph') ?>"></textarea>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center"><?= lang('App.chores_swap_send') ?></button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-swap')"><?= lang('App.cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Delete recurring task -->
<div class="modal-overlay" id="modal-delete-recurring">
  <div class="modal" style="max-width:380px">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="repeat-2" style="width:18px;height:18px;color:var(--danger)"></i> <?= lang('App.chores_rec_delete_title') ?></h3>
      <button class="modal-close" onclick="closeModal('modal-delete-recurring')">×</button>
    </div>
    <div style="padding:0 24px 24px;display:flex;flex-direction:column;gap:10px">
      <form method="post" id="form-delete-this">
        <?= csrf_field() ?>
        <input type="hidden" name="scope" value="this">
        <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center">
          <i data-lucide="calendar-x" style="width:14px;height:14px"></i> <?= lang('App.chores_rec_delete_this') ?>
        </button>
      </form>
      <form method="post" id="form-delete-future">
        <?= csrf_field() ?>
        <input type="hidden" name="scope" value="future">
        <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center">
          <i data-lucide="calendar-off" style="width:14px;height:14px"></i> <?= lang('App.chores_rec_delete_future') ?>
        </button>
      </form>
      <button type="button" class="btn btn-secondary" onclick="closeModal('modal-delete-recurring')" style="width:100%;justify-content:center"><?= lang('App.cancel') ?></button>
    </div>
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

/* ── History rows ───────────────────────────────────────────── */
.history-list { display: flex; flex-direction: column; gap: 2px; }
.history-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 4px;
  border-bottom: 1px solid var(--divider);
}
.hi-badge   { flex: 0 0 110px; }
.hi-name    { flex: 2; min-width: 0; font-weight: 500; font-size: 0.9rem;
              overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.hi-break   { display: none; }
.hi-person  { flex: 0 0 220px; display: flex; align-items: center;
              justify-content: center; gap: 6px; }
.hi-person-name { font-size: 0.85rem; white-space: nowrap; }
.hi-penalty { flex: 0 0 70px; text-align: right; font-weight: 700; color: var(--danger); }
.hi-date    { flex: 0 0 140px; text-align: right; font-size: 0.75rem;
              color: var(--muted); white-space: nowrap; }
.hi-action  { flex: 0 0 auto; }

@media (max-width: 768px) {
  /* Calendar: hide task name chips in cells, show coloured dot instead */
  .cal-task {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-size: 0.6rem;
    padding: 1px 4px;
  }
  /* Day-panel actions: wrap to their own line on mobile */
  .day-task-row {
    gap: 8px;
  }
  .day-task-row > div:last-child {
    flex-shrink: 1 !important;
    width: 100%;
    justify-content: flex-end;
  }
  /* Missed chores: hide person name, keep avatar */
  .chore-person-name { display: none; }

  /* History rows: card layout on mobile */
  .history-list { gap: 0; padding: 4px 0; }
  .history-item {
    flex-wrap: wrap;
    gap: 6px 8px;
    padding: 12px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface2);
    margin-bottom: 8px;
  }
  .hi-badge   { flex: 0 0 auto; order: 1; }
  .hi-name    { flex: 1 1 0; min-width: 0; order: 2;
                font-size: 0.95rem; font-weight: 600; }
  .hi-break   { display: block; flex: 1 0 100%; height: 1px;
                background: var(--divider); order: 3; margin: 2px 0; }
  .hi-person  { flex: 1 1 0; min-width: 0; justify-content: flex-start; order: 4; }
  .hi-person-name { display: inline; font-size: 0.82rem;
                    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                    max-width: 80px; }
  .hi-penalty { flex: 0 0 auto; width: auto; font-size: 0.9rem; order: 5; }
  .hi-date    { flex: 0 0 auto; text-align: right; font-size: 0.72rem; order: 6; }
  .hi-action  { order: 7; flex: 0 0 auto; }
}
</style>

<script>
const TASKS    = <?= json_encode($tasksByDate) ?>;
const TASKS_BY_ID = {};
Object.values(TASKS).forEach(arr => arr.forEach(t => { TASKS_BY_ID[t.id] = t; }));
const ME_ID    = <?= (int) session()->get('user_id') ?>;
const L = {
  done:    '<?= lang('App.chores_status_done') ?>',
  missed:  '<?= lang('App.chores_status_missed') ?>',
  pending: '<?= lang('App.chores_status_pending') ?>',
  complete:'<?= lang('App.chores_complete') ?>',
  undo:    '<?= lang('App.chores_undo') ?>',
  swap:    '<?= lang('App.chores_swap') ?>',
};
const IS_ADMIN = <?= session()->get('is_admin') ? 'true' : 'false' ?>;
const CSRF     = '<?= csrf_hash() ?>';
const CSRF_KEY = '<?= csrf_token() ?>';
const MARK_URL   = '<?= site_url('/chores/mark-done/') ?>';
const TOGGLE_URL = '<?= site_url('/chores/toggle/') ?>';
const UPD_URL    = '<?= site_url('/chores/update/') ?>';
const DEL_URL    = '<?= site_url('/chores/delete/') ?>';
const REC_DEL_TITLE  = '<?= lang('App.chores_rec_delete_title') ?>';
const REC_DEL_THIS   = '<?= lang('App.chores_rec_delete_this') ?>';
const REC_DEL_FUTURE = '<?= lang('App.chores_rec_delete_future') ?>';

function deleteChore(id, isRecurring) {
  const url = `${DEL_URL}${id}`;
  if (isRecurring) {
    document.getElementById('form-delete-this').action   = url;
    document.getElementById('form-delete-future').action = url;
    openModal('modal-delete-recurring');
  } else {
    const fd = new FormData();
    fd.append(CSRF_KEY, CSRF);
    fd.append('scope', 'this');
    fetch(url, { method: 'POST', body: fd })
      .then(r => { if (r.ok) location.reload(); })
      .catch(() => {});
  }
}

let selectedEl = null;

function selectDay(date, el) {
  if (selectedEl === el) { closePanel(); return; }
  if (selectedEl) selectedEl.classList.remove('selected');
  selectedEl = el;
  el.classList.add('selected');
  renderPanel(date);
}

function renderPanel(date) {
  const tasks = TASKS[date] || [];
  const panel = document.getElementById('day-panel');
  const title = document.getElementById('day-panel-title');
  const body  = document.getElementById('day-panel-body');

  const d     = new Date(date + 'T00:00:00');
  const label = d.toLocaleDateString('es-ES', { weekday:'long', day:'numeric', month:'long' });
  title.textContent = label.charAt(0).toUpperCase() + label.slice(1);

  if (tasks.length === 0) {
    body.innerHTML = `<div style="text-align:center;padding:24px 0 8px;color:var(--muted);font-size:0.88rem"><?= lang('App.ruleta_no_tasks') ?></div>
      <div style="text-align:center;padding-bottom:20px">
        <button class="btn btn-primary btn-sm" onclick="openAddChoreForDate('${date}')">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <?= lang('App.ruleta_add_task') ?>
        </button>
      </div>`;
    panel.style.display = 'block';
    setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
    return;
  }

  let html = '';
  tasks.forEach(t => {
    const isMe = t.assigned_user_id == ME_ID;

    const statusBadge = {
      done:    `<span class="badge badge-done"   style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> ${L.done}</span>`,
      missed:  `<span class="badge badge-missed" style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> ${L.missed}</span>`,
      pending: `<span class="badge badge-pending" style="gap:4px"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ${L.pending}</span>`,
    }[t.status] || '';

    let actions = '';
    if (isMe && t.status === 'pending') {
      actions += `
        <button class="btn btn-sm btn-primary" onclick="toggleDone(${t.id})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          ${L.complete}
        </button>
        <button class="btn btn-sm btn-secondary" onclick="openSwapModal(${t.id},'${escJs(t.task_name)}')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          ${L.swap}
        </button>`;
    }
    if (isMe && t.status === 'done') {
      actions += `
        <button class="btn btn-sm btn-secondary" onclick="toggleDone(${t.id})">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
          ${L.undo}
        </button>`;
    }
    actions += `
      <button class="btn btn-sm btn-secondary btn-icon" title="Editar"
              onclick="openEditChoreModal(${t.id})">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
      </button>
      <button class="btn btn-sm btn-danger btn-icon" type="button" title="Eliminar"
              onclick="deleteChore(${t.id}, ${t.recurrence && t.recurrence !== 'none' ? 'true' : 'false'})">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      </button>`;

    html += `
      <div class="day-task-row">
        <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
          <div style="width:36px;height:36px;background:${t.color ?? 'rgba(37,99,235,0.1)'};border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="${t.text_color ?? 'var(--primary)'}" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg>
          </div>
          <div style="min-width:0">
            <div style="font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${t.recurrence && t.recurrence !== 'none' ? '<span style="opacity:.5;margin-right:3px;font-size:0.8em">↻</span>' : ''}${escHtml(t.task_name)}</div>
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
  panel.style.display = 'block';
  setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 50);
}

function toggleDone(id) {
  const fd = new FormData();
  fd.append(CSRF_KEY, CSRF);
  fetch(`${TOGGLE_URL}${id}`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (!data.ok) return;
      // Update status in all JS structures
      if (TASKS_BY_ID[id]) TASKS_BY_ID[id].status = data.status;
      Object.keys(TASKS).forEach(date => {
        TASKS[date].forEach(t => { if (t.id == id) t.status = data.status; });
      });
      // Re-render the open panel
      if (selectedEl) renderPanel(selectedEl.dataset.date);
    })
    .catch(() => {});
}

function closePanel() {
  document.getElementById('day-panel').style.display = 'none';
  if (selectedEl) { selectedEl.classList.remove('selected'); selectedEl = null; }
}

function openAddChoreForDate(date) {
  document.getElementById('modal-due-date').value = date;
  openModal('modal-add-chore');
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
  openModal('modal-swap');
}

// Envío AJAX del formulario de nueva tarea — igual que el chat con buildNote(data.note)
document.querySelector('#modal-add-chore form').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fetch(this.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        closeModal('modal-add-chore');
        this.reset();
        // Renderizar directamente desde la respuesta (como buildNote en el chat)
        if (data.chore) {
          const t = data.chore;
          lastChoreId = Math.max(lastChoreId, parseInt(t.id));
          if (!TASKS[t.due_date]) TASKS[t.due_date] = [];
          TASKS[t.due_date].push(t);
          TASKS_BY_ID[t.id] = t;
          const cell = document.querySelector(`.cal-day[data-date="${t.due_date}"]`);
          if (cell) {
            const chip = document.createElement('div');
            chip.className = 'cal-task';
            chip.dataset.choreId = t.id;
            chip.style.background = t.color || 'rgba(37,99,235,0.12)';
            chip.style.color = t.text_color || 'var(--text)';
            chip.textContent = t.task_name;
            cell.appendChild(chip);
          }
          if (selectedEl && selectedEl.dataset.date === t.due_date) {
            selectDay(t.due_date, selectedEl);
          }
        }
      } else if (data.error) {
        alert(data.error);
      }
    })
    .catch(() => {});
});

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

<script>
// Polling de tareas — detecta tareas nuevas y actualizadas (intercambios)
const CHORE_POLL_URL = '<?= site_url('/chores/poll') ?>';
let lastChoreId = Object.keys(TASKS_BY_ID).length
  ? Math.max(...Object.keys(TASKS_BY_ID).map(Number))
  : 0;

function sqlNow() {
  const d = new Date();
  return d.getFullYear() + '-' +
    String(d.getMonth()+1).padStart(2,'0') + '-' +
    String(d.getDate()).padStart(2,'0') + ' ' +
    String(d.getHours()).padStart(2,'0') + ':' +
    String(d.getMinutes()).padStart(2,'0') + ':' +
    String(d.getSeconds()).padStart(2,'0');
}

let lastPollAt = sqlNow();

function pollChores() {
  const pollTs = sqlNow();
  fetch(`${CHORE_POLL_URL}?after=${lastChoreId}&after_ts=${encodeURIComponent(lastPollAt)}`)
    .then(r => r.json())
    .then(data => {
      lastPollAt = pollTs;
      if (!data.chores || data.chores.length === 0) return;
      data.chores.forEach(t => {
        lastChoreId = Math.max(lastChoreId, parseInt(t.id));

        const isUpdate = !!TASKS_BY_ID[t.id];

        if (isUpdate) {
          const old = TASKS_BY_ID[t.id];
          // Actualizar en el array de la fecha antigua
          if (TASKS[old.due_date]) {
            const idx = TASKS[old.due_date].findIndex(x => x.id == t.id);
            if (idx !== -1) {
              if (old.due_date !== t.due_date) {
                TASKS[old.due_date].splice(idx, 1);
              } else {
                TASKS[old.due_date][idx] = t;
              }
            }
          }
          // Si cambió de fecha, añadir a la nueva
          if (old.due_date !== t.due_date) {
            if (!TASKS[t.due_date]) TASKS[t.due_date] = [];
            TASKS[t.due_date].push(t);
          }
          TASKS_BY_ID[t.id] = t;

          // Actualizar chip en el calendario
          const chip = document.querySelector(`.cal-day .cal-task[data-chore-id="${t.id}"]`);
          if (chip) {
            chip.style.background = t.color || 'rgba(37,99,235,0.12)';
            chip.style.color = t.text_color || 'var(--text)';
            chip.textContent = t.task_name;
            // Mover el chip si cambió de fecha
            if (old.due_date !== t.due_date) {
              chip.remove();
              const newCell = document.querySelector(`.cal-day[data-date="${t.due_date}"]`);
              if (newCell) newCell.appendChild(chip);
            }
          }
        } else {
          // Tarea nueva
          if (!TASKS[t.due_date]) TASKS[t.due_date] = [];
          TASKS[t.due_date].push(t);
          TASKS_BY_ID[t.id] = t;

          const cell = document.querySelector(`.cal-day[data-date="${t.due_date}"]`);
          if (cell) {
            const chip = document.createElement('div');
            chip.className = 'cal-task';
            chip.dataset.choreId = t.id;
            chip.style.background = t.color || 'rgba(37,99,235,0.12)';
            chip.style.color = t.text_color || 'var(--text)';
            chip.textContent = t.task_name;
            cell.appendChild(chip);
          }
        }

        // Re-renderizar panel abierto si afecta a esa fecha
        if (selectedEl) {
          const openDate = selectedEl.dataset.date;
          if (openDate === t.due_date || (isUpdate && openDate === TASKS_BY_ID[t.id]?.due_date)) {
            renderPanel(openDate);
          }
        }
      });
    })
    .catch(() => {});
}

setInterval(pollChores, 3000);

// ── RULETA ──
const RULETA_COLORS  = ['#4F80FF','#F59E0B','#4ECDC4','#EF4444','#8B5CF6','#EC4899','#10B981','#F97316','#6366F1','#06B6D4'];
const MEMBERS_DATA   = <?= json_encode(array_values(array_map(fn($m) => ['id' => (int)$m['id'], 'username' => $m['username']], $members))) ?>;
let ruletaWinner     = null;
let ruletaSpinning   = false;

function drawRuletaWheel() {
  const canvas = document.getElementById('ruleta-canvas');
  const ctx    = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const cx = W / 2, cy = H / 2, r = W / 2 - 6;
  const n  = MEMBERS_DATA.length;
  ctx.clearRect(0, 0, W, H);
  if (n === 0) return;

  const sliceRad = (2 * Math.PI) / n;

  for (let i = 0; i < n; i++) {
    const start    = i * sliceRad - Math.PI / 2;
    const end      = start + sliceRad;
    const midAngle = start + sliceRad / 2;
    const color    = RULETA_COLORS[i % RULETA_COLORS.length];

    // Segment fill
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, start, end);
    ctx.closePath();
    ctx.fillStyle = color;
    ctx.fill();

    // Segment divider
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, start, end);
    ctx.closePath();
    ctx.strokeStyle = 'rgba(255,255,255,0.9)';
    ctx.lineWidth   = 3;
    ctx.stroke();

    // Text — rotate so it reads outward; flip left-half for legibility
    const textR = r * 0.65;
    const tx = cx + Math.cos(midAngle) * textR;
    const ty = cy + Math.sin(midAngle) * textR;
    const inLeftHalf = midAngle > Math.PI / 2 && midAngle < 3 * Math.PI / 2;

    ctx.save();
    ctx.translate(tx, ty);
    ctx.rotate(inLeftHalf ? midAngle + Math.PI : midAngle);
    ctx.textAlign    = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle    = '#fff';
    const fs = n > 5 ? 11 : 13;
    ctx.font         = `800 ${fs}px Nunito, system-ui, sans-serif`;
    ctx.shadowColor  = 'rgba(0,0,0,0.55)';
    ctx.shadowBlur   = 4;
    const maxLen = n > 6 ? 7 : 10;
    const name = MEMBERS_DATA[i].username.length > maxLen
      ? MEMBERS_DATA[i].username.slice(0, maxLen - 1) + '…'
      : MEMBERS_DATA[i].username;
    ctx.fillText(name, 0, 0);
    ctx.restore();
  }

  // Outer rim
  ctx.beginPath();
  ctx.arc(cx, cy, r, 0, 2 * Math.PI);
  ctx.strokeStyle = 'rgba(255,255,255,0.6)';
  ctx.lineWidth   = 6;
  ctx.stroke();

  ctx.beginPath();
  ctx.arc(cx, cy, r + 3, 0, 2 * Math.PI);
  ctx.strokeStyle = 'rgba(0,0,0,0.12)';
  ctx.lineWidth   = 2;
  ctx.stroke();
}

function openRuleta() {
  ruletaWinner   = null;
  ruletaSpinning = false;
  const canvas = document.getElementById('ruleta-canvas');
  canvas.style.transition = 'none';
  canvas.style.transform  = 'rotate(0deg)';
  document.getElementById('ruleta-result').innerHTML          = '';
  document.getElementById('btn-confirm-ruleta').style.display = 'none';
  document.getElementById('btn-respin').style.display         = 'none';
  document.getElementById('btn-spin').style.display           = '';
  drawRuletaWheel();
  openModal('modal-ruleta');
}

function spinRuleta() {
  if (ruletaSpinning || MEMBERS_DATA.length === 0) return;
  ruletaSpinning = true;
  ruletaWinner   = null;

  document.getElementById('btn-spin').style.display           = 'none';
  document.getElementById('btn-confirm-ruleta').style.display = 'none';
  document.getElementById('btn-respin').style.display         = 'none';
  document.getElementById('ruleta-result').innerHTML          = '';

  const n          = MEMBERS_DATA.length;
  const winnerIdx  = Math.floor(Math.random() * n);
  const sliceDeg   = 360 / n;
  const extraSpins = (5 + Math.floor(Math.random() * 4)) * 360;
  // FIX: no +90 — formula: finalDeg = extraSpins - (winnerIdx+0.5)*sliceDeg
  const finalDeg   = extraSpins - (winnerIdx + 0.5) * sliceDeg;
  const duration   = 3000 + Math.floor(Math.random() * 800);

  const canvas = document.getElementById('ruleta-canvas');
  canvas.style.transition = 'none';
  canvas.style.transform  = 'rotate(0deg)';
  canvas.offsetHeight; // force reflow
  canvas.style.transition = `transform ${duration}ms cubic-bezier(0.17, 0.67, 0.12, 0.99)`;
  canvas.style.transform  = `rotate(${finalDeg}deg)`;

  setTimeout(() => {
    ruletaSpinning = false;
    ruletaWinner   = MEMBERS_DATA[winnerIdx];
    const color    = RULETA_COLORS[winnerIdx % RULETA_COLORS.length];
    document.getElementById('ruleta-result').innerHTML = `
      <div style="font-size:0.78rem;color:var(--muted);margin-bottom:4px"><?= lang('App.ruleta_assigned_to') ?></div>
      <div style="font-size:1.4rem;font-weight:900;color:${color}">${escHtml(ruletaWinner.username)}</div>`;
    document.getElementById('btn-confirm-ruleta').style.display = '';
    document.getElementById('btn-respin').style.display         = '';
    if (navigator.vibrate) navigator.vibrate([80, 30, 80]);
  }, duration);
}

function confirmRuleta() {
  if (!ruletaWinner) return;
  document.getElementById('add-assigned-select').value = ruletaWinner.id;
  closeModal('modal-ruleta');
}
</script>

<?= view('layouts/footer') ?>
