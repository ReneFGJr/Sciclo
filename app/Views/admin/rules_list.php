<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2>Perfis de usuários</h2>
<nav class="mb-3"><a href="<?= base_url('admin/config') ?>">Configurações</a></nav>
<a class="btn btn-success mb-3" href="<?= base_url('admin/config/rules/create') ?>">Novo perfil</a>
<table class="table table-striped"><thead><tr><th>Perfil</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($rules as $rule): ?>
<tr><td><?= esc($rule['name']) ?><?= $rule['code'] === 'site-manager' ? ' (administrador)' : '' ?></td><td>
<a class="btn btn-sm btn-primary" href="<?= base_url('admin/config/rules/edit/' . $rule['id']) ?>">Editar</a>
<?php if ($rule['code'] !== 'site-manager'): ?>
<form class="d-inline" method="post" action="<?= base_url('admin/config/rules/delete/' . $rule['id']) ?>" onsubmit="return confirm('Excluir este perfil?')">
<?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form>
<?php endif; ?></td></tr>
<?php endforeach; ?></tbody></table>
</div>
<?= view('layout/footer') ?>