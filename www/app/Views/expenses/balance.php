<?= view('layouts/header') ?>

<div class="balance-top-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  <!-- Individual balances -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?= lang('App.balance_per_person') ?></span>
    </div>
    <div class="balance-list">
      <?php foreach ($memberBalances as $mb): ?>
      <div class="balance-item">
        <div class="balance-user">
          <div class="user-avatar"><?= strtoupper(substr($mb['username'], 0, 1)) ?></div>
          <div>
            <div style="font-weight:500;font-size:0.9rem"><?= esc($mb['username']) ?></div>
            <div style="font-size:0.72rem;color:var(--muted)"><?= lang('App.balance_paid') ?> €<?= number_format($mb['paid'], 2) ?> · <?= lang('App.balance_should') ?> €<?= number_format($mb['should_pay'], 2) ?></div>
          </div>
        </div>
        <div class="balance-amount <?= $mb['balance'] >= 0 ? 'positive' : 'negative' ?>">
          <?= $mb['balance'] >= 0 ? '+' : '' ?>€<?= number_format(abs($mb['balance']), 2) ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Who owes whom -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><i data-lucide="arrow-right-left"></i> <?= lang('App.balance_suggestions') ?></span>
    </div>
    <?php if (empty($settlements)): ?>
      <div class="empty-state" style="padding:30px 0">
        <div class="icon"><i data-lucide="circle-check-big" style="width:32px;height:32px;color:var(--success)"></i></div>
        <h3><?= lang('App.balance_settled_ok') ?></h3>
        <p><?= lang('App.balance_no_debts') ?></p>
      </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($settlements as $s): ?>
      <div style="padding:14px 16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:6px;min-width:0;flex:1">
            <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem;flex-shrink:0"><?= strtoupper(substr($s['from_name'], 0, 1)) ?></div>
            <span style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px"><?= esc($s['from_name']) ?></span>
            <span style="color:var(--muted);flex-shrink:0">→</span>
            <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem;flex-shrink:0"><?= strtoupper(substr($s['to_name'], 0, 1)) ?></div>
            <span style="font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px"><?= esc($s['to_name']) ?></span>
          </div>
          <span style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700;color:var(--danger);flex-shrink:0">€<?= number_format($s['amount'], 2) ?></span>
        </div>
        <?php if ($s['from_id'] == session()->get('user_id')): ?>
        <form method="post" action="<?= site_url('/expenses/settle') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="to_user_id" value="<?= $s['to_id'] ?>">
          <input type="hidden" name="amount" value="<?= $s['amount'] ?>">
          <button class="btn btn-sm btn-primary"><i data-lucide="check" style="width:12px;height:12px"></i> <?= lang('App.balance_mark_paid') ?></button>
        </form>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- Contribution chart -->
<div class="card">
  <div class="card-header">
    <span class="card-title"><i data-lucide="bar-chart-2"></i> <?= lang('App.balance_contribution') ?></span>
  </div>
  <div class="chart-bar-group">
    <?php
    $colors = ['rgba(124,106,247,0.7)','rgba(247,179,106,0.7)','rgba(106,247,160,0.7)','rgba(247,106,106,0.7)'];
    $maxPaid = max(array_column($memberBalances, 'paid')) ?: 1;
    foreach ($memberBalances as $i => $mb):
      $pct = round(($mb['paid'] / $maxPaid) * 100);
      $color = $colors[$i % count($colors)];
    ?>
    <div class="chart-row">
      <div class="chart-label"><?= esc($mb['username']) ?></div>
      <div class="chart-bar-wrap">
        <div class="chart-bar" style="width:<?= $pct ?>%;background:<?= $color ?>">
          €<?= number_format($mb['paid'], 2) ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Payment history -->
<div class="card" style="margin-top:20px">
  <div class="card-header">
    <span class="card-title"><i data-lucide="history"></i> <?= lang('App.balance_history') ?></span>
  </div>
  <?php if (empty($settleHistory)): ?>
    <div class="empty-state" style="padding:30px 0"><p><?= lang('App.balance_no_history') ?></p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th><?= lang('App.balance_from') ?></th><th><?= lang('App.balance_to') ?></th><th><?= lang('App.balance_amount') ?></th><th><?= lang('App.balance_date') ?></th></tr></thead>
      <tbody>
        <?php foreach ($settleHistory as $h): ?>
        <tr>
          <td><?= esc($h['from_name']) ?></td>
          <td><?= esc($h['to_name']) ?></td>
          <td style="color:var(--success);font-weight:600">€<?= number_format($h['amount'], 2) ?></td>
          <td style="color:var(--muted)"><?= date('d/m/Y', strtotime($h['settled_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<style>
@media (max-width: 640px) {
  .balance-top-grid { grid-template-columns: 1fr !important; }
}
</style>

<?= view('layouts/footer') ?>
