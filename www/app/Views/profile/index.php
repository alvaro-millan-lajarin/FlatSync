<?= view('layouts/header') ?>

<div style="max-width:640px">

  <!-- Profile card -->
  <div class="card" style="margin-bottom:20px">
    <div class="profile-header-inner" style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">

      <!-- Initials -->
      <div style="width:88px;height:88px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;color:#fff;border:3px solid var(--border);flex-shrink:0">
        <?= strtoupper(substr($user['username'], 0, 1)) ?>
      </div>

      <!-- Info -->
      <div style="flex:1;min-width:0">
        <h2 style="font-size:1.35rem;font-weight:700;color:var(--text);letter-spacing:-0.02em"><?= esc($user['username']) ?></h2>
        <p style="color:var(--text-secondary);font-size:0.875rem;margin-top:3px"><?= esc($user['email']) ?></p>
        <p style="color:var(--muted);font-size:0.78rem;margin-top:6px">
          <?= lang('App.profile_member_since') ?> <?= date('d M Y', strtotime($user['created_at'])) ?>
        </p>
      </div>

      <!-- Edit button -->
      <a href="<?= site_url('/profile/edit') ?>" class="btn btn-secondary" style="flex-shrink:0">
        <i data-lucide="pencil" style="width:14px;height:14px"></i> <?= lang('App.profile_edit') ?>
      </a>
    </div>
  </div>

  <!-- Stats row -->
  <div class="profile-stats-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="card" style="text-align:center;padding:20px 16px">
      <div style="font-size:1.6rem;font-weight:700;color:var(--primary);letter-spacing:-0.03em"><?= esc($user['username'][0]) ?></div>
      <div style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px"><?= lang('App.profile_initial') ?></div>
    </div>
    <div class="card" style="text-align:center;padding:20px 16px">
      <div style="font-size:1.6rem;font-weight:700;color:var(--text);letter-spacing:-0.03em"><?= date('Y', strtotime($user['created_at'])) ?></div>
      <div style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px"><?= lang('App.profile_year') ?></div>
    </div>
  </div>

</div>

<style>
@media (max-width: 480px) {
  .profile-header-inner { gap: 16px; }
  .profile-header-inner > a.btn { width: 100%; justify-content: center; }
  .profile-stats-grid { grid-template-columns: 1fr 1fr !important; }
  .profile-stats-grid .card:last-child { grid-column: 1 / -1; }
}
</style>

<?= view('layouts/footer') ?>
