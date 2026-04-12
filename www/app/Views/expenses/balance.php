<?= view('layouts/header') ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">

  <!-- Individual balances -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Balance por persona</span>
    </div>
    <div class="balance-list">
      <?php foreach ($memberBalances as $mb): ?>
      <div class="balance-item">
        <div class="balance-user">
          <div class="user-avatar"><?= strtoupper(substr($mb['username'], 0, 1)) ?></div>
          <div>
            <div style="font-weight:500;font-size:0.9rem"><?= esc($mb['username']) ?></div>
            <div style="font-size:0.72rem;color:var(--muted)">Ha pagado €<?= number_format($mb['paid'], 2) ?> · Debe pagar €<?= number_format($mb['should_pay'], 2) ?></div>
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
      <span class="card-title"><i data-lucide="arrow-right-left"></i> Liquidaciones sugeridas</span>
    </div>
    <?php if (empty($settlements)): ?>
      <div class="empty-state" style="padding:30px 0">
        <div class="icon"><i data-lucide="circle-check-big" style="width:32px;height:32px;color:var(--success)"></i></div>
        <h3>¡Todo está saldado!</h3>
        <p>No hay deudas entre los miembros</p>
      </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php foreach ($settlements as $s): ?>
      <div style="padding:14px 16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
          <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem"><?= strtoupper(substr($s['from_name'], 0, 1)) ?></div>
          <span style="font-weight:500"><?= esc($s['from_name']) ?></span>
          <span style="color:var(--muted)">→</span>
          <div class="user-avatar" style="width:28px;height:28px;font-size:0.72rem"><?= strtoupper(substr($s['to_name'], 0, 1)) ?></div>
          <span style="font-weight:500"><?= esc($s['to_name']) ?></span>
          <span style="margin-left:auto;font-family:'Syne',sans-serif;font-size:1.1rem;font-weight:700;color:var(--danger)">€<?= number_format($s['amount'], 2) ?></span>
        </div>
        <?php if ($s['from_id'] == session()->get('user_id')): ?>
        <form method="post" action="<?= site_url('/expenses/settle') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="to_user_id" value="<?= $s['to_id'] ?>">
          <input type="hidden" name="amount" value="<?= $s['amount'] ?>">
          <button class="btn btn-sm btn-primary"><i data-lucide="check" style="width:12px;height:12px"></i> Marcar como pagado</button>
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
    <span class="card-title"><i data-lucide="bar-chart-2"></i> Aportación de cada miembro</span>
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
    <span class="card-title"><i data-lucide="history"></i> Historial de liquidaciones</span>
  </div>
  <?php if (empty($settleHistory)): ?>
    <div class="empty-state" style="padding:30px 0"><p>Sin liquidaciones previas</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>De</th><th>Para</th><th>Importe</th><th>Fecha</th></tr></thead>
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

<?= view('layouts/footer') ?>
