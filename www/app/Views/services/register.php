<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Anúnciate en FlatSync</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Syne:wght@700;800;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; background: #F0F4FF; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; color: #1E293B; }
    .wrap { background: #fff; border-radius: 24px; box-shadow: 0 8px 40px rgba(0,0,0,0.10); padding: 40px; max-width: 520px; width: 100%; }
    .logo { font-family: 'Syne', sans-serif; font-weight: 900; font-size: 1.5rem; color: #4F80FF; margin-bottom: 8px; }
    h1 { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 800; margin-bottom: 6px; }
    .sub { font-size: 0.88rem; color: #64748B; margin-bottom: 28px; line-height: 1.5; }
    label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 5px; }
    input, select { width: 100%; padding: 10px 13px; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.88rem; font-family: inherit; color: #1E293B; background: #F8FAFC; outline: none; transition: border-color .15s; }
    input:focus, select:focus { border-color: #4F80FF; background: #fff; }
    .field { margin-bottom: 16px; }
    .optional { font-weight: 400; color: #94A3B8; font-size: 0.78rem; }
    .btn { width: 100%; padding: 13px; background: linear-gradient(135deg, #4F80FF, #818CF8); color: #fff; border: none; border-radius: 12px; font-size: 0.95rem; font-weight: 700; font-family: 'Syne', sans-serif; cursor: pointer; margin-top: 8px; transition: opacity .15s; }
    .btn:hover { opacity: .88; }
    .alert-success { background: rgba(34,197,94,0.10); border: 1.5px solid rgba(34,197,94,0.3); color: #166534; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.88rem; }
    .alert-error { background: rgba(239,68,68,0.08); border: 1.5px solid rgba(239,68,68,0.25); color: #991B1B; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.88rem; }
    .price-box { background: #F0F4FF; border-radius: 12px; padding: 14px 16px; margin-bottom: 24px; font-size: 0.83rem; color: #4F80FF; line-height: 1.6; }
    .price-box strong { display: block; font-family: 'Syne', sans-serif; font-size: 1rem; margin-bottom: 4px; color: #1E293B; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="logo">FlatSync</div>
  <h1>Anúnciate en FlatSync</h1>
  <p class="sub">Aparecer en la app cuando un inquilino busque tu servicio en su zona. Solo pagas por cada contacto real.</p>

  <div class="price-box">
    <strong>¿Cómo funciona?</strong>
    Tu empresa aparece destacada cuando un usuario busca tu categoría de servicio.
    Cada vez que pulsan tu número de teléfono dentro de la app, se registra como un <strong>lead</strong>.
    Facturas solo por leads reales, sin cuota mensual fija.
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>

  <?php $errors = session()->getFlashdata('errors') ?? []; ?>
  <?php if (!empty($errors)): ?>
  <div class="alert-error">
    <?php foreach ($errors as $e): ?><div>• <?= esc($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="post" action="/services/register">
    <?= csrf_field() ?>

    <div class="field">
      <label>Nombre de la empresa</label>
      <input type="text" name="name" value="<?= esc(old('name')) ?>" placeholder="Ej: ProFontaneros Madrid SL" required>
    </div>

    <div class="field">
      <label>Categoría de servicio</label>
      <select name="category" required>
        <option value="">— Elige una categoría —</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= esc($cat['key']) ?>" <?= old('category') === $cat['key'] ? 'selected' : '' ?>>
          <?= esc($cat['label']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label>Teléfono de contacto</label>
      <input type="tel" name="phone" value="<?= esc(old('phone')) ?>" placeholder="+34 612 345 678" required>
    </div>

    <div class="field">
      <label>Email de facturación</label>
      <input type="email" name="email" value="<?= esc(old('email')) ?>" placeholder="facturacion@tuempresa.com" required>
    </div>

    <div class="field">
      <label>Ciudad <span class="optional">(opcional)</span></label>
      <input type="text" name="city" value="<?= esc(old('city')) ?>" placeholder="Ej: Valencia">
    </div>

    <div class="field">
      <label>Sitio web <span class="optional">(opcional)</span></label>
      <input type="url" name="website" value="<?= esc(old('website')) ?>" placeholder="https://tuempresa.com">
    </div>

    <button type="submit" class="btn">Enviar solicitud</button>
  </form>

  <p style="font-size:0.75rem;color:#94A3B8;text-align:center;margin-top:16px">
    Tu perfil estará visible tras revisión manual. Normalmente en menos de 48h.
  </p>
</div>
</body>
</html>
