<?= view('layouts/header') ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

  <!-- Incoming requests -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="inbox"></i> <?= lang('App.swaps_incoming') ?></span></div>
    <?php if (empty($incoming)): ?>
      <div class="empty-state" style="padding:30px 0">
        <div class="icon"><i data-lucide="inbox" style="width:32px;height:32px;color:var(--muted)"></i></div>
        <p><?= lang('App.swaps_none_incoming') ?></p>
      </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($incoming as $s): ?>
      <div style="padding:16px;background:var(--surface2);border-radius:12px;border:1px solid <?= $s['status']==='pending' ? 'rgba(245,158,11,0.3)' : 'var(--border)' ?>">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
          <div class="user-avatar" style="width:28px;height:28px;font-size:0.7rem"><?= strtoupper(substr($s['requester_name'], 0, 1)) ?></div>
          <strong><?= esc($s['requester_name']) ?></strong>
          <span style="color:var(--muted);font-size:0.8rem"><?= lang('App.swaps_wants') ?></span>
        </div>
        <div style="margin-bottom:8px">
          <strong><?= esc($s['task_name']) ?></strong>
          <span style="color:var(--muted);font-size:0.78rem"> — <?= date('d/m/Y', strtotime($s['due_date'])) ?></span>
        </div>
        <?php if ($s['compensation'] > 0): ?>
        <div style="margin-bottom:8px">
          <span class="swap-badge"><i data-lucide="banknote" style="width:10px;height:10px"></i> <?= lang('App.swaps_offers') ?> €<?= number_format($s['compensation'], 2) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($s['message']): ?>
        <div style="font-size:0.8rem;color:var(--muted);font-style:italic;margin-bottom:10px">"<?= esc($s['message']) ?>"</div>
        <?php endif; ?>
        <?php if ($s['status'] === 'pending'): ?>
        <div style="display:flex;gap:8px">
          <form method="post" action="<?= site_url('/chores/swap/' . $s['id'] . '/accept') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-primary"><i data-lucide="check" style="width:12px;height:12px"></i> <?= lang('App.swaps_accept') ?></button>
          </form>
          <form method="post" action="<?= site_url('/chores/swap/' . $s['id'] . '/decline') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-danger"><i data-lucide="x" style="width:12px;height:12px"></i> <?= lang('App.swaps_decline') ?></button>
          </form>
        </div>
        <?php else: ?>
        <span class="badge <?= $s['status']==='accepted' ? 'badge-done' : 'badge-missed' ?>">
          <?php if ($s['status']==='accepted'): ?>
            <i data-lucide="check" style="width:10px;height:10px"></i> <?= lang('App.swaps_accepted') ?>
          <?php else: ?>
            <i data-lucide="x" style="width:10px;height:10px"></i> <?= lang('App.swaps_declined') ?>
          <?php endif; ?>
        </span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Outgoing requests -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="send"></i> <?= lang('App.swaps_outgoing') ?></span></div>
    <?php if (empty($outgoing)): ?>
      <div class="empty-state" style="padding:30px 0">
        <div class="icon"><i data-lucide="send" style="width:32px;height:32px;color:var(--muted)"></i></div>
        <p><?= lang('App.swaps_none_outgoing') ?></p>
      </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($outgoing as $s): ?>
      <div style="padding:16px;background:var(--surface2);border-radius:12px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
          <strong><?= esc($s['task_name']) ?></strong>
          <?php
          $statusClass = ['pending'=>'badge-pending','accepted'=>'badge-done','declined'=>'badge-missed'][$s['status']] ?? 'badge-pending';
          $statusIcon  = ['pending'=>'clock','accepted'=>'check','declined'=>'x'][$s['status']] ?? 'clock';
          $statusText  = ['pending'=>lang('App.swaps_waiting'),'accepted'=>lang('App.swaps_accepted'),'declined'=>lang('App.swaps_declined')][$s['status']] ?? $s['status'];
          ?>
          <span class="badge <?= $statusClass ?>"><i data-lucide="<?= $statusIcon ?>" style="width:10px;height:10px"></i> <?= $statusText ?></span>
        </div>
        <div style="font-size:0.8rem;color:var(--muted)">
          <?= lang('App.swaps_asked') ?> <strong><?= esc($s['target_name']) ?></strong> · <?= date('d/m/Y', strtotime($s['created_at'])) ?>
        </div>
        <?php if ($s['compensation'] > 0): ?>
        <div style="margin-top:6px"><span class="swap-badge"><i data-lucide="banknote" style="width:10px;height:10px"></i> <?= lang('App.swaps_offered') ?> €<?= number_format($s['compensation'], 2) ?></span></div>
        <?php endif; ?>
        <?php if ($s['status'] === 'pending'): ?>
        <div style="margin-top:10px">
          <form method="post" action="<?= site_url('/chores/swap/' . $s['id'] . '/cancel') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-secondary"><?= lang('App.swaps_cancel') ?></button>
          </form>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<?= view('layouts/footer') ?>
