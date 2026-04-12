<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear hogar — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <h1>Flat<span>Sync</span></h1>
      <p>Crea un nuevo hogar</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error"><i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i> <?= esc($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/homes/create') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nombre del hogar</label>
        <input type="text" name="home_name" required placeholder="Ej: Mi piso, Casa de los chicos..." autofocus>
      </div>
      <div style="display:flex;align-items:flex-start;gap:10px;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px;font-size:0.83rem;color:var(--muted)">
        <i data-lucide="info" style="width:15px;height:15px;flex-shrink:0;margin-top:1px"></i>
        <span>Se generará un <strong>código de invitación</strong> automáticamente. Compártelo desde <em>Miembros</em> para que otros se unan.</span>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
        <i data-lucide="plus" style="width:16px;height:16px"></i> Crear hogar
      </button>
    </form>

    <div class="auth-footer">
      <a href="<?= site_url('/homes') ?>">← Volver a mis hogares</a>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
