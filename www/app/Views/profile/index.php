<?= view('layouts/header') ?>

<div style="max-width:640px">

  <!-- Profile card -->
  <div class="card" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">

      <!-- Avatar -->
      <div style="position:relative;flex-shrink:0">
        <?php if (!empty($user['avatar_url'])): ?>
          <img src="<?= base_url($user['avatar_url']) ?>"
               alt="Avatar"
               style="width:88px;height:88px;border-radius:50%;object-fit:cover;border:3px solid var(--border)">
        <?php else: ?>
          <div style="width:88px;height:88px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;border:3px solid var(--border)">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Info -->
      <div style="flex:1;min-width:0">
        <h2 style="font-size:1.35rem;font-weight:700;color:var(--text);letter-spacing:-0.02em"><?= esc($user['username']) ?></h2>
        <p style="color:var(--text-secondary);font-size:0.875rem;margin-top:3px"><?= esc($user['email']) ?></p>
        <p style="color:var(--muted);font-size:0.78rem;margin-top:6px">
          Miembro desde <?= date('d M Y', strtotime($user['created_at'])) ?>
        </p>
      </div>

      <!-- Edit button -->
      <a href="<?= site_url('/profile/edit') ?>" class="btn btn-secondary" style="flex-shrink:0">
        <i data-lucide="pencil" style="width:14px;height:14px"></i> Editar perfil
      </a>
    </div>
  </div>

  <!-- Stats row -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
    <div class="card" style="text-align:center;padding:20px 16px">
      <div style="font-size:1.6rem;font-weight:700;color:var(--primary);letter-spacing:-0.03em"><?= esc($user['username'][0]) ?></div>
      <div style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px">Inicial</div>
    </div>
    <div class="card" style="text-align:center;padding:20px 16px">
      <div style="font-size:1.6rem;font-weight:700;color:var(--text);letter-spacing:-0.03em"><?= date('Y', strtotime($user['created_at'])) ?></div>
      <div style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px">Año de registro</div>
    </div>
    <div class="card" style="text-align:center;padding:20px 16px">
      <div style="display:flex;align-items:center;justify-content:center;height:1.6rem">
        <?php if ($user['avatar_url']): ?>
          <i data-lucide="check-circle" style="width:24px;height:24px;color:var(--success)"></i>
        <?php else: ?>
          <i data-lucide="circle" style="width:24px;height:24px;color:var(--muted)"></i>
        <?php endif; ?>
      </div>
      <div style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px">Avatar</div>
    </div>
  </div>

</div>

<?= view('layouts/footer') ?>
