<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2><?= isset($rule['id']) ? 'Editar perfil' : 'Novo perfil' ?></h2>
<form method="post" action="<?= base_url(isset($rule['id']) ? 'admin/config/rules/edit/' . $rule['id'] : 'admin/config/rules/create') ?>">
<?= csrf_field() ?>
<div class="mb-3"><label class="form-label" for="name">Nome do perfil</label><input class="form-control" id="name" name="name" required maxlength="100" value="<?= esc($rule['name'], 'attr') ?>"></div>
<?php if (($rule['code'] ?? '') === 'site-manager'): ?><p>Este perfil concede acesso administrativo, mesmo que seu nome seja alterado.</p><?php endif; ?>
<button class="btn btn-success">Salvar</button><a class="btn btn-secondary" href="<?= base_url('admin/config/rules') ?>">Cancelar</a>
</form>
</div>
<?= view('layout/footer') ?>