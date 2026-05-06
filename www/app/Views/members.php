<?= view('layouts/header') ?>

<?php $_inviteLink = site_url('/homes/join/' . $home['invite_code']); ?>
<div style="margin-bottom:20px;padding:20px 24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius)">
  <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px"><?= lang('App.members_invite_code') ?></div>
  <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
    <div style="flex:1;min-width:0;display:flex;align-items:center;gap:10px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;overflow:hidden">
      <i data-lucide="link" style="width:15px;height:15px;color:var(--primary);flex-shrink:0"></i>
      <span id="invite-link-text" style="font-size:0.85rem;font-weight:600;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= esc($_inviteLink) ?></span>
    </div>
    <button onclick="copyInviteLink()" id="copy-btn" class="btn btn-primary" style="flex-shrink:0;gap:6px">
      <i data-lucide="copy" style="width:14px;height:14px"></i> <?= lang('App.members_copy_link') ?>
    </button>
  </div>
  <div style="color:var(--muted);font-size:0.8rem;margin-top:8px"><?= lang('App.members_invite_hint') ?></div>
</div>
<script>
function copyInviteLink() {
  navigator.clipboard.writeText(<?= json_encode($_inviteLink) ?>).then(() => {
    const btn = document.getElementById('copy-btn');
    btn.innerHTML = '<i data-lucide="check" style="width:14px;height:14px"></i> <?= lang('App.copied') ?>';
    btn.style.background = 'var(--success)';
    if (window.lucide) lucide.createIcons();
    setTimeout(() => {
      btn.innerHTML = '<i data-lucide="copy" style="width:14px;height:14px"></i> <?= lang('App.members_copy_link') ?>';
      btn.style.background = '';
      if (window.lucide) lucide.createIcons();
    }, 2000);
  });
}
</script>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
  <?php foreach ($memberStats as $m): ?>
  <div class="card" style="position:relative">
    <?php if ($m['is_admin']): ?>
      <div style="position:absolute;top:16px;right:16px"><span class="badge badge-accent"><i data-lucide="shield-check" style="width:10px;height:10px"></i> <?= lang('App.homes_admin') ?></span></div>
    <?php endif; ?>
    <?php if ($m['id'] == session()->get('user_id')): ?>
      <div style="position:absolute;top:<?= $m['is_admin'] ? '46px' : '16px' ?>;right:16px"><span class="badge badge-done"><?= lang('App.members_you') ?></span></div>
    <?php endif; ?>

    <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px">
      <?= avatar($m['avatar_url'] ?? null, $m['username'], 48) ?>
      <div>
        <div style="font-size:1rem;font-weight:700;color:var(--text)"><?= esc($m['username']) ?></div>
        <div style="font-size:0.78rem;color:var(--muted)"><?= esc($m['email']) ?></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center">
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--accent2)">€<?= number_format($m['total_paid'], 0) ?></div>
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px"><?= lang('App.members_paid') ?></div>
      </div>
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--success)"><?= $m['chores_done'] ?></div>
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px"><?= lang('App.members_done') ?></div>
      </div>
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--danger)"><?= $m['chores_missed'] ?></div>
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px"><?= lang('App.members_missed') ?></div>
      </div>
    </div>

    <?php if ($m['chores_done'] + $m['chores_missed'] > 0): ?>
    <div style="margin-top:14px">
      <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px"><?= lang('App.members_rate') ?></div>
      <div class="progress-bar">
        <?php $rate = round($m['chores_done'] / ($m['chores_done'] + $m['chores_missed']) * 100); ?>
        <div class="progress-fill" data-width="<?= $rate ?>"></div>
      </div>
      <div style="font-size:0.72rem;color:var(--muted);margin-top:4px"><?= $rate ?>%</div>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<?= view('layouts/footer') ?>
