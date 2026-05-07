<?= view('layouts/header') ?>

<style>
.admin-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:700; }
.badge-pending  { background:rgba(245,158,11,0.12); color:#D97706; }
.badge-active   { background:rgba(34,197,94,0.12);  color:#16A34A; }
.badge-inactive { background:rgba(239,68,68,0.12);  color:#DC2626; }
.lead-num { font-family:'Syne',sans-serif; font-weight:900; font-size:1.1rem; }
</style>

<div class="card" style="margin-bottom:24px">
  <div class="card-header" style="justify-content:space-between;flex-wrap:wrap;gap:12px">
    <span class="card-title"><i data-lucide="bar-chart-2"></i> Panel de leads — Proveedores</span>
    <a href="/services/register" class="btn btn-sm btn-primary" target="_blank">+ Ver formulario público</a>
  </div>
  <p style="font-size:0.82rem;color:var(--muted);margin-bottom:0">
    Aquí ves quién se ha registrado y cuántas veces un usuario ha pulsado "Llamar" en su perfil.
    Aprueba al proveedor para que aparezca en la app. Factura mensualmente los leads del mes.
  </p>
</div>

<?php if (empty($providers)): ?>
<div class="empty-state" style="padding:60px 0">
  <i data-lucide="users" style="width:40px;height:40px;color:var(--muted)"></i>
  <h3>Sin proveedores aún</h3>
  <p>Comparte <a href="/services/register">/services/register</a> con empresas de servicios locales.</p>
</div>
<?php else: ?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Empresa</th>
          <th>Categoría</th>
          <th>Ciudad</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th style="text-align:center">Este mes</th>
          <th style="text-align:center">Total</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($providers as $p): ?>
        <tr>
          <td style="font-weight:600"><?= esc($p['name']) ?><?php if ($p['website']): ?> <a href="<?= esc($p['website']) ?>" target="_blank" rel="noopener" style="font-size:0.72rem;color:var(--primary);margin-left:4px">↗</a><?php endif; ?></td>
          <td><span class="badge badge-accent"><?= esc($p['category']) ?></span></td>
          <td style="color:var(--muted);font-size:0.82rem"><?= esc($p['city'] ?: '—') ?></td>
          <td><a href="tel:<?= esc($p['phone']) ?>" style="font-size:0.85rem"><?= esc($p['phone']) ?></a></td>
          <td><a href="mailto:<?= esc($p['email']) ?>" style="font-size:0.82rem;color:var(--muted)"><?= esc($p['email']) ?></a></td>
          <td style="text-align:center"><span class="lead-num" style="color:var(--accent)"><?= (int)$p['leads_month'] ?></span></td>
          <td style="text-align:center"><span class="lead-num" style="color:var(--muted)"><?= (int)$p['leads_total'] ?></span></td>
          <td>
            <?php if ($p['active']): ?>
              <span class="admin-badge badge-active">Activo</span>
            <?php else: ?>
              <span class="admin-badge badge-pending">Pendiente</span>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap">
            <form action="/admin/providers/<?= $p['id'] ?>/toggle" method="post" style="display:inline">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm <?= $p['active'] ? 'btn-secondary' : 'btn-primary' ?>" style="font-size:0.75rem">
                <?= $p['active'] ? 'Desactivar' : 'Aprobar' ?>
              </button>
            </form>
            <form action="/admin/providers/<?= $p['id'] ?>/delete" method="post" style="display:inline;margin-left:4px"
                  onsubmit="return confirm('¿Eliminar a <?= esc($p['name'], 'js') ?>?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-danger" style="font-size:0.75rem">Borrar</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card" style="margin-top:20px">
  <div class="card-header"><span class="card-title"><i data-lucide="info"></i> Cómo facturar</span></div>
  <div style="font-size:0.85rem;color:var(--text);line-height:1.7">
    <p>Cada lead = una pulsación del botón "Llamar" en el perfil del proveedor dentro de la app.</p>
    <ol style="margin:8px 0 0 18px;padding:0">
      <li>Mira la columna <strong>Este mes</strong> a final de mes.</li>
      <li>Multiplica por el precio pactado (ej. €8/lead).</li>
      <li>Envía factura a la dirección de email del proveedor.</li>
      <li>El contador se reinicia automáticamente cada mes.</li>
    </ol>
  </div>
</div>
<?php endif; ?>

<?= view('layouts/footer') ?>
