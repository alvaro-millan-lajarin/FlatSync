<?php
$_locale = session()->get('lang') ?? 'es';
\Config\Services::language()->setLocale($_locale);
?>
<!DOCTYPE html>
<html lang="<?= $_locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= lang('App.forgot_title') ?> — FlatSync</title>
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
      <h2><?= lang('App.forgot_title') ?></h2>
      <p class="auth-panel-tagline"><?= lang('App.forgot_subtitle') ?></p>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">
      <a href="<?= site_url('/') ?>" class="auth-mobile-logo">flat<span>sync</span></a>

      <div class="auth-form-heading">
        <h2><?= lang('App.forgot_title') ?></h2>
        <p><?= lang('App.forgot_subtitle') ?></p>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" style="margin-bottom:18px">
          <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0"></i>
          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      <?php endif; ?>

      <?php if (!session()->getFlashdata('success')): ?>
      <form method="post" action="<?= site_url('/forgot-password') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label><?= lang('App.forgot_email') ?></label>
          <div class="auth-input-wrap">
            <span class="auth-input-icon"><i data-lucide="mail"></i></span>
            <input type="email" name="email" required autofocus placeholder="tu@email.com">
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:.95rem">
          <i data-lucide="send" style="width:15px;height:15px"></i>
          <?= lang('App.forgot_btn') ?>
        </button>
      </form>
      <?php endif; ?>

      <div class="auth-footer">
        <a href="<?= site_url('/login') ?>">← <?= lang('App.forgot_back') ?></a>
      </div>
      <?= view('layouts/_lang_flags', ['_lang' => $_locale]) ?>
    </div>
  </div>
</div>
<script src="<?= base_url('js/app.js') ?>"></script>
<script>if (window.lucide) lucide.createIcons();</script>
</body>
</html>
