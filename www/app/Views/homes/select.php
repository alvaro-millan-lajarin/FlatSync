<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seleccionar hogar — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="auth-page" style="align-items:flex-start;padding-top:60px">
  <div style="width:100%;max-width:520px">

    <div style="text-align:center;margin-bottom:36px">
      <h1 style="font-size:2rem;font-weight:800;letter-spacing:-0.04em;color:var(--text)">
        Flat<span style="color:var(--primary)">Sync</span>
      </h1>
      <p style="color:var(--muted);margin-top:6px">Elige el hogar en el que quieres entrar</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success" style="margin-bottom:20px"><i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0"></i> <?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error" style="margin-bottom:20px"><i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i> <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (!empty($myHomes)): ?>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:28px">
      <?php foreach ($myHomes as $h): ?>
      <form method="post" action="<?= site_url('/homes/switch/' . $h['id']) ?>">
        <?= csrf_field() ?>
        <button type="submit" style="width:100%;text-align:left;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:14px;font-family:inherit"
          onmouseover="this.style.borderColor='var(--primary)';this.style.boxShadow='var(--shadow)'"
          onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
          <div style="width:44px;height:44px;background:var(--primary-light);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid #BFDBFE">
            <i data-lucide="home" style="width:20px;height:20px;color:var(--primary)"></i>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:1rem;color:var(--text)"><?= esc($h['name']) ?></div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:2px">
              Código: <strong style="letter-spacing:.1em"><?= esc($h['invite_code']) ?></strong>
              <?php if ($h['is_admin']): ?> · <span style="color:var(--primary)"><i data-lucide="shield-check" style="width:10px;height:10px"></i> Admin</span><?php endif; ?>
            </div>
          </div>
          <i data-lucide="chevron-right" style="width:18px;height:18px;color:var(--muted)"></i>
        </button>
      </form>

      <!-- Abandon / Delete actions -->
      <div style="display:flex;gap:8px;padding:0 4px 2px">
        <form method="post" action="<?= site_url('/homes/' . $h['id'] . '/leave-home') ?>"
              data-confirm="¿Abandonar «<?= esc(addslashes($h['name'])) ?>»? Dejarás de ser miembro del hogar.">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-secondary" style="color:var(--danger);border-color:rgba(239,68,68,0.3)">
            <i data-lucide="log-out" style="width:12px;height:12px"></i> Abandonar
          </button>
        </form>
        <?php if ($h['is_admin']): ?>
        <form method="post" action="<?= site_url('/homes/' . $h['id'] . '/delete') ?>"
              data-confirm="¿Eliminar el hogar «<?= esc(addslashes($h['name'])) ?>»? Se borrarán TODOS los datos permanentemente.">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-danger">
            <i data-lucide="trash-2" style="width:12px;height:12px"></i> Eliminar hogar
          </button>
        </form>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:32px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:28px;color:var(--muted)">
      <i data-lucide="home" style="width:40px;height:40px;color:var(--muted);margin-bottom:10px"></i>
      <p>Aún no perteneces a ningún hogar.<br>Crea uno o únete con un código.</p>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <a href="<?= site_url('/homes/create') ?>" class="btn btn-primary" style="justify-content:center;padding:12px">
        <i data-lucide="plus" style="width:16px;height:16px"></i> Crear hogar
      </a>
      <a href="<?= site_url('/homes/join') ?>" class="btn btn-secondary" style="justify-content:center;padding:12px">
        <i data-lucide="key-round" style="width:16px;height:16px"></i> Unirme con código
      </a>
    </div>

    <div style="text-align:center;margin-top:20px;font-size:0.82rem;color:var(--muted)">
      <a href="<?= site_url('/logout') ?>" style="color:var(--muted)">Cerrar sesión</a>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
