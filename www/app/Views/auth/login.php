<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <h1>flat<span>sync</span></h1>
      <p>Gestiona tu hogar compartido</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i> <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/login') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="tu@email.com">
        <?php if (!empty($errors['email'])): ?><small style="color:var(--danger)"><?= $errors['email'] ?></small><?php endif; ?>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
        <?php if (!empty($errors['password'])): ?><small style="color:var(--danger)"><?= $errors['password'] ?></small><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
        Entrar
      </button>
    </form>

    <div class="auth-footer">
      ¿No tienes cuenta? <a href="<?= site_url('/register') ?>">Regístrate</a>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
