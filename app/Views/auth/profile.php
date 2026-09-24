<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12">
      <div class="card shadow-sm p-4">
        <h2 class="mb-4">Meu perfil</h2>
        <dl class="mb-4">
          <dt>Nome</dt>
          <dd><?= esc($user['name']) ?></dd>
          <dt>E-mail</dt>
          <dd><?= esc($user['email']) ?></dd>
        </dl>
        <h3 class="h5 mb-3">Perfis e permissões</h3>
        <?php if ($assignments): ?>
          <div class="table-responsive mb-4">
            <table class="table align-middle">
              <thead>
                <tr><th scope="col">Perfil</th><th scope="col">Início</th><th scope="col">Válido até</th><th scope="col">Status</th></tr>
              </thead>
              <tbody>
                <?php foreach ($assignments as $assignment): ?>
                  <tr>
                    <td><?= esc($assignment['name']) ?></td>
                    <td><?= esc(date('d/m/Y', strtotime($assignment['starts_at']))) ?></td>
                    <td><?= esc(date('d/m/Y', strtotime($assignment['ends_at']))) ?></td>
                    <td><span class="badge <?= esc($assignment['statusClass'], 'attr') ?>"><?= esc($assignment['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted mb-4">Nenhum perfil atribuído.</p>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-outline-primary" href="<?= base_url() ?>">Voltar ao início</a>
          <a class="btn btn-outline-danger" href="<?= site_url('logout') ?>">Sair (logout)</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?= view('layout/footer') ?>
