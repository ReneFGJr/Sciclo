<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2>Usuários</h2>
<a class="btn btn-success mb-3" href="<?= base_url('admin/users/create') ?>">Novo usuário</a>
<div class="table-responsive"><table class="table table-striped">
<thead><tr><th>Nome</th><th>E-mail</th><th class="text-end">Ações</th></tr></thead><tbody>
<?php foreach ($users as $user): ?>
<tr><td><?= esc($user['name']) ?></td><td><?= esc($user['email']) ?></td><td class="text-end">
<div class="d-flex justify-content-end gap-2">
<a class="btn btn-sm btn-primary" href="<?= base_url('admin/users/edit/' . $user['id']) ?>" title="Editar / atribuir perfis" aria-label="Editar / atribuir perfis"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
<?php if ((int) $user['id'] !== (int) session('user_id')): ?>
<?php if (!session('impersonator_id')): ?>
<form class="mb-0" method="post" action="<?= site_url('admin/users/' . $user['id'] . '/access-as') ?>">
<?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary" type="submit" title="Acessar como" aria-label="Acessar como"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" style="vertical-align: -0.125em;"><circle cx="8" cy="7" r="4"/><path d="M1 21v-2a7 7 0 0 1 14 0v2M15 10h8m-3-3 3 3-3 3"/></svg></button></form>
<?php endif; ?>
<form class="mb-0" method="post" action="<?= base_url('admin/users/delete/' . $user['id']) ?>" onsubmit="return confirm('Excluir este usuário e suas atribuições de perfis?')">
<?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" type="submit" title="Excluir usuário" aria-label="Excluir usuário"><i class="bi bi-trash" aria-hidden="true"></i></button></form>
<?php endif; ?></div></td></tr>
<?php endforeach; ?>
<?php if (!$users): ?><tr><td colspan="3">Nenhum usuário cadastrado.</td></tr><?php endif; ?>
</tbody></table></div>
<?= $pager->links() ?>
</div>
<?= view('layout/footer') ?>
