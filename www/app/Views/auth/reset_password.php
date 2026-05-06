<?php
$_locale = session()->get('lang') ?? 'es';
\Config\Services::language()->setLocale($_locale);
?>
<!DOCTYPE html>
<html lang="<?= $_locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= lang('App.reset_title') ?> — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<div class="auth-split">

  <!-- LEFT -->
  <div class="auth-panel-left">
    <div class="auth-blob auth-blob-1"></div>
    <div class="auth-blob auth-blob-2"></div>
    <div class="auth-blob auth-blob-3"></div>
    <div class="auth-panel-content">
      <a href="<?= site_url('/') ?>" class="auth-left-brand">flat<span>sync</span><span class="auth-left-brand-dot"></span></a>
      <h2><?= lang('App.reset_title') ?></h2>
      <p class="auth-panel-tagline"><?= lang('App.reset_subtitle') ?></p>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">
      <a href="<?= site_url('/') ?>" class="auth-mobile-logo">flat<span>sync</span></a>

      <div class="auth-form-heading">
        <h2><?= lang('App.reset_title') ?></h2>
        <p><?= lang('App.reset_subtitle') ?></p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="margin-bottom:18px">
          <i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i>
          <?= esc($error) ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= site_url('/reset-password/' . esc($token)) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label><?= lang('App.reset_new_pass') ?></label>
          <div class="auth-input-wrap">
            <span class="auth-input-icon"><i data-lucide="lock"></i></span>
            <input type="password" id="password" name="password" required placeholder="••••••••" minlength="6" style="padding-right:40px">
            <button type="button" class="auth-pw-toggle" onclick="togglePw('password',this)" tabindex="-1">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label><?= lang('App.reset_confirm') ?></label>
          <div class="auth-input-wrap">
            <span class="auth-input-icon"><i data-lucide="lock-keyhole"></i></span>
            <input type="password" id="confirm" name="confirm" required placeholder="••••••••" minlength="6" style="padding-right:40px">
            <button type="button" class="auth-pw-toggle" onclick="togglePw('confirm',this)" tabindex="-1">
              <i data-lucide="eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:.95rem">
          <i data-lucide="check" style="width:15px;height:15px"></i>
          <?= lang('App.reset_btn') ?>
        </button>
      </form>

      <div class="auth-footer">
        <a href="<?= site_url('/login') ?>">← <?= lang('App.forgot_back') ?></a>
      </div>
      <?= view('layouts/_lang_flags', ['_lang' => $_locale]) ?>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>
if (window.lucide) lucide.createIcons();
function togglePw(id, btn) {
  const inp = document.getElementById(id);
  const isText = inp.type === 'text';
  inp.type = isText ? 'password' : 'text';
  btn.querySelector('i').setAttribute('data-lucide', isText ? 'eye' : 'eye-off');
  lucide.createIcons();
}
</script>
</body>
</html>
