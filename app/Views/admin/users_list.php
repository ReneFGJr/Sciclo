<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2>Usuários</h2>
<a class="btn btn-success mb-3" href="<?= base_url('admin/users/create') ?>">Novo usuário</a>
<div class="table-responsive"><table class="table table-striped">
<thead><tr><th>Nome</th><th>E-mail</th><th>Ações</th></tr></thead><tbody>
<?php foreach ($users as $user): ?>
<tr><td><?= esc($user['name']) ?></td><td><?= esc($user['email']) ?></td><td>
<a class="btn btn-sm btn-primary" href="<?= base_url('admin/users/edit/' . $user['id']) ?>">Editar / atribuir perfis</a>
<?php if ((int) $user['id'] !== (int) session('user_id')): ?>
<form class="d-inline" method="post" action="<?= base_url('admin/users/delete/' . $user['id']) ?>" onsubmit="return confirm('Excluir este usuário e suas atribuições de perfis?')">
<?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Excluir</button></form>
<?php endif; ?></td></tr>
<?php endforeach; ?>
<?php if (!$users): ?><tr><td colspan="3">Nenhum usuário cadastrado.</td></tr><?php endif; ?>
</tbody></table></div>
<?= $pager->links() ?>
</div>
<?= view('layout/footer') ?>