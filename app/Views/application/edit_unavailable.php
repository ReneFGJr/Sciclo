<?= view('layout/header') ?>
<?= view('layout/navbar') ?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <section class="card border-0 shadow-sm" aria-labelledby="edit-unavailable-title">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="rounded-circle bg-light text-secondary p-3"><i class="bi bi-lock fs-3" aria-hidden="true"></i></span>
                        <div>
                            <p class="small text-muted mb-1">Submissão de repositório</p>
                            <h1 class="h3 mb-0" id="edit-unavailable-title">Edição indisponível no momento</h1>
                        </div>
                    </div>
                    <?php if ($reason === 'login'): ?>
                        <p class="mb-4">Para continuar o preenchimento, entre com a conta do usuário responsável pela submissão deste repositório.</p>
                    <?php elseif ($reason === 'owner'): ?>
                        <p class="mb-4">A conta conectada não é a responsável por esta submissão. Para editar os dados, acesse com a conta utilizada no envio do repositório.</p>
                    <?php else: ?>
                        <p class="mb-4">A edição está bloqueada na etapa atual da avaliação. Se forem solicitadas correções, o repositório ficará disponível para ajustes em <strong>Revisões necessárias</strong>.</p>
                    <?php endif; ?>
                    <div class="bg-light rounded p-3 mb-4">
                        <p class="small text-muted mb-0"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>O responsável pode editar a submissão durante o preenchimento ou quando houver revisões solicitadas pela equipe Sciclo.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($reason === 'login'): ?>
                            <a class="btn btn-primary" href="<?= site_url('login') ?>">Entrar na minha conta</a>
                        <?php else: ?>
                            <a class="btn btn-primary" href="<?= site_url('profile') ?>">Ir para minhas submissões</a>
                        <?php endif; ?>
                        <a class="btn btn-outline-secondary" href="<?= site_url() ?>">Voltar ao início</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>
<?= view('layout/footer') ?>
