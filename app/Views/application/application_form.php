<?php
echo view('layout/header');
echo view('layout/navbar');

?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <h1 class="fw-bold mb-3">Submeta seu repositório para certificação</h1>
            <p class="lead text-muted mb-4">Comece informando o endereço do repositório. Depois, escolha o questionário, registre as respostas e reúna as evidências para enviar a submissão à avaliação.</p>

            <?php if (!session('logged_in')): ?>
                <div class="alert alert-info">
                    <strong>Entre na sua conta antes de começar.</strong>
                    Assim, sua submissão fica vinculada ao seu perfil para acompanhar o preenchimento e o status da avaliação.
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a class="btn btn-primary" href="<?= site_url('login') ?>">Entrar</a>
                        <a class="btn btn-outline-primary" href="<?= site_url('register') ?>">Criar conta</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-7">
                    <section class="card shadow-sm h-100" aria-labelledby="repository-url-title">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-3" id="repository-url-title">1. Informe o endereço do repositório</h2>
                            <p>Use a URL pública da página inicial do repositório, incluindo <strong>https://</strong>. Evite links de itens, coleções ou páginas de login.</p>
                            <form action="<?= site_url('application') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="repo_link" class="form-label fw-bold">URL do repositório</label>
                                    <input type="url" value="<?= esc($repo_link ?? '', 'attr') ?>" name="repo_link" id="repo_link" class="form-control form-control-lg" placeholder="https://repositorio.sua-instituicao.br" aria-describedby="repo-link-help" autocomplete="url" required>
                                    <div id="repo-link-help" class="form-text">Também pode informar o endereço OAI-PMH, se já o conhecer. URLs do DSpace terminadas em /home são aceitas.</div>
                                </div>
                                <p class="small text-muted">Ao continuar, o sistema verifica o endereço e procura o serviço OAI-PMH, usado para identificar o repositório. Aguarde a conclusão dessa verificação.</p>
                                <button type="submit" class="btn btn-primary btn-lg">Verificar repositório e continuar</button>
                            </form>
                        </div>
                    </section>
                </div>
                <div class="col-lg-5">
                    <section class="card bg-light border-0 h-100" aria-labelledby="submission-steps-title">
                        <div class="card-body p-4">
                            <h2 class="h4 mb-3" id="submission-steps-title">Como funciona a submissão</h2>
                            <ol class="ps-3 mb-4">
                                <li class="mb-3"><strong>Verifique o repositório.</strong> Informe a URL e aguarde a identificação do serviço OAI-PMH.</li>
                                <li class="mb-3"><strong>Escolha o questionário.</strong> Selecione o tipo correspondente ao seu repositório.</li>
                                <li class="mb-3"><strong>Preencha as respostas.</strong> Inclua comentários e links de evidências que ajudem a comprovar os critérios.</li>
                                <li><strong>Envie para avaliação.</strong> Complete as respostas obrigatórias e finalize o questionário.</li>
                            </ol>
                            <p class="small mb-0">O preenchimento do questionário prepara a submissão. A avaliação começa após o envio final.</p>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <h2 class="h5">Antes de começar</h2>
                    <p class="text-muted">Tenha em mãos informações sobre a gestão do repositório, suas políticas e os links dos documentos que servirão como evidências. O repositório e seu serviço OAI-PMH precisam estar acessíveis para a verificação.</p>
                    <a href="<?= site_url('about/certification') ?>">Conheça os selos de certificação</a>
                </div>
                <div class="col-md-6">
                    <h2 class="h5">Acompanhe sua submissão</h2>
                    <p class="text-muted">Na seção “Meus repositórios” do seu perfil, consulte o percentual de preenchimento, continue uma submissão e acompanhe o status da avaliação. O percentual indica as questões respondidas, não uma nota de certificação.</p>
                    <?php if (session('logged_in')): ?>
                        <a href="<?= site_url('profile') ?>">Ver meus repositórios</a>
                    <?php else: ?>
                        <a href="<?= site_url('login') ?>">Entrar para acompanhar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo view('layout/footer');
