<?php
$_locale = session()->get('lang') ?? 'es';
\Config\Services::language()->setLocale($_locale);
?>
<!DOCTYPE html>
<html lang="<?= $_locale ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= lang('App.login_btn') ?> — FlatSync</title>
  <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>

<div class="auth-split">

  <!-- ── LEFT PANEL ── -->
  <div class="auth-panel-left">
    <div class="auth-blob auth-blob-1"></div>
    <div class="auth-blob auth-blob-2"></div>
    <div class="auth-blob auth-blob-3"></div>

    <div class="auth-panel-content">
      <a href="<?= site_url('/') ?>" class="auth-left-brand">
        flat<span>sync</span>
        <span class="auth-left-brand-dot"></span>
      </a>

      <h2><?= lang('App.landing_h1_line1') ?><br><em><?= lang('App.landing_typewriter') ?></em></h2>
      <p class="auth-panel-tagline"><?= lang('App.landing_hero_p') ?></p>

      <div class="auth-features">
        <div class="auth-feat" style="--i:0">
          <div class="auth-feat-icon" style="background:rgba(37,99,235,0.25)">
            <i data-lucide="wallet" style="width:20px;height:20px;color:#93C5FD"></i>
          </div>
          <div>
            <div class="auth-feat-title"><?= lang('App.landing_f1_title') ?></div>
            <div class="auth-feat-sub"><?= lang('App.landing_f1_desc') ?></div>
          </div>
        </div>
        <div class="auth-feat" style="--i:1">
          <div class="auth-feat-icon" style="background:rgba(22,163,74,0.22)">
            <i data-lucide="calendar-check" style="width:20px;height:20px;color:#86EFAC"></i>
          </div>
          <div>
            <div class="auth-feat-title"><?= lang('App.landing_f2_title') ?></div>
            <div class="auth-feat-sub"><?= lang('App.landing_f2_desc') ?></div>
          </div>
        </div>
        <div class="auth-feat" style="--i:2">
          <div class="auth-feat-icon" style="background:rgba(234,88,12,0.22)">
            <i data-lucide="message-circle" style="width:20px;height:20px;color:#FCA5A5"></i>
          </div>
          <div>
            <div class="auth-feat-title"><?= lang('App.landing_f3_title') ?></div>
            <div class="auth-feat-sub"><?= lang('App.landing_f3_desc') ?></div>
          </div>
        </div>
      </div>

      <div class="auth-stats">
        <div class="auth-stat-item"><strong>500+</strong> <?= lang('App.landing_stat_flats') ?></div>
        <div class="auth-stat-sep"></div>
        <div class="auth-stat-item"><strong>100%</strong> <?= lang('App.landing_mq_free') ?></div>
        <div class="auth-stat-sep"></div>
        <div class="auth-stat-item"><strong>2 min</strong> <?= lang('App.landing_stat_start') ?></div>
      </div>
    </div>
  </div>

  <!-- ── RIGHT PANEL ── -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">

      <a href="<?= site_url('/') ?>" class="auth-mobile-logo">flat<span>sync</span></a>

      <div class="auth-form-heading">
        <h2><?= lang('App.login_btn') ?></h2>
        <p><?= lang('App.login_title') ?></p>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" style="margin-bottom:18px">
          <i data-lucide="check-circle" style="width:14px;height:14px;flex-shrink:0"></i>
          <?= esc(session()->getFlashdata('success')) ?>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error" style="margin-bottom:18px">
          <i data-lucide="alert-triangle" style="width:14px;height:14px;flex-shrink:0"></i>
          <?= esc(session()->getFlashdata('error')) ?>
        </div>
        <?php if (session()->getFlashdata('unverified_email')): ?>
          <form method="post" action="<?= site_url('/resend-verification') ?>" style="margin-bottom:18px">
            <?= csrf_field() ?>
            <input type="hidden" name="email" value="<?= esc(session()->getFlashdata('unverified_email')) ?>">
            <button type="submit" class="btn btn-secondary" style="width:100%;justify-content:center;font-size:.88rem;padding:10px">
              <i data-lucide="mail" style="width:14px;height:14px"></i>
              Reenviar email de verificación
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <form method="post" action="<?= site_url('/login') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="email"><?= lang('App.login_email') ?></label>
          <div class="auth-input-wrap">
            <span class="auth-input-icon"><i data-lucide="mail"></i></span>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" required placeholder="tu@email.com">
          </div>
          <?php if (!empty($errors['email'])): ?><small style="color:var(--danger)"><?= $errors['email'] ?></small><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="password"><?= lang('App.login_password') ?></label>
          <div class="auth-input-wrap">
            <span class="auth-input-icon"><i data-lucide="lock"></i></span>
            <input type="password" id="password" name="password" required placeholder="••••••••" style="padding-right:40px">
            <button type="button" class="auth-pw-toggle" onclick="togglePw('password',this)" tabindex="-1">
              <i data-lucide="eye"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?><small style="color:var(--danger)"><?= $errors['password'] ?></small><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;margin-top:4px;font-size:.95rem">
          <?= lang('App.login_btn') ?>
          <i data-lucide="arrow-right" style="width:15px;height:15px"></i>
        </button>
        <div style="text-align:center;margin-top:12px">
          <a href="<?= site_url('/forgot-password') ?>" style="font-size:.85rem;color:var(--muted);text-decoration:none" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--muted)'"><?= lang('App.login_forgot') ?></a>
        </div>
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
