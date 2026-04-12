<?= view('layouts/header') ?>

<div style="margin-bottom:20px;padding:20px 24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">Código de invitación</div>
    <div style="font-size:1.5rem;font-weight:800;letter-spacing:.15em;color:var(--primary);font-variant-numeric:tabular-nums"><?= esc($home['invite_code']) ?></div>
  </div>
  <div style="color:var(--muted);font-size:0.85rem;max-width:320px">
    Comparte este código para que nuevas personas se unan al hogar. Cada miembro lo introduce durante el registro.
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
  <?php foreach ($memberStats as $m): ?>
  <div class="card" style="position:relative">
    <?php if ($m['is_admin']): ?>
      <div style="position:absolute;top:16px;right:16px"><span class="badge badge-accent"><i data-lucide="shield-check" style="width:10px;height:10px"></i> Admin</span></div>
    <?php endif; ?>
    <?php if ($m['id'] == session()->get('user_id')): ?>
      <div style="position:absolute;top:<?= $m['is_admin'] ? '46px' : '16px' ?>;right:16px"><span class="badge badge-done">Tú</span></div>
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
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px">Pagado</div>
      </div>
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--success)"><?= $m['chores_done'] ?></div>
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px">Completadas</div>
      </div>
      <div style="background:var(--surface2);border-radius:8px;padding:10px">
        <div style="font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;color:var(--danger)"><?= $m['chores_missed'] ?></div>
        <div style="font-size:0.68rem;color:var(--muted);margin-top:2px">Perdidas</div>
      </div>
    </div>

    <?php if ($m['chores_done'] + $m['chores_missed'] > 0): ?>
    <div style="margin-top:14px">
      <div style="font-size:0.72rem;color:var(--muted);margin-bottom:4px">Tasa de cumplimiento</div>
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
