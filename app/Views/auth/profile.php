<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<div class="container py-5">
  <h1 class="h2 mb-4">Meu perfil</h1>
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow-sm p-4">
      <h2 class="h4 mb-3">Meus repositórios</h2>
      <?php if (!$repositories): ?>
        <div class="card shadow-sm p-4">
          <p class="text-muted">Você ainda não iniciou uma submissão de repositório.</p>
          <a class="btn btn-primary align-self-start" href="<?= site_url('application') ?>">Submeter repositório</a>
        </div>
      <?php endif; ?>
      <?php foreach ($repositories as $repository): $summary = $repository['summary']; ?>
        <article class="card shadow-sm p-4 mb-3">
          <h3 class="h5"><?= esc($repository['repository_name'] ?: 'Repositório #' . $repository['id']) ?></h3>
          <p class="text-break mb-2"><?= esc($repository['base_url']) ?></p>
          <?php if (!empty($repository['repository_type'])): ?>
            <p class="small text-muted">Tipo: <?= esc($repository['repository_type']) ?></p>
          <?php endif; ?>
          <div class="mb-3">
            <span class="badge <?= esc($repository['evaluationClass'], 'attr') ?>"><?= esc($repository['evaluationStatus']) ?></span>
          </div>
          <div class="d-flex justify-content-between gap-2 mb-2">
            <span>Preenchimento da submissão</span>
            <strong><?= number_format($summary['completion'], 1, ',', '.') ?>%</strong>
          </div>
          <div class="progress" role="progressbar" aria-label="Preenchimento da submissão" aria-valuenow="<?= $summary['completion'] ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: <?= $summary['completion'] ?>%"></div>
          </div>
          <p class="small text-muted mt-2 mb-3"><?= $summary['answered'] ?> de <?= $summary['total'] ?> questões respondidas.</p>
          <?php if (!empty($repository['submitted_at'])): ?>
            <p class="small mb-0">Enviado em <?= esc(date('d/m/Y H:i', strtotime($repository['submitted_at']))) ?></p>
          <?php else: ?>
            <a class="btn btn-outline-primary align-self-start" href="<?= site_url('application/form/select/' . $repository['id']) ?>">Continuar submissão</a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card shadow-sm p-4">
        <h2 class="h4 mb-4">Meus dados</h2>
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
