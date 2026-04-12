<?= view('layouts/header') ?>

<!-- Stats row -->
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card accent">
    <div class="stat-icon"><i data-lucide="wallet"></i></div>
    <div class="stat-value">€<?= number_format($monthTotal, 2) ?></div>
    <div class="stat-label">Total gastado este mes</div>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon"><i data-lucide="user"></i></div>
    <div class="stat-value">€<?= number_format($myPaid, 2) ?></div>
    <div class="stat-label">Lo que has pagado tú</div>
  </div>
  <div class="stat-card <?= $myBalance > 0 ? 'success' : ($myBalance < 0 ? 'danger' : 'accent') ?>">
    <div class="stat-icon"><i data-lucide="<?= $myBalance >= 0 ? 'trending-up' : 'trending-down' ?>"></i></div>
    <div class="stat-value"><?= $myBalance >= 0 ? '+' : '-' ?>€<?= number_format(abs($myBalance), 2) ?></div>
    <div class="stat-label"><?= $myBalance > 0 ? 'Te deben' : ($myBalance < 0 ? 'Debes' : 'Estás al día') ?></div>
  </div>
</div>

<!-- Filter bar -->
<div class="card" style="margin-bottom:20px">
  <form method="get" action="<?= site_url('/expenses') ?>" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="form-group" style="margin:0;flex:1;min-width:150px">
      <label>Mes</label>
      <input type="month" name="month" value="<?= $filterMonth ?>">
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:150px">
      <label>Categoría</label>
      <select name="category">
        <option value="">Todas</option>
        <option value="food" <?= $filterCategory === 'food' ? 'selected' : '' ?>>Comida</option>
        <option value="cleaning" <?= $filterCategory === 'cleaning' ? 'selected' : '' ?>>Limpieza</option>
        <option value="bills" <?= $filterCategory === 'bills' ? 'selected' : '' ?>>Facturas</option>
        <option value="other" <?= $filterCategory === 'other' ? 'selected' : '' ?>>Otros</option>
      </select>
    </div>
    <div class="form-group" style="margin:0;flex:1;min-width:150px">
      <label>Pagado por</label>
      <select name="paid_by">
        <option value="">Todos</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= $m['id'] ?>" <?= $filterPaidBy == $m['id'] ? 'selected' : '' ?>><?= esc($m['username']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-secondary" style="margin-bottom:0">Filtrar</button>
    <a href="<?= site_url('/expenses') ?>" class="btn btn-secondary" style="margin-bottom:0"><i data-lucide="x" style="width:13px;height:13px"></i> Limpiar</a>
  </form>
</div>

<!-- Expenses table -->
<div class="card">
  <div class="card-header">
    <span class="card-title">Historial de gastos</span>
    <div style="display:flex;gap:8px">
      <button class="btn btn-primary" onclick="openModal('modal-add-expense')"><i data-lucide="plus" style="width:14px;height:14px"></i> Añadir gasto</button>
      <a href="<?= site_url('/expenses/export') ?>" class="btn btn-sm btn-secondary"><i data-lucide="download" style="width:13px;height:13px"></i> Exportar</a>
    </div>
  </div>

  <?php if (empty($expenses)): ?>
    <div class="empty-state"><div class="icon"><i data-lucide="receipt" style="width:32px;height:32px;color:var(--muted)"></i></div><h3>Sin gastos registrados</h3><p>Añade el primer gasto compartido del hogar</p></div>
  <?php else: ?>
  <?php
    $cats = ['food'=>'Comida','cleaning'=>'Limpieza','bills'=>'Facturas','other'=>'Otros'];
    $grouped = [];
    foreach ($expenses as $e) { $grouped[$e['date']][] = $e; }
    $today     = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
  ?>
  <div style="display:flex;flex-direction:column;gap:20px">
    <?php foreach ($grouped as $date => $dayExpenses): ?>
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
        <div style="flex:1;height:1px;background:var(--divider)"></div>
      </div>

      <!-- Filas de gastos -->
      <div style="display:flex;flex-direction:column;gap:2px">
        <?php foreach ($dayExpenses as $e): ?>
        <div style="display:flex;align-items:center;gap:16px;padding:12px 4px;border-bottom:1px solid var(--divider)">
          <!-- Título -->
          <div style="flex:2;min-width:0">
            <div style="font-weight:500;font-size:0.9rem"><?= esc($e['title']) ?></div>
            <?php if ($e['description']): ?>
              <div style="font-size:0.75rem;color:var(--muted);margin-top:1px"><?= esc($e['description']) ?></div>
            <?php endif; ?>
          </div>
          <!-- Categoría -->
          <div style="flex:0 0 auto">
            <span class="badge badge-accent"><?= $cats[$e['category']] ?? esc($e['category']) ?></span>
          </div>
          <!-- Importe -->
          <div style="flex:0 0 80px;text-align:right;font-size:1rem;font-weight:700;color:var(--primary)">
            €<?= number_format($e['amount'], 2) ?>
          </div>
          <!-- Pagado por -->
          <div style="flex:0 0 130px;display:flex;align-items:center;gap:6px">
            <div class="user-avatar" style="width:24px;height:24px;font-size:0.65rem;flex-shrink:0"><?= strtoupper(substr($e['paid_by_name'], 0, 1)) ?></div>
            <span style="font-size:0.855rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($e['paid_by_name']) ?></span>
          </div>
          <!-- Prueba -->
          <div style="flex:0 0 60px;text-align:center">
            <?php if ($e['receipt_image']): ?>
              <a href="<?= base_url('uploads/' . $e['receipt_image']) ?>" target="_blank" class="btn btn-sm btn-secondary"><i data-lucide="image" style="width:12px;height:12px"></i></a>
            <?php else: ?>
              <span style="color:var(--muted);font-size:0.8rem">—</span>
            <?php endif; ?>
          </div>
          <!-- Acciones -->
          <div style="flex:0 0 auto;display:flex;gap:6px">
            <button class="btn btn-sm btn-secondary btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($e), ENT_QUOTES) ?>)" title="Editar"><i data-lucide="pencil" style="width:13px;height:13px"></i></button>
            <form method="post" action="<?= site_url('/expenses/delete/' . $e['id']) ?>" onsubmit="return confirm('¿Eliminar este gasto?')">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-danger btn-icon" title="Eliminar"><i data-lucide="trash-2" style="width:13px;height:13px"></i></button>
            </form>
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
      <h3 class="modal-title"><i data-lucide="plus-circle" style="width:18px;height:18px;color:var(--primary)"></i> Añadir gasto</h3>
      <button class="modal-close" onclick="closeModal('modal-add-expense')">×</button>
    </div>
    <form method="post" action="<?= site_url('/expenses/store') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="title" required placeholder="Ej: Papel higiénico, Cena compartida...">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Importe (€)</label>
          <input type="number" name="amount" required min="0.01" step="0.01" placeholder="0.00">
        </div>
        <div class="form-group">
          <label>Categoría</label>
          <select name="category">
            <option value="food">Comida</option>
            <option value="cleaning">Limpieza</option>
            <option value="bills">Facturas</option>
            <option value="other">Otros</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Pagado por</label>
          <select name="paid_by" required>
            <?php foreach ($members as $m): ?>
              <option value="<?= $m['id'] ?>" <?= $m['id'] == session()->get('user_id') ? 'selected' : '' ?>><?= esc($m['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha</label>
          <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="description" placeholder="Detalles adicionales..."></textarea>
      </div>
      <div class="form-group">
        <label><i data-lucide="paperclip" style="width:13px;height:13px"></i> Adjuntar ticket / foto <small style="color:var(--muted)">(opcional)</small></label>
        <input type="file" name="receipt_image" accept="image/*,.pdf">
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Guardar gasto</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-add-expense')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit expense -->
<div class="modal-overlay" id="modal-edit-expense">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title"><i data-lucide="pencil" style="width:18px;height:18px;color:var(--primary)"></i> Editar gasto</h3>
      <button class="modal-close" onclick="closeModal('modal-edit-expense')">×</button>
    </div>
    <form method="post" id="form-edit-expense" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group">
        <label>Descripción</label>
        <input type="text" name="title" id="edit-title" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Importe (€)</label>
          <input type="number" name="amount" id="edit-amount" required step="0.01">
        </div>
        <div class="form-group">
          <label>Categoría</label>
          <select name="category" id="edit-category">
            <option value="food">Comida</option>
            <option value="cleaning">Limpieza</option>
            <option value="bills">Facturas</option>
            <option value="other">Otros</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Fecha</label>
        <input type="date" name="date" id="edit-date" required>
      </div>
      <div style="display:flex;gap:10px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">Guardar cambios</button>
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-edit-expense')">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(expense) {
  document.getElementById('edit-title').value   = expense.title;
  document.getElementById('edit-amount').value  = expense.amount;
  document.getElementById('edit-category').value = expense.category;
  document.getElementById('edit-date').value    = expense.date;
  document.getElementById('form-edit-expense').action = '/expenses/update/' + expense.id;
  openModal('modal-edit-expense');
}
</script>

<?= view('layouts/footer') ?>
