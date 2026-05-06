<?= view('layouts/header') ?>

<div style="max-width:560px">
  <div class="card">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" style="margin-bottom:18px">
        <?php foreach ($errors as $e): ?><?= esc($e) ?><br><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post" action="<?= site_url('/profile/edit') ?>" id="profile-form">
      <?= csrf_field() ?>

      <div class="form-group">
        <label><?= lang('App.profile_name') ?> <span style="color:var(--danger)">*</span></label>
        <input type="text" name="username" value="<?= esc($user['username']) ?>" required placeholder="Tu nombre">
      </div>

      <div class="form-group">
        <label><?= lang('App.profile_email') ?></label>
        <input type="email" name="email" value="<?= esc($user['email']) ?>" placeholder="tu@email.com">
        <p style="font-size:0.75rem;color:var(--muted);margin-top:4px"><?= lang('App.profile_email_hint') ?></p>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">
          <?= lang('App.profile_save') ?>
        </button>
        <a href="<?= site_url('/profile') ?>" class="btn btn-secondary" style="flex:1;justify-content:center"><?= lang('App.cancel') ?></a>
      </div>
    </form>
  </div>
</div>


<?= view('layouts/footer') ?>
