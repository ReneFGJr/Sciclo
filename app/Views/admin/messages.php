<?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $style): ?>
<?php if ($message = session()->getFlashdata($key)): ?>
<div class="alert alert-<?= $style ?>" role="alert"><?= esc($message) ?></div>
<?php endif; endforeach; ?>
<?php foreach ($errors ?? [] as $error): ?>
<div class="alert alert-danger" role="alert"><?= esc($error) ?></div>
<?php endforeach; ?>