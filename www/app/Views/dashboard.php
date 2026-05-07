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

/* Chart bars — evolution */
.chart-bar { border-radius: 6px; font-weight: 700; }
#evol-chart .chart-bar { background: linear-gradient(90deg,#6366F1,#818CF8) !important; color:#312E81 !important; }
/* 4-col stats on wide, 2-col on tablet, 1-col stacked on phone */
@media (max-width: 900px) { .stats-grid { grid-template-columns: 1fr 1fr !important; } }
@media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr !important; } }
/* member grid stacks on mobile */
@media (max-width: 768px) { .db-member-grid { grid-template-columns: 1fr !important; } }
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
  <div class="stat-card danger">
    <div class="stat-icon"><i data-lucide="x-circle"></i></div>
    <div class="stat-value"><?= $missedChores ?></div>
    <div class="stat-label"><?= lang('App.dashboard_chores_missed') ?></div>
  </div>
</div>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  <!-- Por categoría (donut) -->
  <div class="card" id="cat-chart">
    <div class="card-header"><span class="card-title"><i data-lucide="folder"></i> <?= lang('App.dashboard_by_category') ?></span></div>
    <?php
      $donutColors = ['food'=>'#F59E0B','cleaning'=>'#4F80FF','bills'=>'#6366F1','other'=>'#4ECDC4'];
      $donutFallback = ['#94A3B8','#64748B','#475569','#334155'];
      $donutR = 75; $donutSW = 32;
      $donutC = 2 * M_PI * $donutR;
      $totalCat = array_sum(array_column($byCategory, 'total')) ?: 1;
      $cumLen = 0; $di = 0;
    ?>
    <?php if (!empty($byCategory)): ?>
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
      <div style="position:relative;flex-shrink:0;width:190px;height:190px">
        <svg viewBox="0 0 200 200" style="width:190px;height:190px;transform:rotate(-90deg)">
          <?php foreach ($byCategory as $cat):
            $frac = $cat['total'] / $totalCat;
            $seg  = $frac * $donutC;
            $gap  = $donutC - $seg;
            $col  = $donutColors[$cat['category']] ?? $donutFallback[$di % 4];
            $di++;
          ?>
          <circle cx="100" cy="100" r="<?= $donutR ?>"
            fill="none" stroke="<?= $col ?>" stroke-width="<?= $donutSW ?>"
            stroke-dasharray="<?= round($seg,2) ?> <?= round($gap,2) ?>"
            stroke-dashoffset="<?= round(-$cumLen,2) ?>"/>
          <?php $cumLen += $seg; endforeach; ?>
          <circle cx="100" cy="100" r="<?= $donutR - $donutSW/2 - 1 ?>" fill="white"/>
        </svg>
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none">
          <div style="font-family:'Syne',sans-serif;font-size:1.05rem;font-weight:900;color:var(--text)">€<?= number_format($totalCat, 0) ?></div>
          <div style="font-size:0.68rem;color:var(--muted);margin-top:1px"><?= lang('App.dashboard_total') ?></div>
        </div>
      </div>
      <div style="flex:1;min-width:120px;display:flex;flex-direction:column;gap:10px">
        <?php $di2 = 0; foreach ($byCategory as $cat):
          $col2 = $donutColors[$cat['category']] ?? $donutFallback[$di2 % 4]; $di2++;
        ?>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
          <div style="display:flex;align-items:center;gap:7px">
            <div style="width:11px;height:11px;border-radius:3px;background:<?= $col2 ?>;flex-shrink:0"></div>
            <span style="font-size:0.83rem"><?= $catLabels[$cat['category']] ?? esc($cat['category']) ?></span>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <span style="font-size:0.83rem;font-weight:700">€<?= number_format($cat['total'],2) ?></span>
            <span style="font-size:0.73rem;color:var(--muted);margin-left:4px"><?= round($cat['total']/$totalCat*100) ?>%</span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <p style="color:var(--muted);font-size:0.85rem;text-align:center;padding:20px 0"><?= lang('App.no_data') ?></p>
    <?php endif; ?>
  </div>

  <!-- Evolución mensual -->
  <div class="card" id="evol-chart">
    <div class="card-header"><span class="card-title"><i data-lucide="trending-up"></i> <?= lang('App.dashboard_evolution') ?></span></div>
    <?php $maxEvol = max(1, max(array_column($monthlyEvolution, 'total') ?: [0])); $monthsShort = lang('App.months_short'); ?>
    <div class="chart-bar-group">
      <?php foreach ($monthlyEvolution as $me): ?>
      <div class="chart-row">
        <div class="chart-label" style="font-size:0.78rem"><?= $monthsShort[(int)$me['month'] - 1] ?> <?= $me['year'] ?></div>
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

<!-- Gastos + tareas por miembro -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px" class="db-member-grid">

  <!-- Gastos por miembro -->
  <?php if (!empty($byMember)): ?>
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="users"></i> <?= lang('App.dashboard_by_member') ?></span></div>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($byMember as $bm):
        $balance = $bm['paid'] - ($monthExpenses / max($memberCount, 1));
      ?>
      <div class="mw-member-card" style="padding:14px 16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
          <div style="display:flex;align-items:center;gap:8px">
            <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem"><?= strtoupper(substr($bm['username'], 0, 1)) ?></div>
            <span style="font-weight:500"><?= esc($bm['username']) ?></span>
          </div>
          <div style="text-align:right">
            <div style="font-family:'Syne',sans-serif;font-weight:700;color:var(--accent2)">€<?= number_format($bm['paid'], 2) ?></div>
            <div style="font-size:0.7rem;color:<?= $balance >= 0 ? 'var(--success)' : 'var(--danger)' ?>;font-weight:600">
              <?= $balance >= 0 ? '+' : '' ?>€<?= number_format($balance, 2) ?>
            </div>
          </div>
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

  <!-- Tareas por miembro -->
  <?php if (!empty($choresByMember)): ?>
  <div class="card">
    <div class="card-header"><span class="card-title"><i data-lucide="clipboard-check"></i> <?= lang('App.dashboard_chores_completion') ?></span></div>
    <div style="display:flex;flex-direction:column;gap:14px;margin-top:4px">
      <?php foreach ($choresByMember as $cm):
        $total = $cm['done'] + $cm['missed'];
        $donePct = $total > 0 ? round($cm['done'] / $total * 100) : 0;
        $missedPct = 100 - $donePct;
      ?>
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px">
          <div style="display:flex;align-items:center;gap:7px">
            <div class="user-avatar" style="width:24px;height:24px;font-size:0.68rem"><?= strtoupper(substr($cm['username'], 0, 1)) ?></div>
            <span style="font-size:0.85rem;font-weight:500"><?= esc($cm['username']) ?></span>
          </div>
          <span style="font-size:0.72rem;color:var(--muted)"><?= $total ?> tareas</span>
        </div>
        <div style="display:flex;height:18px;border-radius:8px;overflow:hidden;background:var(--surface2)">
          <?php if ($donePct > 0): ?>
          <div style="width:<?= $donePct ?>%;background:var(--success);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;color:#fff;transition:width 0.4s"><?= $cm['done'] ?></div>
          <?php endif; ?>
          <?php if ($missedPct > 0 && $cm['missed'] > 0): ?>
          <div style="width:<?= $missedPct ?>%;background:var(--danger);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;color:#fff;transition:width 0.4s"><?= $cm['missed'] ?></div>
          <?php endif; ?>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px">
          <span style="font-size:0.68rem;color:var(--success);font-weight:600">● <?= lang('App.dashboard_done') ?> <?= $cm['done'] ?></span>
          <span style="font-size:0.68rem;color:var(--danger);font-weight:600">● <?= lang('App.dashboard_missed_lbl') ?> <?= $cm['missed'] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Legend -->
    <div style="display:flex;gap:14px;margin-top:16px;padding-top:12px;border-top:1px solid var(--divider)">
      <div style="display:flex;align-items:center;gap:5px;font-size:0.73rem;color:var(--muted)">
        <div style="width:10px;height:10px;border-radius:2px;background:var(--success)"></div><?= lang('App.dashboard_done') ?>
      </div>
      <div style="display:flex;align-items:center;gap:5px;font-size:0.73rem;color:var(--muted)">
        <div style="width:10px;height:10px;border-radius:2px;background:var(--danger)"></div><?= lang('App.dashboard_missed_lbl') ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>

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
