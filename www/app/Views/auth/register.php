<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card" style="max-width:420px">
    <div class="auth-logo">
      <h1>flat<span>sync</span></h1>
      <p>Crea tu cuenta para empezar</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i>
        <div><?php foreach ($errors as $e): ?><?= esc($e) ?><br><?php endforeach; ?></div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/register') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="username" value="<?= old('username') ?>" required placeholder="Tu nombre">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= old('email') ?>" required placeholder="tu@email.com">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" required placeholder="Mínimo 6 caracteres">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px;margin-top:4px">
        Crear cuenta
      </button>
    </form>

    <div class="auth-footer" style="margin-top:20px">
      ¿Ya tienes cuenta? <a href="<?= site_url('/login') ?>">Inicia sesión</a>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
