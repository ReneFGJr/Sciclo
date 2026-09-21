<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container my-4">
<?= view('admin/messages') ?>
<h2><?= isset($user['id']) ? 'Editar usuário' : 'Novo usuário' ?></h2>
<form method="post" action="<?= base_url(isset($user['id']) ? 'admin/users/edit/' . $user['id'] : 'admin/users/create') ?>">
<?= csrf_field() ?>
<div class="mb-3"><label for="name" class="form-label">Nome</label><input id="name" name="name" class="form-control" required maxlength="100" value="<?= esc($user['name'], 'attr') ?>"></div>
<div class="mb-3"><label for="email" class="form-label">E-mail</label><input id="email" name="email" type="email" class="form-control" required maxlength="150" value="<?= esc($user['email'], 'attr') ?>"></div>
<div class="mb-3"><label for="password" class="form-label">Senha <?= isset($user['id']) ? '(deixe em branco para manter)' : '' ?></label><input id="password" name="password" type="password" autocomplete="new-password" class="form-control" minlength="8" maxlength="72" <?= isset($user['id']) ? '' : 'required' ?>></div>
<button class="btn btn-success">Salvar usuário</button>
<a class="btn btn-secondary" href="<?= base_url('admin/users') ?>">Voltar</a>
</form>
<?php if (isset($user['id'])): ?>
<hr><h3>Perfis e vigência</h3>
<p>As datas de início e fim são inclusivas. Perfis futuros ou encerrados não concedem acesso administrativo.</p>
<?php foreach ($assignments as $assignment): ?>
<div class="card mb-3"><div class="card-body">
<strong><?= esc($assignment['name']) ?></strong>
<span class="badge bg-secondary"><?= $assignment['starts_at'] > date('Y-m-d') ? 'Agendado' : ($assignment['ends_at'] < date('Y-m-d') ? 'Encerrado' : 'Vigente') ?></span>
<form class="row g-2 mt-1" method="post" action="<?= base_url('admin/users/' . $user['id'] . '/rules') ?>">
<?= csrf_field() ?>
<input type="hidden" name="assignment_id" value="<?= $assignment['id'] ?>">
<input type="hidden" name="rule_id" value="<?= $assignment['rule_id'] ?>">
<div class="col-md-4"><label class="form-label" for="start-<?= $assignment['id'] ?>">Início</label><input class="form-control" type="date" id="start-<?= $assignment['id'] ?>" name="starts_at" value="<?= esc($assignment['starts_at'], 'attr') ?>" required></div>
<div class="col-md-4"><label class="form-label" for="end-<?= $assignment['id'] ?>">Fim</label><input class="form-control" type="date" id="end-<?= $assignment['id'] ?>" name="ends_at" value="<?= esc($assignment['ends_at'], 'attr') ?>" required></div>
<div class="col-md-4 align-self-end"><button class="btn btn-primary">Atualizar período</button></div>
</form>
<form method="post" class="mt-2" action="<?= base_url('admin/users/' . $user['id'] . '/rules/' . $assignment['id'] . '/delete') ?>" onsubmit="return confirm('Remover esta atribuição de perfil?')">
<?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">Remover atribuição</button></form>
</div></div>
<?php endforeach; ?>
<?php if (!$assignments): ?><p>Nenhum perfil atribuído.</p><?php endif; ?>
<h4>Atribuir perfil</h4>
<form class="row g-3" method="post" action="<?= base_url('admin/users/' . $user['id'] . '/rules') ?>">
<?= csrf_field() ?>
<div class="col-md-4"><label for="rule_id" class="form-label">Perfil</label><select class="form-select" id="rule_id" name="rule_id" required>
<option value="">Selecione</option>
<?php foreach ($rules as $rule): ?><option value="<?= $rule['id'] ?>"><?= esc($rule['name']) ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-3"><label for="starts_at" class="form-label">Início</label><input class="form-control" id="starts_at" name="starts_at" type="date" required></div>
<div class="col-md-3"><label for="ends_at" class="form-label">Fim</label><input class="form-control" id="ends_at" name="ends_at" type="date" required></div>
<div class="col-md-2 align-self-end"><button class="btn btn-success">Atribuir</button></div>
</form>
<?php endif; ?>
</div>
<?= view('layout/footer') ?>