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
      <p><?= lang('App.login_title') ?></p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i> <?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('/login') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email"><?= lang('App.login_email') ?></label>
        <input type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="tu@email.com">
        <?php if (!empty($errors['email'])): ?><small style="color:var(--danger)"><?= $errors['email'] ?></small><?php endif; ?>
      </div>
      <div class="form-group">
        <label for="password"><?= lang('App.login_password') ?></label>
        <input type="password" id="password" name="password" required placeholder="••••••••">
        <?php if (!empty($errors['password'])): ?><small style="color:var(--danger)"><?= $errors['password'] ?></small><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
        <?= lang('App.login_btn') ?>
      </button>
    </form>

    <div class="auth-divider"><span><?= lang('App.login_or') ?></span></div>

    <a href="<?= site_url('/auth/google') ?>" class="btn-google">
      <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
        <path fill="none" d="M0 0h48v48H0z"/>
      </svg>
      <?= lang('App.login_google') ?>
    </a>

    <div class="auth-footer">
      <?= lang('App.login_no_account') ?> — <a href="<?= site_url('/register') ?>"><?= lang('App.register_btn') ?></a>
    </div>

    <?php $_lang = session()->get('lang') ?? 'es'; ?>
    <?= view('layouts/_lang_flags', ['_lang' => $_lang]) ?>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
