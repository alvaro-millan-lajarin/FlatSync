<?= view('layouts/header') ?>

<div style="max-width:560px">
  <div class="card">

    <!-- Avatar section -->
    <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:28px;padding-bottom:24px;border-bottom:1px solid var(--divider)">

      <div id="avatar-wrap" style="position:relative;cursor:pointer;margin-bottom:12px" onclick="document.getElementById('avatar-input').click()">
        <?php if (!empty($user['avatar_url'])): ?>
          <img id="avatar-preview" src="<?= base_url($user['avatar_url']) ?>"
               style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--border);display:block">
        <?php else: ?>
          <div id="avatar-initials" style="width:96px;height:96px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:2.2rem;font-weight:700;color:#fff;border:3px solid var(--border)">
            <?= strtoupper(substr($user['username'], 0, 1)) ?>
          </div>
          <img id="avatar-preview" src="" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--border);display:none">
        <?php endif; ?>

        <!-- Overlay -->
        <div style="position:absolute;inset:0;border-radius:50%;background:rgba(0,0,0,0.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s"
             onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
          <i data-lucide="camera" style="width:22px;height:22px;color:#fff;stroke-width:1.75"></i>
        </div>
      </div>

      <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="previewAvatar(this)">

      <p style="font-size:0.78rem;color:var(--muted);margin-bottom:10px">Haz clic en el avatar para cambiar la imagen</p>

      <?php if (!empty($user['avatar_url'])): ?>
      <button type="button" onclick="confirmRemove()" class="btn btn-danger btn-sm">
        Eliminar foto
      </button>
      <?php endif; ?>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error" style="margin-bottom:18px">
        <?php foreach ($errors as $e): ?><?= esc($e) ?><br><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post" action="<?= site_url('/profile/edit') ?>" enctype="multipart/form-data" id="profile-form">
      <?= csrf_field() ?>
      <input type="file" name="avatar" id="avatar-file-field" style="display:none">
      <input type="hidden" name="remove_avatar" id="remove-avatar-field" value="0">

      <div class="form-group">
        <label>Nombre <span style="color:var(--danger)">*</span></label>
        <input type="text" name="username" value="<?= esc($user['username']) ?>" required placeholder="Tu nombre">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= esc($user['email']) ?>" placeholder="tu@email.com">
        <p style="font-size:0.75rem;color:var(--muted);margin-top:4px">Usado para iniciar sesión. Déjalo vacío para no cambiar.</p>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center">
          Guardar cambios
        </button>
        <a href="<?= site_url('/profile') ?>" class="btn btn-secondary" style="flex:1;justify-content:center">Cancelar</a>
      </div>
    </form>
  </div>
</div>

<script>
function previewAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    // Show preview
    const preview = document.getElementById('avatar-preview');
    const initials = document.getElementById('avatar-initials');
    preview.src = e.target.result;
    preview.style.display = 'block';
    if (initials) initials.style.display = 'none';

    // Copy file to the real form input
    const dt = new DataTransfer();
    dt.items.add(input.files[0]);
    document.getElementById('avatar-file-field').files = dt.files;

    document.getElementById('remove-avatar-field').value = '0';
  };
  reader.readAsDataURL(input.files[0]);
}

function confirmRemove() {
  if (!confirm('¿Eliminar la foto de perfil?')) return;
  document.getElementById('remove-avatar-field').value = '1';

  // Reset preview to initials
  const preview = document.getElementById('avatar-preview');
  const initials = document.getElementById('avatar-initials');
  preview.style.display = 'none';
  if (initials) initials.style.display = 'flex';

  // Submit immediately
  document.getElementById('profile-form').submit();
}
</script>

<?= view('layouts/footer') ?>
