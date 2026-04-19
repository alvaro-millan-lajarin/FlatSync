  </main>
</div>

<!-- Modal de confirmación personalizado (reemplaza confirm() nativo) -->
<div class="modal-overlay" id="modal-confirm" style="display:none" onclick="if(event.target===this)closeConfirm(false)">
  <div class="modal" style="max-width:380px">
    <div class="modal-header" style="border-bottom:none;padding-bottom:4px">
      <h3 class="modal-title" id="confirm-title" style="font-size:1.05rem"></h3>
    </div>
    <p id="confirm-msg" style="font-size:0.9rem;color:var(--text-secondary);padding:0 24px 20px;margin:0;line-height:1.5"></p>
    <div style="display:flex;gap:10px;padding:0 24px 24px">
      <button class="btn btn-secondary" style="flex:1;justify-content:center" onclick="closeConfirm(false)"><?= lang('App.cancel') ?></button>
      <button id="confirm-ok-btn" class="btn btn-danger" style="flex:1;justify-content:center"><?= lang('App.confirm_delete') ?></button>
    </div>
  </div>
</div>

<script src="<?= base_url('js/app.js') ?>"></script>
<script>
if (window.lucide) lucide.createIcons();

/* ── Modal de confirmación global ── */
let _confirmCallback = null;

const _confirmDefaults = { title: <?= json_encode(lang('App.confirm_title')) ?>, okText: <?= json_encode(lang('App.confirm_delete')) ?> };
function showConfirm(msg, onOk, { title = _confirmDefaults.title, okText = _confirmDefaults.okText, okClass = 'btn-danger' } = {}) {
  document.getElementById('confirm-title').textContent = title;
  document.getElementById('confirm-msg').textContent   = msg;
  const btn = document.getElementById('confirm-ok-btn');
  btn.textContent = okText;
  btn.className   = 'btn ' + okClass + ' ' + 'confirm-ok-flex';
  btn.style.cssText = 'flex:1;justify-content:center';
  _confirmCallback = onOk;
  btn.onclick = () => closeConfirm(true);
  document.getElementById('modal-confirm').style.display = 'flex';
}

function closeConfirm(accepted) {
  document.getElementById('modal-confirm').style.display = 'none';
  if (accepted && _confirmCallback) _confirmCallback();
  _confirmCallback = null;
}

/* Interceptar formularios con data-confirm automáticamente */
document.addEventListener('submit', function(e) {
  const msg = e.currentTarget === e.target ? null : null; // no-op, see below
}, true);

document.addEventListener('submit', function(e) {
  const msg = e.target.dataset.confirm;
  if (!msg) return;
  e.preventDefault();
  const form = e.target;
  showConfirm(msg, () => {
    delete form.dataset.confirm;
    form.submit();
  });
}, false);
</script>
</body>
</html>
