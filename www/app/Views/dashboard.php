<?= view('layouts/header') ?>

<style>
/* ── Dashboard: month nav & chart overrides ── */
.mw-month-nav { display:flex; gap:10px; align-items:center; margin-bottom:28px; flex-wrap:wrap; }
.mw-month-btn {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 8px 18px; border-radius: 50px;
  font-size: 0.82rem; font-weight: 700;
  text-decoration: none; transition: all .18s;
  background: #fff; color: #8D6868;
  border: 2px solid #F5DEDE; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.mw-month-btn:hover { background: #EBF3FF; border-color: #7AAEFF; color: #4F80FF; }
.mw-month-label { font-size: 1.15rem; font-weight: 900; color: #3D2020; letter-spacing: -0.01em; }

/* Chart bars — coral for categories, mint for evolution */
.chart-bar { border-radius: 6px; font-weight: 700; }
#cat-chart   .chart-bar { background: linear-gradient(90deg,#4F80FF,#7AAEFF) !important; color:#1A3A8F !important; }
#evol-chart  .chart-bar { background: linear-gradient(90deg,#6366F1,#818CF8) !important; color:#312E81 !important; }
</style>

<div>

<!-- Selector de mes -->
<div class="mw-month-nav">
  <a href="<?= site_url('/dashboard?month=' . $prevMonth) ?>" class="mw-month-btn">‹ <?= lang('App.previous') ?></a>
  <span class="mw-month-label"><?= $monthLabel ?></span>
  <a href="<?= site_url('/dashboard?month=' . $nextMonth) ?>" class="mw-month-btn"><?= lang('App.next') ?> ›</a>
  <?php if ($filterMonth !== date('Y-m')): ?>
    <a href="<?= site_url('/dashboard') ?>" class="mw-month-btn"><?= lang('App.current_month') ?></a>
  <?php endif; ?>
</div>

<?php
$catColors = [
  'food'     => ['rgba(245,158,11,0.15)',  '#B45309'],
  'cleaning' => ['rgba(79,128,255,0.15)',  '#1A3A8F'],
  'bills'    => ['rgba(99,102,241,0.15)',  '#3730A3'],
  'other'    => ['rgba(78,205,196,0.18)',  '#1A8C86'],
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
  <div class="card" id="cat-chart">
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
  <div class="card" id="evol-chart">
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
    <div class="mw-member-card" style="padding:14px 16px">
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

</div>

<?= view('layouts/footer') ?>
