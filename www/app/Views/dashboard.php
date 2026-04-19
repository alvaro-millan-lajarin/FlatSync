<?= view('layouts/header') ?>

<!-- Selector de mes -->
<div style="display:flex;gap:10px;align-items:center;margin-bottom:24px;flex-wrap:wrap">
  <a href="<?= site_url('/dashboard?month=' . $prevMonth) ?>" class="btn btn-secondary"><?= lang('App.previous') ?></a>
  <span style="font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700"><?= $monthLabel ?></span>
  <a href="<?= site_url('/dashboard?month=' . $nextMonth) ?>" class="btn btn-secondary"><?= lang('App.next') ?></a>
  <?php if ($filterMonth !== date('Y-m')): ?>
    <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary"><?= lang('App.current_month') ?></a>
  <?php endif; ?>
</div>

<?php
$catColors = [
  'food'     => ['rgba(245,158,11,0.18)', 'var(--warning)'],
  'cleaning' => ['rgba(37,99,235,0.12)',  'var(--primary)'],
  'bills'    => ['rgba(239,68,68,0.12)',  'var(--danger)'],
  'other'    => ['rgba(34,197,94,0.12)',  'var(--success)'],
];
$catLabels = ['food'=>lang('App.cat_food'),'cleaning'=>lang('App.cat_cleaning'),'bills'=>lang('App.cat_bills'),'other'=>lang('App.cat_other')];
?>

<!-- Stats -->
<div class="stats-grid" style="margin-bottom:24px">
  <div class="stat-card accent">
    <div class="stat-icon"><i data-lucide="wallet"></i></div>
    <div class="stat-value">€<?= number_format($monthExpenses, 2) ?></div>
    <div class="stat-label"><?= lang('App.dashboard_total') ?></div>
    <?php if ($vsLastMonth !== null): ?>
    <div style="font-size:0.78rem;margin-top:6px;color:<?= $vsLastMonth >= 0 ? 'var(--danger)' : 'var(--success)' ?>">
      <?= $vsLastMonth >= 0 ? '↑' : '↓' ?> <?= abs(round($vsLastMonth)) ?>% <?= lang('App.dashboard_vs_prev') ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="stat-card warning">
    <div class="stat-icon"><i data-lucide="user"></i></div>
    <div class="stat-value">€<?= number_format($monthExpenses / max($memberCount, 1), 2) ?></div>
    <div class="stat-label"><?= lang('App.dashboard_per_person') ?></div>
  </div>
  <div class="stat-card success">
    <div class="stat-icon"><i data-lucide="check-circle"></i></div>
    <div class="stat-value"><?= $doneChores ?></div>
    <div class="stat-label"><?= lang('App.dashboard_chores_done') ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  <!-- Por categoría -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="folder"></i> <?= lang('App.dashboard_by_category') ?></span></div>
    <?php $maxCat = max(array_column($byCategory, 'total') ?: [1]); ?>
    <div class="chart-bar-group">
      <?php foreach ($byCategory as $cat):
        $cc = $catColors[$cat['category']] ?? ['rgba(200,200,200,0.5)', '#888']; ?>
      <div class="chart-row">
        <div class="chart-label" style="font-size:0.82rem"><?= $catLabels[$cat['category']] ?? esc($cat['category']) ?></div>
        <div class="chart-bar-wrap">
          <div class="chart-bar" style="width:<?= round($cat['total']/$maxCat*100) ?>%;background:<?= $cc[0] ?>;color:<?= $cc[1] ?>">
            €<?= number_format($cat['total'], 2) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:16px">
      <?php foreach ($byCategory as $cat): ?>
      <div style="padding:5px 12px;background:var(--surface2);border-radius:20px;font-size:0.75rem">
        <?= $catLabels[$cat['category']] ?? esc($cat['category']) ?>
        <strong style="margin-left:4px"><?= round($cat['total'] / max($monthExpenses, 1) * 100) ?>%</strong>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Evolución mensual -->
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="trending-up"></i> <?= lang('App.dashboard_evolution') ?></span></div>
    <?php $maxEvol = max(1, max(array_column($monthlyEvolution, 'total') ?: [0])); ?>
    <div class="chart-bar-group">
      <?php foreach ($monthlyEvolution as $me): ?>
      <div class="chart-row">
        <div class="chart-label" style="font-size:0.78rem"><?= date('M', mktime(0,0,0,$me['month'],1)) ?> <?= $me['year'] ?></div>
        <div class="chart-bar-wrap">
          <div class="chart-bar" style="width:<?= round($me['total']/$maxEvol*100) ?>%;background:rgba(124,106,247,0.6);color:var(--accent)">
            €<?= number_format($me['total'], 0) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Gastos por miembro -->
<?php if (!empty($byMember)): ?>
<div class="card" style="margin-bottom:24px">
  <div class="card-header"><span class="card-title"><i data-lucide="users"></i> <?= lang('App.dashboard_by_member') ?></span></div>
  <div style="display:flex;flex-direction:column;gap:12px">
    <?php foreach ($byMember as $bm): ?>
    <div style="padding:14px 16px;background:var(--surface2);border-radius:10px">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:8px">
          <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem"><?= strtoupper(substr($bm['username'], 0, 1)) ?></div>
          <span style="font-weight:500"><?= esc($bm['username']) ?></span>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;color:var(--accent2)">€<?= number_format($bm['paid'], 2) ?></span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" data-width="<?= round($bm['paid'] / max($monthExpenses, 1) * 100) ?>"></div>
      </div>
      <div style="font-size:0.72rem;color:var(--muted);margin-top:4px">
        <?= round($bm['paid'] / max($monthExpenses, 1) * 100) ?>% <?= lang('App.dashboard_of_total') ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Mayores gastos del mes -->
<?php if (!empty($topExpenses)): ?>
<div class="card">
  <div class="card-header"><span class="card-title"><i data-lucide="award"></i> <?= lang('App.dashboard_top') ?></span></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th><?= lang('App.dashboard_col_expense') ?></th><th><?= lang('App.dashboard_col_cat') ?></th><th><?= lang('App.dashboard_col_paid') ?></th><th><?= lang('App.dashboard_col_amount') ?></th></tr></thead>
      <tbody>
        <?php foreach ($topExpenses as $i => $e): ?>
        <tr>
          <td style="color:var(--muted);font-weight:600"><?= $i + 1 ?></td>
          <td style="font-weight:500"><?= esc($e['title']) ?></td>
          <td><span class="badge badge-accent"><?= $catLabels[$e['category']] ?? esc($e['category']) ?></span></td>
          <td><?= esc($e['paid_by_name']) ?></td>
          <td style="font-family:'Syne',sans-serif;font-weight:700;color:var(--accent2)">€<?= number_format($e['amount'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= view('layouts/footer') ?>
