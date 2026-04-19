<?php
$_activeLang = $_lang ?? session()->get('lang') ?? 'es';
$_flags = [
  'es' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 3 2" style="width:100%;height:100%;display:block"><rect width="3" height="2" fill="#AA151B"/><rect y=".5" width="3" height="1" fill="#F1BF00"/></svg>',
  'en' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60 36" style="width:100%;height:100%;display:block"><rect width="60" height="36" fill="#012169"/><path d="M0,0 60,36M60,0 0,36" stroke="#fff" stroke-width="7.2"/><path d="M0,0 60,36M60,0 0,36" stroke="#C8102E" stroke-width="3.6"/><rect x="24" y="0" width="12" height="36" fill="#fff"/><rect x="0" y="12" width="60" height="12" fill="#fff"/><rect x="25.5" y="0" width="9" height="36" fill="#C8102E"/><rect x="0" y="13.5" width="60" height="9" fill="#C8102E"/></svg>',
  'ca' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 9 6" style="width:100%;height:100%;display:block"><rect width="9" height="6" fill="#FCDD09"/><rect y=".667" width="9" height=".667" fill="#C60B1E"/><rect y="2" width="9" height=".667" fill="#C60B1E"/><rect y="3.333" width="9" height=".667" fill="#C60B1E"/><rect y="4.667" width="9" height=".667" fill="#C60B1E"/></svg>',
];
$_titles = ['es'=>'Español','en'=>'English','ca'=>'Català'];
?>
<div style="display:flex;justify-content:center;gap:10px;margin-top:16px">
  <?php foreach($_flags as $code => $svg): ?>
  <a href="<?= site_url('/lang/'.$code) ?>" title="<?= $_titles[$code] ?>"
     style="display:block;width:38px;height:25px;border-radius:4px;overflow:hidden;text-decoration:none;
            opacity:<?= $_activeLang===$code ? '1' : '0.35' ?>;
            box-shadow:<?= $_activeLang===$code ? '0 0 0 2px var(--primary)' : '0 0 0 1px var(--border)' ?>;
            transition:opacity .15s,box-shadow .15s">
    <?= $svg ?>
  </a>
  <?php endforeach; ?>
</div>
